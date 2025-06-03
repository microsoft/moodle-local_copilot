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
 * Get course students API for teachers.
 *
 * @package local_copilot
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license https://opensource.org/license/MIT MIT License
 * @copyright (C) 2024 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot\api_functions;

use local_copilot\manifest_generator;

/**
 * Get course students API for teachers.
 */
class local_copilot_get_course_students_for_teacher extends api_function_base {
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->path = '/local_copilot_get_course_students_for_teacher';
        $this->method = 'get';
        $this->summary = 'Return the list of students enrolled in a course with the course ID for teacher.';
        $this->description = 'This API function looks for the course with the provided course ID, and if it is found, ' .
            'it returns the list of students enrolled in the course, including their full name, user ID, username, ' .
            'link to the user profile page, and roles in the course separated by commas.';
        $this->operationid = 'getCourseStudentsForTeacher';
        $this->scopesuffix = 'read';
        $this->parameters = [
            [
                'name' => 'course_id',
                'in' => 'query',
                'description' => 'ID of the course.',
                'required' => true,
                'schema' => [
                    'type' => 'integer',
                ],
            ],
        ];
        $this->responses = [
            '200' => [
                'description' => 'A list of students enrolled in the course.',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'array',
                            'description' => 'List of students enrolled in the course.',
                            'items' => [
                                'type' => 'object',
                                'description' => 'A student enrolled in the course.',
                                'properties' => [
                                    'student_full_name' => [
                                        'type' => 'string',
                                        'description' => 'Full name of the student.',
                                    ],
                                    'student_id' => [
                                        'type' => 'integer',
                                        'description' => 'Moodle user ID of the student.',
                                    ],
                                    'student_username' => [
                                        'type' => 'string',
                                        'description' => 'Moodle username of the student. ' .
                                            'This could map to the UPN of the Microsoft account.',
                                    ],
                                    'student_link' => [
                                        'type' => 'string',
                                        'format' => 'uri',
                                        'description' => 'Link to the student profile page.',
                                    ],
                                    'student_roles' => [
                                        'type' => 'string',
                                        'description' => 'The name of the roles that the student has in course, ' .
                                            'separated by commas.',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            '400' => [
                'description' => 'Bad request.',
            ],
            '401' => [
                'description' => 'Unauthorized.',
            ],
            '404' => [
                'description' => 'Course or user not found in Moodle.',
            ],
            '500' => [
                'description' => 'Internal server error.',
            ],
        ];
        $this->responsesemantics = [
            'data_path' => '$',
            'properties' => [
                'title' => '$.student_full_name',
                'url' => '$.student_link',
            ],
            'static_template' => [
                'type' => 'AdaptiveCard',
                '$schema' => 'http://adaptivecards.io/schemas/adaptive-card.json',
                'version' => '1.5',
                'body' => [
                    [
                        'type' => 'Container',
                        '$data' => '${$root}',
                        'items' => [
                            [
                                'type' => 'TextBlock',
                                'text' => 'Student name: **${student_full_name}**',
                                'wrap' => true,
                            ],
                            [
                                'type' => 'TextBlock',
                                'text' => 'Student username: ${student_username}',
                                'wrap' => true,
                            ],
                            [
                                'type' => 'TextBlock',
                                'text' => '[Moodle profile page](${student_link})',
                                'wrap' => true,
                            ],
                        ],
                    ],
                ],
                'actions' => [
                    [
                        'type' => 'Action.OpenUrl',
                        'title' => 'Open student profile page in Moodle',
                        'url' => '${student_link}',
                        'isVisible' => '${if(student_link, true, false)}',
                    ],
                ],
            ],
        ];
        $this->instructions = 'You can use the getCourseStudentsForTeacher action to find all the students in a Moodle ' .
            'course with the given ID. This action returns the full name, username, ID, link to the Moodle profile, ' .
            'and the list of roles for each student.';
        $this->sortorder = 2;
        $this->supportpagination = true;
        $this->enabled = false;
    }

    /**
     * Check if the API function is applicable to the given role type.
     *
     * @param string $roletype
     * @return bool
     */
    public static function check_applicable_role_type(string $roletype): bool {
        if (in_array($roletype, [manifest_generator::ROLE_TYPE_TEACHER])) {
            return true;
        }

        return false;
    }
}
