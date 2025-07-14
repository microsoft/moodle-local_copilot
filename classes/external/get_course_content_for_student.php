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
 * Web service function to return course details, sections, and activities for students.
 *
 * @package local_copilot
 * @author Dorel Manolescu <dorel.manolescu@enovation.ie>
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license https://opensource.org/license/MIT MIT License
 * @copyright (C) 2014 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot\external;

defined('MOODLE_INTERNAL') || die();

use context_course;
use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use core_external\external_value;
use local_copilot\local\resource\base_activity;
use local_copilot\local\resource\base_course;
use local_copilot\local\resource\course_section;
use local_copilot\local\resource\student_activity;
use local_copilot\local\resource\student_course;

require_once($CFG->libdir . '/externallib.php');

/**
 * Web service class definition.
 */
class get_course_content_for_student extends external_api {
    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'course_id' => new external_value(PARAM_INT, 'Moodle course ID', VALUE_REQUIRED),
        ]);
    }
    /**
     * Returns whether a course is found, and if found, the list of course sections and activities.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure(
            array_merge(
                base_course::get_return_structure(),
                student_course::get_return_structure(),
                [
                    'sections' => new external_multiple_structure(
                        new external_single_structure(array_merge(
                            course_section::get_return_structure(),
                            [
                                'activities' => new external_multiple_structure(
                                    new external_single_structure(
                                        array_merge(
                                            base_activity::get_return_structure(),
                                            student_activity::get_return_structure()
                                        )
                                    )
                                ),
                            ]
                        ))
                    ),
                ]
            ),
        );
    }

    /**
     * Returns course metadata, list of course sections, and activities in each section.
     *
     * @param int $courseid
     * @return array|null
     * @uses die
     */
    public static function execute(int $courseid): ?array {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), ['course_id' => $courseid]);
        $courseid = $params['course_id'];

        $course = $DB->get_record('course', ['id' => $courseid]);

        if (!$course) {
            header('HTTP/1.0 404 course not found');
            die();
        }

        // Perform security checks.
        $coursecontext = context_course::instance($courseid);
        self::validate_context($coursecontext);
        // Check if user is enrolled in the course.
        $roles = get_user_roles($coursecontext);
        if (!$roles) {
            header('HTTP/1.0 403 user does not have access to the course content');
            die();
        }

        $returnvalue = base_course::extract_course_data($course, $USER->id);
        $returnvalue += student_course::extract_course_data($course, $USER->id);
        $returnvalue['sections'] = [];

        // Get ordered course sections.
        $sections = $DB->get_records('course_sections', ['course' => $course->id], 'section');
        if ($sections) {
            foreach ($sections as $section) {
                $sectiondata = course_section::extract_section_data($section, $USER->id);
                $sectiondata['activities'] = [];
                // Get the activities in the section.
                $coursemodules = $DB->get_records('course_modules', ['course' => $course->id, 'section' => $section->id]);

                if ($coursemodules) {
                    // Get the sequence of activities in the section.
                    $sequence = explode(',', $section->sequence);

                    // Create an associative array of activities for easy access.
                    $activitiesmap = [];
                    foreach ($coursemodules as $activity) {
                        $activity->modname = $DB->get_field('modules', 'name', ['id' => $activity->module]);
                        $activitiesmap[$activity->id] = $activity;
                    }

                    // Order the activities based on the sequence.
                    foreach ($sequence as $cmid) {
                        if (isset($activitiesmap[$cmid])) {
                            $coursemodule = $activitiesmap[$cmid];
                            $activitydate = base_activity::extract_activity_data($coursemodule, $USER->id);
                            $studentactivitydata = student_activity::extract_student_activity_data($coursemodule, $USER->id);
                            $sectiondata['activities'][] = array_merge($activitydate, $studentactivitydata);
                        }
                    }
                }
                $returnvalue['sections'][] = $sectiondata;
            }
        }
        return $returnvalue;
    }
}
