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
 * Web service function to return assignment details for student.
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
use context_module;
use external_api;
use external_function_parameters;
use external_single_structure;
use core_external\external_value;
use local_copilot\resource\base_assignment_activity;
use local_copilot\resource\student_assignment_activity;

require_once($CFG->libdir . '/externallib.php');

/**
 * Web service class definition.
 */
class get_assignment_details_for_student extends external_api {
    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'activity_id' => new external_value(PARAM_INT, 'Moodle assignment activity ID', VALUE_REQUIRED),
        ]);
    }

    /**
     * Return user assignment metadata.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure(
            array_merge(
                base_assignment_activity::get_return_structure(),
                student_assignment_activity::get_return_structure()
            )
        );
    }

    /**
     * Return assignment details for student.
     *
     * @param int $activityid
     * @return array
     */
    public static function execute(int $activityid): array {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), ['activity_id' => $activityid]);
        $assignmentid = $params['activity_id'];

        $assignment = $DB->get_record('assign', ['id' => $assignmentid]);

        if (!$assignment) {
            // Fall back to use course module ID.
            if ($cm = get_coursemodule_from_id('assign', $assignmentid)) {
                $assignment = $DB->get_record('assign', ['id' => $cm->instance]);
            } else {
                header('HTTP/1.0 404 assignment not found');
                die;
            }
        }

        $coursecontext = context_course::instance($assignment->course);
        $cm = get_coursemodule_from_instance('assign', $assignment->id, $assignment->course);

        // Check if user has access to the assignment and can submit mod/assign:submit.
        if (!has_capability('mod/assign:submit', context_module::instance($cm->id))) {
            header('HTTP/1.0 403 user does not have access to the assignment');
            die;
        }

        // Check if user is enrolled in the course.
        $roles = get_user_roles($coursecontext);
        if (!$roles) {
            header('HTTP/1.0 403 user is not enrolled');
            die;
        }

        // Check if user is student.
        $isstudent = false;
        foreach ($roles as $role) {
            if ($role->shortname == 'student') {
                $isstudent = true;
                break;
            }
        }

        if (!$isstudent) {
            header('HTTP/1.0 403 user is not a student');
            die;
        }

        $coursedata = $DB->get_record('course', ['id' => $assignment->course], 'id, fullname', MUST_EXIST);

        $assignment->section = $cm->section;
        $assignment->courseid = $coursedata->id;
        $assignment->coursename = $coursedata->fullname;
        $assignment->availability = $cm->availability;
        $assignment->userid = $USER->id;
        $assignment->completion_enabled = (bool) $cm->completion;

        $studnetassignmentactivity = array_merge(
            base_assignment_activity::extract_assignment_activity_data($assignment, $cm, $coursedata),
            student_assignment_activity::extract_assignment_activity_data($assignment, $cm)
        );

        return $studnetassignmentactivity;
    }
}
