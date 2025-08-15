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
 * Resource type "student_assignment_activity" for web service return data in local_copilot.
 *
 * @package local_copilot
 * @author Dorel Manolescu <dorel.manolescu@enovation.ie>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2014 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot\local\resource;

defined('MOODLE_INTERNAL') || die();

use external_value;
use stdClass;
use mod_assign\external\external_api;

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->dirroot . '/course/format/lib.php');
require_once($CFG->dirroot . '/mod/assign/externallib.php');
require_once($CFG->dirroot . '/grade/querylib.php');

/**
 * Class student_assignment_activity.
 * Student assignment activity resource type.
 */
class student_assignment_activity implements resource_type {
    /**
     * Get the resource return structure.
     *
     * @return array
     */
    public static function get_return_structure(): array {
        return [
            'completed' => new external_value(
                PARAM_BOOL,
                'If completion tracking is enabled in the assignment activity, whether the student has completed the activity.'
            ),
            'completion_datetime' => new external_value(
                PARAM_INT,
                'If completion tracking is enabled in the assignment activity, and the student has completed the activity, ' .
                'the completion date time in unix timestamp.'
            ),
            'submitted' => new external_value(PARAM_BOOL, 'Whether the student has submitted the assignment.'),
            'submission_datetime' => new external_value(
                PARAM_INT,
                'If the student has submitted the assignment, the submission date time in unix timestamp.'
            ),
            'activity_grade' => new external_value(
                PARAM_TEXT,
                'If the student has received a grade for the assignment activity, the grade; otherwise \'-1\'.'
            ),
        ];
    }

    /**
     * Extract student assign activity data.
     *
     * @param stdClass $assignmentactivity
     * @param stdClass $cm
     * @return array
     */
    public static function extract_assignment_activity_data(stdClass $assignmentactivity, stdClass $cm): array {
        global $DB;

        $submissionstatus = external_api::call_external_function(
            'mod_assign_get_submission_status',
            [
                'assignid' => $assignmentactivity->id,
                'userid' => $assignmentactivity->userid,
            ]
        );

        $submitted = false;
        $submitteddatetime = 0;
        $finalgrade = -1;

        // Check if the call was successful and data exists.
        if (empty($submissionstatus['error']) && isset($submissionstatus['data'])) {
            $data = $submissionstatus['data'];

            // Safely extract submission status and datetime.
            if (isset($data['lastattempt']['submission']['status'])) {
                $submissionstatusvalue = $data['lastattempt']['submission']['status'];

                if ($submissionstatusvalue == 'submitted') {
                    $submitted = true;
                    $submitteddatetime = $data['lastattempt']['submission']['timemodified'] ?? 0;

                    if (grade_is_user_graded_in_activity($cm, $assignmentactivity->userid)) {
                        $grade = grade_get_grades(
                            $assignmentactivity->courseid,
                            'mod',
                            'assign',
                            $assignmentactivity->id,
                            $assignmentactivity->userid
                        );
                        $finalgrade = $grade->items[0]->grades[$assignmentactivity->userid]->grade;
                    }
                }
            }
        }

        // Get the activity completion status.
        $completionstatus = $DB->get_record(
            'course_modules_completion',
            ['coursemoduleid' => $cm->id, 'userid' => $assignmentactivity->userid]
        );

        if (!$completionstatus) {
            // If there is no completion status, assume not completed.
            $completionstatus = new stdClass();
            $completionstatus->completionstate = false;
            $completionstatus->timemodified = 0;
        }

        $assignmentactivitydata = [];
        $assignmentactivitydata['completed'] = $completionstatus->completionstate == 1;
        $assignmentactivitydata['completion_datetime'] = $completionstatus->timemodified;
        $assignmentactivitydata['submitted'] = $submitted;
        $assignmentactivitydata['submission_datetime'] = $submitteddatetime;
        $assignmentactivitydata['activity_grade'] = $finalgrade;

        return $assignmentactivitydata;
    }
}
