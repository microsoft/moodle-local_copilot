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
 * Resource type for course attributes for student activity for web service return data in local_copilot.
 *
 * @package local_copilot
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license https://opensource.org/license/MIT MIT License
 * @copyright (C) 2014 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot\resource;

defined('MOODLE_INTERNAL') || die();

use external_value;
use moodle_url;
use stdClass;

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->dirroot . '/course/format/lib.php');
require_once($CFG->dirroot . '/grade/querylib.php');

/**
 * Section resource type.
 */
class base_activity_course implements resource_type {
    /**
     * Get the resource return structure.
     *
     * @return array
     */
    public static function get_return_structure(): array {
        return [
            'course_name' => new external_value(PARAM_TEXT, 'Name of the course that the activity belongs to.'),
            'course_id' => new external_value(PARAM_INT, 'ID of the course that the activity belongs to.'),
            'course_link' => new external_value(PARAM_URL, 'Link to the course that the activity belongs to.'),
            'section_name' => new external_value(PARAM_TEXT, 'Name of the section that the activity belongs to.'),
        ];
    }

    /**
     * Extract activity course data.
     *
     * @param stdClass $coursemodule
     * @return array
     */
    public static function extract_activity_course_data(stdClass $coursemodule): array {
        global $DB;

        $courselink = new moodle_url('/course/view.php', ['id' => $coursemodule->course]);
        $courseformat = course_get_format($coursemodule->course);
        $section = $DB->get_record('course_sections', ['id' => $coursemodule->section], 'name, section');

        return [
            'course_name' => $coursemodule->coursename,
            'course_id' => $coursemodule->course,
            'course_link' => $courselink->out(false),
            'section_name' => $courseformat->get_section_name($section->section),
        ];
    }
}
