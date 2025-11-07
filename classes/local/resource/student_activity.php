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
 * Additional activity information for students in web service return data in local_copilot for students.
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

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->dirroot . '/grade/querylib.php');

/**
 * Student activity resource type.
 */
class student_activity implements resource_type {
    /**
     * Get the resource return structure.
     *
     * @return array
     */
    public static function get_return_structure(): array {
        return [
            'completed' => new external_value(
                PARAM_BOOL,
                'If completion tracking is enabled in the activity, whether the student has completed the activity.'
            ),
            'completion_datetime' => new external_value(
                PARAM_INT,
                'If completion tracking is enabled in the activity, and the student has completed the activity, ' .
                'the completion date time in unix timestamp.'
            ),
            'activity_grade' => new external_value(
                PARAM_TEXT,
                'If the student has received a grade for the activity, the grade; otherwise \'-1\'.'
            ),
        ];
    }

    /**
     * Extract student activity data.
     *
     * @param stdClass $coursemodule
     * @param int $userid
     * @return array
     */
    public static function extract_student_activity_data(stdClass $coursemodule, int $userid): array {
        global $DB;

        // Get the activity completion status.
        $completionstatus = $DB->get_record(
            'course_modules_completion',
            ['coursemoduleid' => $coursemodule->id, 'userid' => $userid]
        );

        $finalgrade = -1;

        if (grade_is_user_graded_in_activity($coursemodule, $userid)) {
            $grade = grade_get_grades(
                $coursemodule->course,
                'mod',
                $coursemodule->modname,
                $coursemodule->instance,
                $userid
            );
            if ($grade) {
                $finalgrade = $grade->items[0]->grades[$userid]->grade;
            }
        }

        if (!$completionstatus) {
            // If there is no completion status, assume not completed.
            $completionstatus = new stdClass();
            $completionstatus->completionstate = false;
            $completionstatus->timemodified = 0;
        }
        return [
            'completed' => $completionstatus->completionstate == 1,
            'completion_datetime' => $completionstatus->timemodified,
            'activity_grade' => $finalgrade,
        ];
    }
}
