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
 * Web service function to return course details, sections, and activities for teachers.
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
use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use core_external\external_value;
use local_copilot\resource\base_activity;
use local_copilot\resource\base_course;
use local_copilot\resource\course_section;
use local_copilot\resource\teacher_activity;
use local_copilot\resource\teacher_course;

require_once($CFG->libdir . '/externallib.php');

/**
 * Web service class definition.
 */
class get_course_content_for_teacher extends external_api {
    /**
     * Method parameter definitions.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'course_id' => new external_value(PARAM_INT, 'Moodle course id', VALUE_REQUIRED),
        ]);
    }

    /**
     * Method return definitions.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure(
            array_merge(
                base_course::get_return_structure(),
                teacher_course::get_return_structure(),
                [
                    'sections' => new external_multiple_structure(
                        new external_single_structure(array_merge(
                            course_section::get_return_structure(),
                            [
                                'activities' => new external_multiple_structure(
                                    new external_single_structure(
                                        array_merge(
                                            base_activity::get_return_structure(),
                                            teacher_activity::get_return_structure()
                                        )
                                    )
                                ),
                            ]
                        ))
                    ),
                ],
            )
        );
    }

    /**
     * Return course details, the list of course sections, and activities in each section.
     *
     * @param int $courseid
     * @return array|null
     */
    public static function execute(int $courseid): ?array {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), ['course_id' => $courseid]);
        $courseid = $params['course_id'];

        $course = $DB->get_record('course', ['id' => $courseid]);
        if (!$course) {
            header('HTTP/1.0 404 course not found');
            die;
        }

        $coursecontext = context_course::instance($courseid);
        // Check if user has course update capability.
        if (!has_capability('moodle/course:update', $coursecontext)) {
            header('HTTP/1.0 403 user does not have teacher role');
            die;
        }
        $returnvalue = base_course::extract_course_data($course, $USER->id);
        $returnvalue += teacher_course::extract_course_data($course);
        $returnvalue['sections'] = [];

        // Get ordered course sections.
        $sections = $DB->get_records('course_sections', ['course' => $course->id], 'section');
        if ($sections) {
            foreach ($sections as $section) {
                $sectiondata = course_section::extract_section_data($section);
                $sectiondata['activities'] = [];
                // Get the activities in the section.
                $coursemodules = $DB->get_records('course_modules', ['course' => $course->id, 'section' => $section->id]);

                if ($coursemodules) {
                    // Get the sequence of activities in the section.
                    $sequence = explode(',', $section->sequence);

                    // Create an associative array of activities for easy access.
                    $activitiesmap = [];
                    foreach ($coursemodules as $coursemodule) {
                        $coursemodule->modname = $DB->get_field('modules', 'name', ['id' => $coursemodule->module]);
                        $activitiesmap[$coursemodule->id] = $coursemodule;
                    }

                    // Order the activities based on the sequence.
                    foreach ($sequence as $cmid) {
                        if (isset($activitiesmap[$cmid])) {
                            $coursemodule = $activitiesmap[$cmid];
                            $activitydata = array_merge(
                                base_activity::extract_activity_data($coursemodule, $USER->id),
                                teacher_activity::extract_teacher_activity_data($coursemodule)
                            );
                            $sectiondata['activities'][] = $activitydata;
                        }
                    }
                }
                $returnvalue['sections'][] = $sectiondata;
            }
        }

        return $returnvalue;
    }
}
