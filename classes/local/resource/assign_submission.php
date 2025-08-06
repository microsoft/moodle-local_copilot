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
 * Resource type "assignment submission" for web service return data in local_copilot.
 *
 * @package local_copilot
 * @author Dorel Manolescu <dorel.manolescu@enovation.ie>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2014 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot\local\resource;

defined('MOODLE_INTERNAL') || die();

use external_value;
use mod_assign\external\external_api;
use stdClass;

require_once($CFG->dirroot . '/mod/assign/externallib.php');
require_once($CFG->dirroot . '/grade/querylib.php');

/**
 * User assignment submission resource type.
 */
class assign_submission implements resource_type {
    /**
     * Get the resource return structure.
     *
     * @return array
     */
    public static function get_return_structure(): array {
        return [
            /* Exclude student details in the current phase.
            'student_full_name' => new external_value(PARAM_TEXT, 'Full name of the student who submitted the assignment.'),
            */
            'student_user_id' => new external_value(PARAM_INT, 'ID of the student who submitted the assignment.'),
            'submitted' => new external_value(PARAM_TEXT, 'Whether the assignment is submitted by the student.'),
            'submission_datetime' => new external_value(PARAM_INT,
                'If a submission has been made, the submission date time in unix timestamp.'),
            'activity_grade' => new external_value(PARAM_TEXT, 'The grade of the submission.'),
            'completed' => new external_value(PARAM_BOOL, 'Whether the assignment activity is completed by the student.'),
            'completion_datetime' => new external_value(PARAM_INT,
                'If the student has completed the assignment activity, the completion date time in unix timestamp.'),
        ];
    }

    /**
     * Extract user assign submission data.
     *
     * @param stdClass $record
     * @param stdClass $cm
     * @return array
     */
    public static function extract_activity_data(stdClass $record, $cm): array {
        global $DB;

        $submissionstatus = external_api::call_external_function(
            'mod_assign_get_submission_status',
            [
                'assignid' => $record->id,
                'userid' => $record->userid,
            ]
        );

        $submissiondatetime = 0;
        $finalgrade = -1;
        if ($submissionstatus['data']['lastattempt']['submission']['status'] == 'submitted') {
            $submissiondatetime = $submissionstatus['data']['lastattempt']['submission']['timemodified'];
            if (grade_is_user_graded_in_activity($cm, $record->userid)) {
                $grade = grade_get_grades($record->courseid, 'mod', 'assign',
                    $record->id, $record->userid);
                $finalgrade = $grade->items[0]->grades[$record->userid]->grade;
            }
        }

        // Get the activity completion status.
        $completionstatus = $DB->get_record('course_modules_completion',
            ['coursemoduleid' => $cm->id, 'userid' => $record->userid]);

        if (!$completionstatus) {
            $completionstatus = new stdClass();
            $completionstatus->completionstate = false;
            $completionstatus->timemodified = 0;
        }

        return [
            /* Exclude student details in the current phase.
            'student_full_name' => $record->fullname,
            */
            'student_user_id' => $record->userid,
            'submitted' => $submissionstatus['data']['lastattempt']['submission']['status'],
            'submission_datetime' => $submissiondatetime,
            'activity_grade' => $finalgrade,
            'completed' => $completionstatus->completionstate == 1,
            'completion_datetime' => $completionstatus->timemodified,
        ];
    }
}
