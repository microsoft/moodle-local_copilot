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
 * Resource type "course" for web service return data in local_copilot.
 *
 * @package local_copilot
 * @author Lai Wei <lai.wei@enovation.ie
 * @license https://opensource.org/license/MIT MIT License
 * @copyright (C) 2014 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot\local\resource;

defined('MOODLE_INTERNAL') || die();

use context_course;
use external_value;
use moodle_url;
use stdClass;

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->libdir . '/accesslib.php');

/**
 * Course resource type.
 */
class base_course implements resource_type {
    /**
     * Get the resource return structure.
     *
     * @return array
     */
    public static function get_return_structure(): array {
        return [
            'course_name' => new external_value(PARAM_TEXT, 'Full name of the course.'),
            'course_id' => new external_value(PARAM_INT, 'ID of the course.'),
            'course_shortname' => new external_value(PARAM_TEXT, 'Short name of the course, which is unique on the Moodle site.'),
            'course_link' => new external_value(PARAM_URL, 'Link to the course.'),
            'course_summary' => new external_value(PARAM_TEXT, 'Summary of the course in HTML format.'),
            'course_image' => new external_value(PARAM_URL, 'Link to the course image.', VALUE_OPTIONAL),
            'category' => new external_value(PARAM_TEXT, 'Name of the direct parent category of the course.'),
            'visibility' => new external_value(PARAM_BOOL, 'Whether the course is visible to students.'),
            'start_datetime' => new external_value(PARAM_INT, 'Start date time of the course in unix timestamp.'),
            'end_datetime' => new external_value(PARAM_INT,
                'End date time of the course in unix timestamp. If the course does not have an end date, it is set to 0.'),
            'completion_enabled' => new external_value(PARAM_BOOL, 'Whether completion tracking is enabled for the course.'),
            'roles' => new external_value(PARAM_TEXT, 'The name of the roles that the user has in course, separated by commas.'),
        ];
    }

    /**
     * Extract course data.
     *
     * @param stdClass $course
     * @param int|null $userid
     * @return array
     */
    public static function extract_course_data(stdClass $course, int|null $userid = null): array {
        global $DB;

        // Get the course category name.
        $categoryname = '';
        if ($category = $DB->get_record('course_categories', ['id' => $course->category])) {
            $categoryname = $category->name;
        }

        // Get the course link.
        $courselink = new moodle_url('/course/view.php', ['id' => $course->id]);

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

        $rolesvalue = '';
        if ($userid) {
            // Get user role in course.
            $context = context_course::instance($course->id);
            $roles = get_user_roles($context);
            if ($roles) {
                foreach ($roles as $userrole) {
                    $rolesvalue .= role_get_name($userrole) . ',';
                }
                $rolesvalue = substr($rolesvalue, 0, -1);
            }
        }

        return [
            'course_name' => $course->fullname,
            'course_id' => $course->id,
            'course_shortname' => $course->shortname,
            'course_link' => $courselink->out(false),
            'course_summary' => strip_tags($course->summary),
            'course_image' => $courseimageurl,
            'category' => $categoryname,
            'visibility' => $course->visible,
            'start_datetime' => $course->startdate,
            'end_datetime' => $course->enddate,
            'completion_enabled' => $course->enablecompletion,
            'roles' => $rolesvalue,
        ];
    }
}
