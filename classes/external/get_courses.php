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
 * Web service function to get all courses for a user.
 *
 * @package local_copilot
 * @author Dorel Manolescu <dorel.manolescu@enovation.ie>
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license https://opensource.org/license/MIT MIT License
 * @copyright (C) 2014 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot\external;

defined('MOODLE_INTERNAL') || die();

use external_api;
use external_value;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use local_copilot\resource\base_course;

require_once($CFG->libdir . '/externallib.php');

/**
 * Web service class definition.
 */
class get_courses extends external_api {
    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'limit' => new external_value(PARAM_INT, 'Number of courses to return per request', VALUE_OPTIONAL, 10),
            'offset' => new external_value(PARAM_INT, 'Starting point for fetching the next batch of courses', VALUE_OPTIONAL, 0),
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
                    base_course::get_return_structure(),
                    [
                        'has_more' => new external_value(PARAM_BOOL, 'Whether there are more courses to fetch.'),
                    ],
                )
            )
        );
    }

    /**
     * Returns list of user courses.
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public static function execute(int $limit = 10, int $offset = 0): array {
        global $USER;

        $moodlelimit = 10;
        $microconfig = get_config('local_copilot');
        if ($microconfig->paginationlimit) {
            $moodlelimit = $microconfig->paginationlimit;
        }

        // Validate input.
        $limit = (!empty($limit) && is_numeric($limit)) ? intval($limit) : $moodlelimit;
        $offset = (!empty($offset) && is_numeric($offset)) ? intval($offset) : 0;
        // Get the courses where the user is enrolled.
        $courses = enrol_get_users_courses($USER->id, true, ['enddate']);

        $coursedata = [];
        $totalcourses = count($courses);
        $hasmore = ($offset + $limit) < $totalcourses;

        if (!empty($courses)) {
            $courses = array_slice($courses, $offset, $limit);
            foreach ($courses as $course) {
                $data = base_course::extract_course_data($course, $USER->id);
                unset($data['course_image']); // Remove the course_image field for this implementation.
                $coursedata[] = array_merge(
                    $data,
                    ['has_more' => $hasmore]
                );
            }
        }

        return $coursedata;
    }
}
