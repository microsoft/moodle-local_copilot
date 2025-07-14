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
 * Resource type "teacher_assignment_activity" for web service return data in local_copilot.
 *
 * @package local_copilot
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license https://opensource.org/license/MIT MIT License
 * @copyright (C) 2014 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot\local\resource;

use context_course;
use external_multiple_structure;
use external_single_structure;
use external_value;
use grade_item;
use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/format/lib.php');
require_once($CFG->libdir . '/accesslib.php');
require_once($CFG->libdir . '/enrollib.php');

/**
 * Class teacher_assignment_activity.
 * Teacher assignment activity resource type.
 */
class teacher_assignment_activity implements resource_type {
    /**
     * Get return structure for teacher assignment activity.
     *
     * @return array
     */
    public static function get_return_structure(): array {
        return [
            'submissions' => new external_multiple_structure(
                new external_single_structure(
                    assign_submission::get_return_structure()
                )
            ),
            'submissions_count' => new external_value(PARAM_INT, 'The number of submissions for the assignment activity.'),
            'graded_submissions_count' => new external_value(PARAM_INT,
                'The number of graded submissions for the assignment activity.'),
            'average_grade' => new external_value(PARAM_TEXT,
                'Average grade for the assignment activity, or \'n/a\' if no grades have been given.'),
            'completed_users_count' => new external_value(PARAM_INT,
                'The number of users who have completed the assignment activity.'),

        ];
    }

    /**
     * Extract assignment activity data for teacher.
     *
     * @param stdClass $assignmentactivity
     * @param stdClass $coursemodule
     * @param stdClass $coursedata
     * @param context_course $coursecontext
     * @return array
     */
    public static function extract_teacher_assignment_activity_data(stdClass $assignmentactivity, stdClass $coursemodule,
        stdClass $coursedata, context_course $coursecontext): array {
        global $DB;

        // Get enrolled users in the course.
        $enrolledusers = get_enrolled_users($coursecontext, '', 0, 'u.id, u.username, u.email, u.firstname, u.lastname');
        $submissions = [];
        if ($enrolledusers) {
            foreach ($enrolledusers as $enrolleduser) {
                if (!has_capability('moodle/course:update', $coursecontext, $enrolleduser->id)) {
                    $submissiondata = assign_submission::extract_activity_data((object)[
                        'id' => $assignmentactivity->id,
                        'userid' => $enrolleduser->id,
                        'courseid' => $coursedata->id,
                        'fullname' => fullname($enrolleduser),
                    ], $coursemodule);
                    if ($submissiondata['submitted']) {
                        $submissions[] = $submissiondata;
                    }
                }
            }
        }

        $gradedsubmissionscount = 0;
        $completeduserscount = 0;
        $averagegrade = 'n/a';
        $sumgrades = 0;

        if ($gradeitem = grade_item::fetch(['itemtype' => 'mod', 'iteminstance' => $coursemodule->instance,
            'itemmodule' => 'assign'])) {
            $sql = 'SELECT userid, finalgrade
                      FROM {grade_grades}
                     WHERE itemid = :itemid
                       AND finalgrade IS NOT NULL';
            $params = ['itemid' => $gradeitem->id];
            $activitygrades = $DB->get_records_sql($sql, $params);

            if ($activitygrades) {
                foreach ($activitygrades as $activitygrade) {
                    if ($activitygrade->finalgrade) {
                        $gradedsubmissionscount++;
                        $sumgrades += (float) $activitygrade->finalgrade;
                    }
                }
                $averagegrade = $sumgrades / $gradedsubmissionscount;
            }
        }

        // Get completed users.
        if ($coursemodule->completion) {
            $sql = 'SELECT COUNT(DISTINCT userid) AS completeduserscount
                      FROM {course_modules_completion}
                     WHERE coursemoduleid = :coursemoduleid
                       AND completionstate = 1';
            $params = ['coursemoduleid' => $coursemodule->id];
            $completeduserscount = $DB->count_records_sql($sql, $params);
        }

        $assignmentactivitydata = [
            'submissions' => $submissions,
            'submissions_count' => count($submissions),
            'graded_submissions_count' => $gradedsubmissionscount,
            'average_grade' => $averagegrade,
            'completed_users_count' => $completeduserscount,
        ];

        return $assignmentactivitydata;
    }
}
