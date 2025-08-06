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
 * Self enrolment method resource type for web service return data in local_copilot.
 *
 * @package local_copilot
 * @author Dorel Manolescu <dorel.manolescu@enovation.ie>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2014 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot\local\resource;

defined('MOODLE_INTERNAL') || die();

use context_course;
use external_value;
use moodle_url;

require_once($CFG->libdir . '/externallib.php');

/**
 * Self enrolment method resource type.
 */
class self_enrolment_method implements resource_type {
    /**
     * Get the resource return structure.
     *
     * @return array
     */
    public static function get_return_structure(): array {
        return [
            'course_name' => new external_value(PARAM_TEXT, 'Full name of the course.'),
            'course_id' => new external_value(PARAM_INT, 'ID of the course.'),
            'course_link' => new external_value(PARAM_URL, 'Link to the course.'),
            'course_shortname' => new external_value(PARAM_TEXT, 'Short name of the course, which is unique on the Moodle site.'),
            'course_image' => new external_value(PARAM_URL, 'Link to the course image.'),
            'category' => new external_value(PARAM_TEXT, 'Name of the direct parent category of the course.'),
            'self_enrolment_method_id' => new external_value(PARAM_INT, 'ID of the self enrolment method.'),
            'self_enrolment_method_name' => new external_value(PARAM_TEXT, 'Name of the self enrolment method.'),
            'self_enrolment_method_type' => new external_value(PARAM_TEXT,
                'Self enrolment method type. Can be either self or guest.'),
            'self_enrolment_method_status' => new external_value(PARAM_TEXT,
                'Boolean true if the user can self enrol, false if the user can\'t, or a string if there is an error.'),
        ];
    }

    /**
     * Extract self enrolment method data.
     *
     * @param array $enrolmentmethoddata
     * @return array
     */
    public static function extract_self_enrolment_method_data(array $enrolmentmethoddata): array {
        global $DB;

        $course = $DB->get_record('course', ['id' => $enrolmentmethoddata['courseid']], '*', MUST_EXIST);
        $courseurl = new moodle_url('/course/view.php', ['id' => $course->id]);
        $coursecategoryname = $DB->get_field('course_categories', 'name', ['id' => $course->category], MUST_EXIST);

        // Get course image URL.
        $courseimageurl = '';
        $fs = get_file_storage();
        $coursecontext = context_course::instance($course->id);
        $courseoverviewfiles = $fs->get_area_files($coursecontext->id, 'course', 'overviewfiles', 0);
        if ($courseoverviewfiles) {
            foreach ($courseoverviewfiles as $file) {
                if ($file->is_directory()) {
                    continue;
                }
                $courseimageurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(),
                    $file->get_filearea(), null, $file->get_filepath(), $file->get_filename());
                $courseimageurl = $courseimageurl->out(false);
                break;
            }
        }

        return [
            'course_name' => $course->fullname,
            'course_id' => $course->id,
            'course_link' => $courseurl->out(false),
            'course_shortname' => $course->shortname,
            'course_image' => $courseimageurl,
            'category' => $coursecategoryname,
            'self_enrolment_method_id' => $enrolmentmethoddata['id'],
            'self_enrolment_method_name' => $enrolmentmethoddata['name'],
            'self_enrolment_method_type' => $enrolmentmethoddata['type'],
            'self_enrolment_method_status' => $enrolmentmethoddata['status'],
        ];
    }
}
