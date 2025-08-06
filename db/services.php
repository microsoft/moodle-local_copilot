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
 * Web service definition.
 *
 * @package local_copilot
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2024 onwards Microsoft, Inc. (http://microsoft.com/)
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_copilot_get_courses' => [
        'classname' => 'local_copilot\external\get_courses',
        'description' => 'Returns all courses that a user is enrolled in.',
        'type' => 'read',
        'ajax' => true,
        'services' => ['local_copilot'],
    ],
    'local_copilot_get_course_students_for_teacher' => [
        'classname' => 'local_copilot\external\get_course_students_for_teacher',
        'description' => 'Returns all students enrolled in a course for a teacher.',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'moodle/course:update, mod/course:viewparticipants',
        'services' => ['local_copilot'],
    ],
    'local_copilot_get_course_content_for_teacher' => [
        'classname' => 'local_copilot\external\get_course_content_for_teacher',
        'description' => 'Returns course details, all sections, and activities in each section for a teacher.',
        'type' => 'read',
        'ajax' => true,
        'services' => ['local_copilot'],
    ],
    'local_copilot_get_course_content_for_student' => [
        'classname' => 'local_copilot\external\get_course_content_for_student',
        'description' => 'Returns course details, all sections, and activities in each section for a student.',
        'type' => 'read',
        'ajax' => true,
        'services' => ['local_copilot'],
    ],
    'local_copilot_get_activities_by_type_for_teacher' => [
        'classname' => 'local_copilot\external\get_activities_by_type_for_teacher',
        'description' => 'Returns all activities of the given type for a teacher.',
        'type' => 'read',
        'ajax' => true,
        'services' => ['local_copilot'],
    ],
    'local_copilot_get_activities_by_type_for_student' => [
        'classname' => 'local_copilot\external\get_activities_by_type_for_student',
        'description' => 'Returns all activities of the given type for a student.',
        'type' => 'read',
        'ajax' => true,
        'services' => ['local_copilot'],
    ],
    'local_copilot_get_assignment_details_for_teacher' => [
        'classname' => 'local_copilot\external\get_assignment_details_for_teacher',
        'description' => 'Returns assignment activity details, list of submissions along with grade details from all students ' .
            'for a teacher.',
        'type' => 'read',
        'ajax' => true,
        'services' => ['local_copilot'],
    ],
    'local_copilot_get_assignment_details_for_student' => [
        'classname' => 'local_copilot\external\get_assignment_details_for_student',
        'description' => 'Returns assignment activity metadata, submission and grade details of an assignment for a student.',
        'type' => 'read',
        'ajax' => true,
        'services' => ['local_copilot'],
    ],
    'local_copilot_set_course_image_for_teacher' => [
        'classname' => 'local_copilot\external\set_course_image_for_teacher',
        'description' => 'Updates course image from URL.',
        'type' => 'write',
        'ajax' => true,
        // Require course update capability.
        'capabilities' => 'moodle/course:update',
        'services' => ['local_copilot'],
    ],

    'local_copilot_get_self_enrolment_instances_for_student' => [
        'classname' => 'local_copilot\external\get_self_enrolment_instances_for_student',
        'description' => 'Returns self enrolment instances for a student.',
        'type' => 'read',
        'ajax' => true,
        'services' => ['local_copilot'],
    ],

    'local_copilot_create_assignment_for_teacher' => [
        'classname' => 'local_copilot\external\create_assignment_for_teacher',
        'description' => 'Creates an assignment activity in a given course.',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'moodle/course:update, mod/assign:addinstance',
        'services' => ['local_copilot'],
    ],

    'local_copilot_create_announcement_for_teacher' => [
        'classname' => 'local_copilot\external\create_announcement_for_teacher',
        'description' => 'Creates an announcement post in a given course.',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'moodle/course:update, mod/forum:addinstance',
        'services' => ['local_copilot'],
    ],

    'local_copilot_create_forum_for_teacher' => [
        'classname' => 'local_copilot\external\create_forum_for_teacher',
        'description' => 'Creates a forum activity in a given course.',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'moodle/course:update, mod/forum:addinstance',
        'services' => ['local_copilot'],
    ],
];

// Pre-built service.
$services = [
    'Microsoft 365 Copilot Web Services' => [
        'functions' => [
            'local_copilot_get_courses',
            'local_copilot_get_course_students_for_teacher',
            'local_copilot_get_course_content_for_teacher',
            'local_copilot_get_course_content_for_student',
            'local_copilot_get_activities_by_type_for_teacher',
            'local_copilot_get_activities_by_type_for_student',
            'local_copilot_get_assignment_details_for_teacher',
            'local_copilot_get_assignment_details_for_student',
            'local_copilot_set_course_image_for_teacher',
            'local_copilot_get_self_enrolment_instances_for_student',
            'enrol_self_enrol_user',
            'local_copilot_create_assignment_for_teacher',
            'local_copilot_create_announcement_for_teacher',
            'local_copilot_create_forum_for_teacher',
        ],
        'restrictedusers' => 0,
        'enabled' => 1,
        'shortname' => 'copilot_webservices',
    ],
];
