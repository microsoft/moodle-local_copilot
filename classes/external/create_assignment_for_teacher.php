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
 * Web service function to create an assignment for teacher.
 *
 * @package local_copilot
 * @author Dorel Manolescu <dorel.manolescu@enovation.ie>
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2014 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot\external;

use cache_helper;
use context_course;
use Exception;
use external_api;
use external_function_parameters;
use external_single_structure;
use core_external\external_value;
use stdClass;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/course/lib.php');

/**
 * Web service class definition.
 */
class create_assignment_for_teacher extends external_api {
    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'course_id' => new external_value(PARAM_INT, 'Moodle course id', VALUE_REQUIRED),
            'assignment_name' => new external_value(PARAM_TEXT, 'Title/name of the activity', VALUE_REQUIRED),
            'section_id' => new external_value(PARAM_INT, 'Section/topic id', VALUE_REQUIRED),
            'assignment_description' => new external_value(PARAM_RAW, 'Description of the activity', VALUE_DEFAULT, ''),
            'allowsubmissionsfromdate' => new external_value(
                PARAM_TEXT,
                'Allow submissions from date in MM/DD/YYYY format',
                VALUE_DEFAULT,
                ''
            ),
            'due_date' => new external_value(PARAM_TEXT, 'Due date in MM/DD/YYYY format', VALUE_DEFAULT, ''),
            'assignment_instructions' => new external_value(PARAM_RAW, 'Cut off date', VALUE_DEFAULT, ''),
        ]);
    }

    /**
     * Method return definitions.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'True if the operation was successful'),
            'id' => new external_value(PARAM_INT, 'ID of the activity created'),
            'error' => new external_value(PARAM_TEXT, 'Error message if the activity creation failed'),
        ]);
    }

    /**
     * Create course activity.
     *
     * @param int $assignmentcourseid
     * @param string $assignmentname
     * @param int $sectionid
     * @param string|null $assignmentdescription
     * @param string|null $allowsubmissionsfromdate
     * @param string|null $assignmentduedate
     * @param string|null $assignmentinstructions
     * @return array|null
     * @uses die
     */
    public static function execute(
        int $assignmentcourseid,
        string $assignmentname,
        int $sectionid,
        ?string $assignmentdescription = null,
        ?string $allowsubmissionsfromdate = null,
        ?string $assignmentduedate = null,
        ?string $assignmentinstructions = null
    ): ?array {
        global $DB;

        // Convert date strings to Unix timestamps.
        $allowsubmissionsfromdate = $allowsubmissionsfromdate ? strtotime($allowsubmissionsfromdate) : 0;
        $assignmentduedate = $assignmentduedate ? strtotime($assignmentduedate) : 0;

        if ($allowsubmissionsfromdate === false || $assignmentduedate === false) {
            return ['success' => false, 'id' => 0, 'error' => 'Invalid date format. Use MM/DD/YYYY.'];
        }

        // Validate parameters.
        $params = self::validate_parameters(self::execute_parameters(), [
            'course_id' => $assignmentcourseid, 'assignment_name' => $assignmentname, 'section_id' => $sectionid,
            'assignment_description' => $assignmentdescription, 'allowsubmissionsfromdate' => $allowsubmissionsfromdate,
            'due_date' => $assignmentduedate, 'assignment_instructions' => $assignmentinstructions]);

        $courseid = $params['course_id'];
        $title = $params['assignment_name'];
        $section = $params['section_id'];
        $description = $params['assignment_description'];
        $allowsubmissionsfromdate = $params['allowsubmissionsfromdate'];
        $duedate = $params['due_date'];
        $instructions = $params['assignment_instructions'];

        $course = $DB->get_record('course', ['id' => $courseid]);
        if (!$course) {
            header('HTTP/1.0 404 course not found');
            die();
        }

        // Perform security checks.
        $coursecontext = context_course::instance($courseid);
        self::validate_context($coursecontext);
        if (!has_capability('mod/assign:addinstance', $coursecontext)) {
            header('HTTP/1.0 403 user does not have assign add instance capability');
            die();
        }

        // From: mod/assign/tests/generator/lib.php.
        $moduledata = new stdClass();
        $moduledata->course = $courseid;
        $moduledata->name = trim($title);
        $moduledata->section = $section;
        $moduledata->visible = 1;
        $moduledata->submissiondrafts = 0; // Not sure about this.
        $moduledata->requiresubmissionstatement = 0;
        $moduledata->sendnotifications = 0;
        $moduledata->sendlatenotifications = 0;
        $moduledata->duedate = $duedate;
        $moduledata->cutoffdate = 0;
        $moduledata->allowsubmissionsfromdate = $allowsubmissionsfromdate;
        $moduledata->gradingduedate = 0;
        $moduledata->grade = 100;
        $moduledata->teamsubmission = 0;
        $moduledata->nosubmissions = 0; // Will allow file submissions.
        $moduledata->requireallteammemberssubmit = 0;
        $moduledata->blindmarking = 0;
        $moduledata->markingworkflow = 0;
        $moduledata->modulename = 'assign';
        $moduledata->introeditor = ['text' => '', 'format' => ''];
        $moduledata->assignsubmission_file_enabled = 1; // Will allow file submissions.
        $moduledata->assignsubmission_file_maxfiles = 1; // Will allow file submissions.
        $moduledata->maxsubmissionsizebytes = 0;
        $moduledata->alwaysshowdescription = 1;
        try {
            $modid = create_module($moduledata);
            if (!$modid) {
                header('HTTP/1.0 500 Internal Server Error');
                die('Failed to create activity');
            }
            if ($description || $instructions) {
                $assignment = $DB->get_record('assign', ['id' => $modid->instance], '*');
                $assignment->intro = $description;
                $assignment->introformat = 1;
                $assignment->activity = $instructions;
                $assignment->activityformat = 1;
                $assignment->nosubmissions = 0;
                $assignment->timemodified = time() + 1;
                $DB->update_record('assign', $assignment);
            }

            $returnvalue = ['success' => true, 'id' => $modid->instance, 'error' => ''];
        } catch (Exception $e) {
            $returnvalue = ['success' => false, 'id' => 0, 'error' => $e->getMessage()];
        }

        rebuild_course_cache($courseid);
        cache_helper::purge_by_event('changesincourse');

        return $returnvalue;
    }
}
