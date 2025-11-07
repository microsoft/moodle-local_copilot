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
 * Additional activity information for teachers in for web service return data in local_copilot.
 *
 * @package local_copilot
 * @author Dorel Manolescu <dorel.manolescu@enovation.ie>
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2014 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot\local\resource;

defined('MOODLE_INTERNAL') || die();

use external_value;
use stdClass;
use grade_item;

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->libdir . '/gradelib.php');

/**
 * Teacher activity resource type.
 */
class teacher_activity implements resource_type {
    /**
     * Get the resource return structure.
     *
     * @return array
     */
    public static function get_return_structure(): array {
        return [
            'graded_users_count' => new external_value(
                PARAM_INT,
                'The number of users who have received a grade for the activity.'
            ),
            'average_grade' => new external_value(
                PARAM_TEXT,
                'Average grade for the activity, or \'n/a\' if no grades have been given.'
            ),
            'completed_users_count' => new external_value(PARAM_INT, 'The number of users who have completed the activity.'),
        ];
    }

    /**
     * Extract teacher activity data.
     *
     * @param stdClass $coursemodule
     * @return array|null
     */
    public static function extract_teacher_activity_data(stdClass $coursemodule): ?array {
        global $DB;

        $grdeduserscount = 0;
        $completeduserscount = 0;
        $averagegrade = 'n/a';
        $sumgrades = 0;

        if ($module = $DB->get_field('modules', 'name', ['id' => $coursemodule->module])) {
            // Get graded users and average grade.
            if (
                $gradeitem = grade_item::fetch(
                    [
                        'itemtype' => 'mod',
                        'iteminstance' => $coursemodule->instance,
                        'itemmodule' => $module,
                    ]
                )
            ) {
                $sql = 'SELECT userid, finalgrade
                          FROM {grade_grades}
                         WHERE itemid = :itemid
                           AND finalgrade IS NOT NULL';
                $params = ['itemid' => $gradeitem->id];
                $activitygrades = $DB->get_records_sql($sql, $params);

                if ($activitygrades) {
                    foreach ($activitygrades as $activitygrade) {
                        if ($activitygrade->finalgrade) {
                            $grdeduserscount++;
                            $sumgrades += (float) $activitygrade->finalgrade;
                        }
                    }
                    $averagegrade = $sumgrades / $grdeduserscount;
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
        }

        return [
            'graded_users_count' => $grdeduserscount,
            'average_grade' => $averagegrade,
            'completed_users_count' => $completeduserscount,
        ];
    }
}
