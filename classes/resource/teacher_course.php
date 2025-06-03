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
 * Resource type for additional course information for teachers, to be used by web service return data in local_copilot.
 *
 * @package local_copilot
 * @author Lai Wei <lai.wei@enovation.ie
 * @license https://opensource.org/license/MIT MIT License
 * @copyright (C) 2014 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot\resource;

defined('MOODLE_INTERNAL') || die();

use context_course;
use external_value;
use stdClass;

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->libdir . '/accesslib.php');

/**
 * Course resource type.
 */
class teacher_course implements resource_type {
    /**
     * Get the resource return structure.
     *
     * @return array
     */
    public static function get_return_structure(): array {
        return [
            'enrolled_users_count' => new external_value(PARAM_INT, 'Number of users enrolled in the course.'),
            'groups_count' => new external_value(PARAM_INT, 'Number of groups in the course.'),
        ];
    }

    /**
     * Extract course data.
     *
     * @param stdClass $course
     * @return array
     */
    public static function extract_course_data(stdClass $course) {
        global $DB;

        $context = context_course::instance($course->id);
        $users = get_enrolled_users($context, '', 0, 'u.id', null, 0, 0, true);
        $groups = $DB->get_records('groups', ['courseid' => $course->id]);

        return [
            'enrolled_users_count' => count($users),
            'groups_count' => count($groups),
        ];
    }
}
