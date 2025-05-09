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
 * Resource type for assignment activity for both student and teacher, for web service return data in local_copilot.
 *
 * @package local_copilot
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2014 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot\resource;

use core_external\external_value;
use moodle_url;
use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/format/lib.php');
require_once($CFG->libdir . '/accesslib.php');
require_once($CFG->libdir . '/enrollib.php');

/**
 * Class teacher_assignment_activity.
 * Teacher assignment activity resource type.
 */
class base_assignment_activity implements resource_type {
    /**
     * Get return structure for teacher assignment activity.
     *
     * @return array
     */
    public static function get_return_structure(): array {
        return [
            'activity_name' => new external_value(PARAM_TEXT, 'Name of the assignment activity.'),
            'activity_id' => new external_value(PARAM_INT, 'ID of the assignment activity.'),
            'activity_link' => new external_value(PARAM_URL, 'Link to the assignment activity.'),
            'activity_description' => new external_value(PARAM_RAW, 'Description of the assignment activity in HTML format.'),
            'due_date' => new external_value(PARAM_INT, 'Assignment submission due date in unix timestamp.'),
            'completion_enabled' => new external_value(PARAM_BOOL,
                'Whether completion tracking is enabled in the assignment activity.'),
            'instructions' => new external_value(PARAM_RAW, 'Assignment activity instructions.'),
            'course_name' => new external_value(PARAM_TEXT, 'The name of the course that the assignment activity is in.'),
            'course_id' => new external_value(PARAM_INT, 'The ID of the course that the assignment activity is in.'),
            'course_link' => new external_value(PARAM_URL, 'The link to the course that the assignment activity is in.'),
            'section_name' => new external_value(PARAM_TEXT, 'The name of the section that the assignment activity is in.'),
            'use_card' => new external_value(PARAM_BOOL, 'Whether to use card view for the assignment activity.'),
        ];
    }

    /**
     * Extract assignment activity data for teacher.
     *
     * @param stdClass $assignmentactivity
     * @param stdClass $cm
     * @param stdClass $coursedata
     * @return array
     */
    public static function extract_assignment_activity_data(stdClass $assignmentactivity, stdClass $cm,
        stdClass $coursedata): array {
        global $DB;

        $activitylink = new moodle_url('/mod/assign/view.php', ['id' => $cm->id]);
        $courselink = new moodle_url('/course/view.php', ['id' => $coursedata->id]);
        $section = $DB->get_record('course_sections', ['id' => $cm->section], 'section');
        $courseformat = course_get_format($coursedata->id);

        $assignmentactivitydata = [];
        $assignmentactivitydata['activity_name'] = $assignmentactivity->name;
        $assignmentactivitydata['activity_id'] = $assignmentactivity->id;
        $assignmentactivitydata['activity_link'] = $activitylink->out(false);
        $assignmentactivitydata['activity_description'] = format_text($assignmentactivity->intro, $assignmentactivity->introformat);
        $assignmentactivitydata['due_date'] = $assignmentactivity->duedate;
        $assignmentactivitydata['completion_enabled'] = (bool) $cm->completion;
        $assignmentactivitydata['instructions'] = $assignmentactivity->activity;
        $assignmentactivitydata['course_name'] = $coursedata->fullname;
        $assignmentactivitydata['course_id'] = $coursedata->id;
        $assignmentactivitydata['course_link'] = $courselink->out(false);
        $assignmentactivitydata['section_name'] = $courseformat->get_section_name($section->section);
        $assignmentactivitydata['use_card'] = true;

        return $assignmentactivitydata;
    }
}
