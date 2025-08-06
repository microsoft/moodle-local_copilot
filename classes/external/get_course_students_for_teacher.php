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
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2014 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot\external;

defined('MOODLE_INTERNAL') || die();

use context_course;
use core_external\external_value;
use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use moodle_url;

require_once($CFG->libdir . '/externallib.php');

/**
 * Web service class definition.
 */
class get_course_students_for_teacher extends external_api {
    /**
     * Return description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'course_id' => new external_value(PARAM_INT, 'ID of the Moodle course', VALUE_REQUIRED),
            'limit' => new external_value(PARAM_INT, 'Number of students to return per request', VALUE_DEFAULT, 10),
            'offset' => new external_value(PARAM_INT, 'Starting point for fetching the next batch of students', VALUE_DEFAULT, 0),
        ]);
    }

    /**
     * Return descriptions of method return value.
     *
     * @return external_multiple_structure
     */
    public static function execute_returns(): external_multiple_structure {
        return new external_multiple_structure(
            new external_single_structure([
                'student_full_name' => new external_value(PARAM_TEXT, 'Full name of the student.'),
                'student_id' => new external_value(PARAM_INT, 'Moodle user ID of the student.'),
                'student_username' => new external_value(PARAM_TEXT, 'Username of the student.'),
                'student_link' => new external_value(PARAM_URL, 'Link to the student profile page.'),
                'student_roles' => new external_value(PARAM_TEXT,
                    'The name of the roles that the student has in course, separated by commas.'),
                'has_more' => new external_value(PARAM_BOOL, 'Flag indicating whether there are more students to fetch.'),
            ])

        );
    }

    /**
     * Returns list of students enrolled in a course.
     *
     * @param int $courseid
     * @param int $limit
     * @param int $offset
     * @return array|null
     * @uses die
     */
    public static function execute(int $courseid, int $limit = 10, int $offset = 0): ?array {
        global $DB;

        $moodlelimit = 10;
        $microconfig = get_config('local_copilot');
        if ($microconfig->paginationlimit) {
            $moodlelimit = $microconfig->paginationlimit;
        }

        // Validate parameters.
        $params = self::validate_parameters(self::execute_parameters(),
            ['course_id' => $courseid, 'limit' => $limit, 'offset' => $offset]);
        $courseid = $params['course_id'];
        $limit = $params['limit'];
        $limit = (!empty($limit) && is_numeric($limit)) ? $limit : $moodlelimit;
        $offset = $params['offset'];
        $offset = (!empty($offset) && is_numeric($offset)) ? $offset : 0;

        $students = [];

        // Validate course.
        $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
        if (!$course) {
            header('HTTP/1.0 404 course not found');
            die();
        }

        // Perform security checks.
        $coursecontext = context_course::instance($courseid);
        self::validate_context($coursecontext);
        // Check if the user is a teacher.
        if (!has_capability('moodle/course:viewparticipants', $coursecontext)) {
            header('HTTP/1.0 403 the user cannot view participants in this course');
            die();
        }

        $enrolledusers = get_enrolled_users($coursecontext, '', 0, 'u.*', null, 0, 0, true);
        foreach ($enrolledusers as $enrolleduser) {
            // Exclude teachers.
            if (has_capability('moodle/course:update', $coursecontext, $enrolleduser)) {
                continue;
            }

            // Prepare profile page URL.
            $profilepageurl = new moodle_url('/user/view.php', ['id' => $enrolleduser->id, 'course' => $courseid]);

            // Prepare roles.
            $rolesvalue = '';
            $roles = get_user_roles($coursecontext, $enrolleduser->id);
            if ($roles) {
                foreach ($roles as $userrole) {
                    $rolesvalue .= role_get_name($userrole) . ',';
                }
                $rolesvalue = substr($rolesvalue, 0, -1);
            }

            $students[] = [
                'student_full_name' => fullname($enrolleduser),
                'student_id' => $enrolleduser->id,
                'student_username' => $enrolleduser->username,
                'student_link' => $profilepageurl->out(false),
                'student_roles' => $rolesvalue,
            ];
        }

        $hasmore = ($offset + $limit) < count($students);
        $students = array_slice($students, $offset, $limit);
        foreach ($students as &$student) {
            $student['has_more'] = $hasmore;
        }

        return $students;
    }
}
