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
use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use finfo;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

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
        global $DB, $CFG;

        // Validate parameters.
        $params = self::validate_parameters(self::execute_parameters(), [
            'course_id' => $courseid,
            'image_url' => $imageurl,
        ]);
        $courseid = $params['course_id'];
        $imageurl = $params['image_url'];

        if (!$course = $DB->get_record('course', ['id' => $courseid])) {
            header('HTTP/1.0 404 course not found');
            die();
        }

        // Perform security checks.
        $coursecontext = context_course::instance($courseid);
        self::validate_context($coursecontext);
        if (!has_capability('moodle/course:update', $coursecontext)) {
            header('HTTP/1.0 403 user does not have capability to update course');
            die();
        }

        // Download the image.
        $ch = curl_init($imageurl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);        // Maximum number of redirects to follow.
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);    // Set the Referer header automatically.
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Verify SSL certificates.
        curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS | CURLPROTO_HTTP); // Only allow HTTP and HTTPS.

        // Add header capture.
        $headers = [];
        curl_setopt($ch, CURLOPT_HEADERFUNCTION,
            function($curl, $header) use (&$headers) {
                $len = strlen($header);
                $header = explode(':', $header, 2);
                if (count($header) < 2) {
                    return $len;
                }
                $headers[strtolower(trim($header[0]))][] = trim($header[1]);
                return $len;
            }
        );

        $imagecontent = curl_exec($ch);

        // Check for curl errors.
        if (curl_errno($ch)) {
            throw new \Exception('Curl error: ' . curl_error($ch));
        }

        $contenttype = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $finalurl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL); // Get the final URL after redirects.

        // Try multiple methods to get content type.
        if (empty($contenttype)) {
            // Try from captured headers.
            $contenttype = $headers['content-type'][0] ?? '';
        }

        if (empty($contenttype)) {
            // Try from get_headers.
            $headers = get_headers($finalurl, 1);
            $contenttype = $headers['Content-Type'] ?? $headers['content-type'] ?? '';
        }

        // If still empty, try to detect from content.
        if (empty($contenttype) && $imagecontent) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $contenttype = $finfo->buffer($imagecontent);
        }

        // Specific check for WebP format from content.
        if ((empty($contenttype) || $contenttype === 'application/octet-stream') && $imagecontent) {
            // Get first few bytes.
            $signature = substr($imagecontent, 0, 8);

            // Check signatures.
            if (substr($signature, 0, 2) === "\xFF\xD8") {
                $contenttype = 'image/jpeg';
            } else if (substr($signature, 0, 8) === "\x89PNG\r\n\x1a\n") {
                $contenttype = 'image/png';
            } else if (substr($signature, 0, 3) === 'GIF') {
                $contenttype = 'image/gif';
            } else if (substr($signature, 0, 4) === 'RIFF' && substr($imagecontent, 8, 4) === 'WEBP') {
                $contenttype = 'image/webp';
            }
        }

        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Check if the image was downloaded successfully.
        if ($imagecontent === false || $httpcode !== 200) {
            header('HTTP/1.0 404 Not Found');
            die();
        }

        // Validate if the content is really an image.
        if (!in_array($contenttype, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
            header('HTTP/1.0 415 Unsupported Media Type');
            die();
        }

        // Define Moodle file storage.
        require_once($CFG->libdir . '/filelib.php');
        $fs = get_file_storage();
        $files = $fs->get_area_files($coursecontext->id, 'course', 'overviewfiles', 0, 'itemid, filepath, filename', false);
        foreach ($files as $file) {
            $file->delete();
        }

        $extension = pathinfo($imageurl, PATHINFO_EXTENSION);
        if (empty($extension)) {
            switch ($contenttype) {
                case 'image/jpeg':
                case 'image/webp':
                    $extension = 'jpg';
                    break;
                case 'image/png':
                    $extension = 'png';
                    break;
                case 'image/gif':
                    $extension = 'gif';
                    break;
                default:
                    header('HTTP/1.0 415 Unsupported Media Type');
                    die();
            }
        }
        if ($extension === 'webp') {
            $extension = 'jpg';
        }

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
}
