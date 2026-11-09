<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Web service function to set course image for teacher.
 *
 * @package local_copilot
 * @author Dorel Manolescu <dorel.manolescu@enovation.ie>
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2014 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot\external;

use cache_helper;
use context_course;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use finfo;

/**
 * Web service class definition.
 */
class set_course_image_for_teacher extends external_api {
    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'course_id' => new external_value(PARAM_INT, 'ID of the Moodle course', VALUE_REQUIRED),
            'image_url' => new external_value(PARAM_URL, 'URL to the course image', VALUE_REQUIRED),
        ]);
    }

    /**
     * Returns description of method return value.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'True if the course image was updated successfully'),
        ]);
    }

    /**
     * Update course image.
     *
     * @param int $courseid
     * @param string $imageurl
     * @return array
     * @uses die
     */
    public static function execute(int $courseid, string $imageurl): array {
        global $DB;

        // Validate parameters.
        $params = self::validate_parameters(self::execute_parameters(), [
            'course_id' => $courseid,
            'image_url' => $imageurl,
        ]);
        $courseid = $params['course_id'];
        $imageurl = $params['image_url'];

        if (!$course = $DB->get_record('course', ['id' => $courseid])) {
            static::send_error_response('404 course not found');
        }

        // Perform security checks.
        $coursecontext = context_course::instance($courseid);
        self::validate_context($coursecontext);
        if (!has_capability('moodle/course:update', $coursecontext)) {
            static::send_error_response('403 user does not have capability to update course');
        }

        // Download the image using Moodle's curl class, so that the request and every redirect are checked against the
        // curl security helper (blocked hosts and allowed ports) to prevent server-side request forgery.
        $curl = static::create_curl();
        $imagecontent = $curl->get($imageurl, [], [
            'CURLOPT_FOLLOWLOCATION' => true,
            'CURLOPT_MAXREDIRS' => 5,
            'CURLOPT_TIMEOUT' => 30,
            'CURLOPT_CONNECTTIMEOUT' => 10,
        ]);
        $info = $curl->get_info();
        $httpcode = (int) ($info['http_code'] ?? 0);

        // Blocked URLs, connection errors and non-200 responses are all reported the same way, without exposing curl error
        // details to the caller.
        if ($curl->get_errno() || $httpcode !== 200 || $imagecontent === false || $imagecontent === '') {
            static::send_error_response('404 Not Found');
        }

        // Detect the content type from the downloaded bytes, rather than trusting the remote Content-Type header.
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $contenttype = $finfo->buffer($imagecontent);

        // Validate if the content is really an image.
        $allowedtypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            // Moodle 4.5 does not recognise the .webp extension as a web image, so course overview files with that
            // extension are not displayed. Save WebP images with a .jpg extension instead; browsers render them by content.
            'image/webp' => 'jpg',
        ];
        if (!array_key_exists($contenttype, $allowedtypes)) {
            static::send_error_response('415 Unsupported Media Type');
        }

        // Define Moodle file storage.
        $fs = get_file_storage();
        $files = $fs->get_area_files($coursecontext->id, 'course', 'overviewfiles', 0, 'itemid, filepath, filename', false);
        foreach ($files as $file) {
            $file->delete();
        }

        // Derive the file extension from the detected content type, never from the caller-supplied URL.
        $extension = $allowedtypes[$contenttype];

        $filename = 'course_image.' . $extension;

        $fileinfo = [
            'contextid' => $coursecontext->id,
            'component' => 'course',
            'filearea' => 'overviewfiles',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => $filename,
        ];

        // Save the new image to Moodle file system.
        $fs->create_file_from_string($fileinfo, $imagecontent);

        // Clear Moodle cache.
        rebuild_course_cache($courseid);
        cache_helper::purge_by_event('changesincourse');

        return ['success' => true];
    }

    /**
     * Create the curl client used to download the image.
     *
     * Moodle's curl class checks the URL and every redirect against the curl security helper (blocked hosts and allowed
     * ports), unlike a raw curl_init() request.
     *
     * @return \curl
     */
    protected static function create_curl(): \curl {
        global $CFG;

        require_once($CFG->libdir . '/filelib.php');
        return new \curl();
    }

    /**
     * Send an HTTP error status to the caller and stop processing the request.
     *
     * @param string $status HTTP status code and reason phrase, e.g. "404 Not Found"
     * @return never
     * @uses die
     */
    protected static function send_error_response(string $status): never {
        header('HTTP/1.0 ' . $status);
        die();
    }
}
