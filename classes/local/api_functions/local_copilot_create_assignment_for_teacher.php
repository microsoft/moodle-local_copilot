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
 * Create assign activity.
 *
 * @package local_copilot
 * @author Lai Wei <lai.wei@enovation.ie>
 * @author Dorel Manolescu <dorel.manolescu@enovation.ie>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2024 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot\local\api_functions;

use local_copilot\manifest_generator;

/**
 * Create assignment API for teachers.
 */
class local_copilot_create_assignment_for_teacher extends api_function_base {
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->path = '/local_copilot_create_assignment_for_teacher';
        $this->method = 'post';
        $this->summary = 'Create an assignment in a course.';
        $this->description = 'This API function creates an assignment in the course with the provided course ID, ' .
            'section ID using the assignment details provided in the request.';
        $this->operationid = 'createAssignmentForTeacher';
        $this->scopesuffix = 'write';
        $this->parameters = [];
        $this->requestbody = [
            'content' => [
                'application/json' => [
                    'schema' => [
                        'type' => 'object',
                        'required' => ['course_id', 'assignment_name', 'assignment_description', 'section_id',
                            'allowsubmissionsfromdate', 'due_date', 'assignment_instructions'],
                        'properties' => [
                            'course_id' => [
                                'type' => 'integer',
                                'description' => 'ID of the course to create the assignment.',
                            ],
                            'assignment_name' => [
                                'type' => 'string',
                                'description' => 'Name of the assignment.',
                            ],
                            'assignment_description' => [
                                'type' => 'string',
                                'description' => 'Description of the assignment in HTML format.',
                            ],
                            'section_id' => [
                                'type' => 'integer',
                                'description' => 'ID of the section to add the assignment.',
                            ],
                            'allowsubmissionsfromdate' => [
                                'type' => 'string',
                                'description' => 'Assignment submission start date in American date format (MM/DD/YYYY).',
                            ],
                            'due_date' => [
                                'type' => 'string',
                                'description' => 'Assignment submission due date in American date format (MM/DD/YYYY).',
                            ],
                            'assignment_instructions' => [
                                'type' => 'string',
                                'description' => 'Assignment instructions.',
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->responses = [
            '200' => [
                'description' => 'Whether the assignment was created successfully.',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'description' => 'Whether the assignment was created successfully.',
                            'properties' => [
                                'success' => [
                                    'type' => 'boolean',
                                    'description' => 'Whether the assignment was created successfully.',
                                ],
                                'id' => [
                                    'type' => 'integer',
                                    'description' => 'Activity ID of the created assignment, if successful; otherwise 0.',
                                ],
                                'error' => [
                                    'type' => 'string',
                                    'description' => 'Error message if the assignment creation failed.',
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
            '403' => [
                'description' => 'The user does not have capability to create assignment.',
            ],
            '404' => [
                'description' => 'Course not found in Moodle.',
            ],
            '500' => [
                'description' => 'Internal server error.',
            ],
        ];
        $this->confirmation = [
            'type' => 'AdaptiveCard',
            'title' => 'Create assignment',
            'body' => '**Do you want to create new assignments in Moodle?**',
        ];
        $this->instructions = 'You can use the createAssignmentForTeacher action to create an assignment in a specific course. ' .
            'Before using the createAssignmentForTeacher action, always ensure that the assignment_name, course_id, ' .
            'and section_id are provided, as they are mandatory. ' .
            'If any of these are missing, ask the user for the required information, clearly ' .
            'distinguishing between mandatory and optional fields. The mandatory fields are assignment_name, course_id, ' .
            'and section_id. ' .
            'The optional fields are assignment_description, allowsubmissionsfromdate, due_date, assignment_instructions. ' .
            'The course_id sets the course, assignment_name defines the name, section_id specifies the section, and ' .
            'assignment_description provides a brief overview. ' .
            'If allowsubmissionsfromdate is not provided, assume today’s date as the default. ' .
            'While it\'s NOT required, the user can choose to provide allowsubmissionsfromdate or due_date in natural language ' .
            '(e.g., "next Monday", "April 25, 2025", "MM/DD/YYYY", or "in 2 weeks"). ' .
            'You should convert this input to the American format ' .
            'MM/DD/YYYY before calling the web service. Do NOT ask the user to provide a Unix timestamp. ' .
            'Finally, assignment_instructions ' .
            'contains detailed guidelines for students.' . PHP_EOL .
            'If you used createAssignmentForTeacher action to create an assignment activity, and it is created successfully, ' .
            'call getAssignmentDetailsForTeacher action to get its details, and show them.';
        $this->sortorder = 12;
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
