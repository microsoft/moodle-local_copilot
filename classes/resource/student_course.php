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
 * Resource type for additional course information for students, to be used by web service return data in local_copilot.
 *
 * @package local_copilot
 * @author Lai Wei <lai.wei@enovation.ie
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2014 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot\resource;

defined('MOODLE_INTERNAL') || die();

use external_value;
use stdClass;

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->libdir . '/accesslib.php');

/**
 * Course resource type.
 */
class student_course implements resource_type {
    /**
     * Get the resource return structure.
     *
     * @return array
     */
    public static function get_return_structure(): array {
        return [
            'grade' => new external_value(PARAM_TEXT,
                'Grade of the student in the course. If the student has not received a grade, it is set to \'-1\'.'),
            'completed' => new external_value(PARAM_BOOL, 'Whether the student has completed the course.'),
            'completion_datetime' => new external_value(PARAM_INT,
                'If the student has completed the course, the completion date time in unix timestamp.'),
        ];
    }

    /**
     * Extract course data.
     *
     * @param stdClass $course
     * @param int $userid
     * @return array
     */
    public static function extract_course_data(stdClass $course, int $userid) {
        global $DB;

        // Get user course grade.
        $grade = -1;
        if ($coursegradeitemid = $DB->get_field('grade_items', 'id', ['courseid' => $course->id, 'itemtype' => 'course'])) {
            $grade = $DB->get_field('grade_grades', 'finalgrade', ['itemid' => $coursegradeitemid, 'userid' => $userid]);
        }
        if (!$grade) {
            $grade = -1;
        }

        // Get user course completion status.
        $completed = false;
        $completiondatetime = 0;
        if ($timecompleted = $DB->get_field('course_completions', 'timecompleted',
            ['course' => $course->id, 'userid' => $userid])) {
            if ($timecompleted) {
                $completed = true;
                $completiondatetime = $timecompleted;
            }
        }

        return [
            'grade' => $grade,
            'completed' => $completed,
            'completion_datetime' => $completiondatetime,
        ];
    }
}
