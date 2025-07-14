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
 * Web service function to get user activities by type for students.
 *
 * @package local_copilot
 * @author Dorel Manolescu <dorel.manolescu@enovation.ie>
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license https://opensource.org/license/MIT MIT License
 * @copyright (C) 2014 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot\external;

defined('MOODLE_INTERNAL') || die();

use context_course;
use context_system;
use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use core_external\external_value;
use local_copilot\local\resource\base_activity;
use local_copilot\local\resource\student_activity;
use local_copilot\local\resource\base_activity_course;

require_once($CFG->libdir . '/externallib.php');

/**
 * Web service class definition.
 */
class get_activities_by_type_for_student extends external_api {
    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'activity_type' => new external_value(PARAM_TEXT,
                'The code name or display name of the activity type, e.g. forum, assignment, quiz, etc. Use singular form.'),
            'limit' => new external_value(PARAM_INT, 'Number of activities to return per request', VALUE_DEFAULT, 10),
            'offset' => new external_value(PARAM_INT, 'Starting point for fetching the next batch of activities', VALUE_DEFAULT,
                0),
        ]);
    }

    /**
     * Returns description of method response value.
     *
     * @return external_multiple_structure
     */
    public static function execute_returns(): external_multiple_structure {
        return new external_multiple_structure(
            new external_single_structure(
                array_merge(
                    base_activity::get_return_structure(),
                    student_activity::get_return_structure(),
                    base_activity_course::get_return_structure(),
                    [
                        'has_more' => new external_value(PARAM_BOOL, 'Flag indicating whether there are more activities to fetch.'),
                    ],
                )
            )
        );
    }

    /**
     * Return the list of activities of the given type for the current user.
     *
     * @param string $activitytype
     * @param int $limit
     * @param int $offset
     * @return array|null
     * @uses die
     */
    public static function execute(string $activitytype, int $limit = 10, int $offset = 0): ?array {
        global $DB, $USER;

        $moodlelimit = 10;
        $microconfig = get_config('local_copilot');
        if ($microconfig->paginationlimit) {
            $moodlelimit = $microconfig->paginationlimit;
        }

        // Validate parameters.
        $params = self::validate_parameters(self::execute_parameters(),
            ['activity_type' => $activitytype, 'limit' => $limit, 'offset' => $offset]);
        $activitytype = $params['activity_type'];
        $limit = $params['limit'];
        $limit = (!empty($limit) && is_numeric($limit)) ? $limit : $moodlelimit;
        $offset = $params['offset'];
        $offset = (!empty($offset) && is_numeric($offset)) ? $offset : 0;

        // Perform security checks.
        $context = context_system::instance();
        self::validate_context($context);

        // Check if activity type exists in modules table.
        $module = $DB->get_record('modules', ['name' => $activitytype]);
        if (!$module) {
            // Get all modules names.
            $modules = $DB->get_records('modules');
            foreach ($modules as $mod) {
                $modulelangname = get_string('pluginname', $mod->name);
                if (strtolower($modulelangname) == strtolower($activitytype)) {
                    $module = $mod;
                    break;
                }
            }

            if (!$module) {
                header('HTTP/1.0 404 activity not found');
                die();
            }
        }

        // Get all activity instances of the type.
        $coursemodules = $DB->get_records('course_modules', ['module' => $module->id]);
        $courses = enrol_get_users_courses($USER->id, true, ['enddate']);

        foreach ($coursemodules as $cmid => $coursemodule) {
            if (!array_key_exists($coursemodule->course, $courses)) {
                // Remove the activity if the user is not enrolled in the course.
                unset($coursemodules[$cmid]);
            }
        }

        $activitydata = [];
        if (!empty($coursemodules)) {
            foreach ($coursemodules as $coursemodule) {
                $course = $DB->get_record('course', ['id' => $coursemodule->course]);
                $coursecontext = context_course::instance($course->id);

                // Check if the user is enrolled in the course as student and does not have course update capability.
                if (!has_capability('moodle/course:update', $coursecontext)) {
                    $courses[$course->id] = $course;

                    $coursemodule->coursename = $course->fullname;
                    $coursemodule->modname = $module->name;

                    $activitydata[] = array_merge(
                        base_activity::extract_activity_data($coursemodule, $USER->id),
                        student_activity::extract_student_activity_data($coursemodule, $USER->id),
                        base_activity_course::extract_activity_course_data($coursemodule)
                    );
                }
            }
        }

        $hasmore = ($offset + $limit) < count($activitydata);
        $activitydata = array_slice($activitydata, $offset, $limit);
        foreach ($activitydata as &$activity) {
            $activity['has_more'] = $hasmore;
        }

        return $activitydata;
    }
}
