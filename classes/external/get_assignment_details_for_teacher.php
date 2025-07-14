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
 * Web service function to return assignment details for teacher.
 *
 * @package local_copilot
 * @author Dorel Manolescu <dorel.manolescu@enovation.ie>
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license https://opensource.org/license/MIT MIT License
 * @copyright (C) 2014 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot\external;

use context_course;
use core_external\external_value;
use external_api;
use external_function_parameters;
use external_single_structure;
use local_copilot\local\resource\base_assignment_activity;
use local_copilot\local\resource\teacher_assignment_activity;
use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/format/lib.php');
require_once($CFG->libdir . '/externallib.php');

/**
 * Web service class definition.
 */
class get_assignment_details_for_teacher extends external_api {
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
     * Returns descriptions of method return value.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure(
            array_merge(
                base_assignment_activity::get_return_structure(),
                teacher_assignment_activity::get_return_structure()
            )
        );
    }

    /**
     * Returns assignment details and the list of submissions.
     *
     * @param int $activityid
     * @return array
     * @uses die
     */
    public static function execute(int $activityid): array {
        global $DB;

        // Validate parameters.
        $params = self::validate_parameters(self::execute_parameters(), ['activity_id' => $activityid]);
        $assignmentid = $params['activity_id'];

        $assignment = $DB->get_record('assign', ['id' => $assignmentid]);

        if (!$assignment) {
            // Fall back to use course module ID.
            if ($cm = get_coursemodule_from_id('assign', $assignmentid)) {
                $assignment = $DB->get_record('assign', ['id' => $cm->instance]);
            } else {
                header('HTTP/1.0 404 assignment not found');
                die();
            }
        }

        // Perform security checks.
        $coursecontext = context_course::instance($assignment->course);
        self::validate_context($coursecontext);
        // Check if user has course update capability.
        if (!has_capability('moodle/course:update', $coursecontext)) {
            header('HTTP/1.0 403 user does not have course update capability');
            die();
        }
        $cm = get_coursemodule_from_instance('assign', $assignment->id, $assignment->course);

        if (!$cm) {
            header('HTTP/1.0 404 assignment not found');
            die();
        }

        $coursedata = $DB->get_record('course', ['id' => $assignment->course], 'id, fullname', MUST_EXIST);

        $teacherassignmentactivity = array_merge(
            base_assignment_activity::extract_assignment_activity_data($assignment, $cm, $coursedata),
            teacher_assignment_activity::extract_teacher_assignment_activity_data($assignment, $cm, $coursedata, $coursecontext)
        );

        return $teacherassignmentactivity;
    }
}
