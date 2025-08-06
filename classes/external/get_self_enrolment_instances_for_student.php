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
 * Web service function to get self enrolment instances for student.
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
use context_system;
use core_enrol_external;
use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use local_copilot\local\resource\self_enrolment_method;

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/enrol/externallib.php');

/**
 * Web service class definition.
 */
class get_self_enrolment_instances_for_student extends external_api {
    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'limit' => new external_value(PARAM_INT, 'Number of self enrolment instances to return per request', VALUE_DEFAULT,
                10),
            'offset' => new external_value(PARAM_INT, 'Starting point for fetching the next batch of self enrolment instances',
                VALUE_DEFAULT, 0),
        ]);
    }

    /**
     * Returns list of available self enrolment methods.
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public static function execute(int $limit = 10, int $offset = 0): array {
        global $USER, $DB;

        $moodlelimit = 10;
        $microconfig = get_config('local_copilot');
        if ($microconfig->paginationlimit) {
            $moodlelimit = $microconfig->paginationlimit;
        }

        // Validate parameters.
        $params = self::validate_parameters(self::execute_parameters(), ['limit' => $limit, 'offset' => $offset]);
        $limit = $params['limit'];
        $limit = (!empty($limit) && is_numeric($limit)) ? $limit : $moodlelimit;
        $offset = $params['offset'];
        $offset = (!empty($offset) && is_numeric($offset)) ? $offset : 0;

        // Perform security checks.
        $context = context_system::instance();
        self::validate_context($context);

        $enrolmentmethodsdata = [];

        // Get all visible courses.
        $courses = $DB->get_records('course', ['visible' => 1]);
        unset($courses[SITEID]); // Remove the site course.
        if ($courses) {
            foreach ($courses as $courseid => $course) {
                // Check if user is not already enrolled in the course.
                if (!is_enrolled(context_course::instance($course->id), $USER)) {
                    $enrolmentmethods = core_enrol_external::get_course_enrolment_methods($course->id);
                    foreach ($enrolmentmethods as $enrolmentmethod) {
                        if ($enrolmentmethod['type'] == 'self' && $enrolmentmethod['status'] == 'true') {
                            $enrolmentmethodsdata[] = self_enrolment_method::extract_self_enrolment_method_data(
                                $enrolmentmethod);
                        }
                    }
                }
            }
        }

        $hasmore = ($offset + $limit) < count($enrolmentmethodsdata);
        $enrolmentmethodsdata = array_slice($enrolmentmethodsdata, $offset, $limit);
        foreach ($enrolmentmethodsdata as $key => $enrolmentmethod) {
            $enrolmentmethodsdata[$key]['has_more'] = $hasmore;
        }

        return $enrolmentmethodsdata;
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
                    self_enrolment_method::get_return_structure(),
                    [
                        'has_more' => new external_value(PARAM_BOOL, 'Whether there are more self enrolment instances to fetch.'),
                    ]
                )
            )
        );
    }
}
