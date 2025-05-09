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
 * Get assignment details API for teachers.
 *
 * @package local_copilot
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2024 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot\api_functions;

use local_copilot\manifest_generator;

/**
 * Get assignment details API for teachers.
 */
class local_copilot_get_assignment_details_for_teacher extends api_function_base {
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->path = '/local_copilot_get_assignment_details_for_teacher';
        $this->method = 'get';
        $this->summary = 'Return details of an assignment with the activity ID for teacher.';
        $this->description = 'This API returns details of an assignment with the activity ID for teacher. ' .
            'The assignment details include the assignment name, assignment activity ID, link, description, ' .
            'due date, instructions, and availability. ' .
            'It also includes the course name, course ID and link that the assignment belongs to, ' .
            'as well as the name of the section that the assignment is in. It also returns the list of submissions and ' .
            'grades of the assignment.';
        $this->operationid = 'getAssignmentDetailsForTeacher';
        $this->scopesuffix = 'read';
        $this->parameters = [
            [
                'name' => 'activity_id',
                'in' => 'query',
                'description' => 'ID of the assignment activity.',
                'required' => true,
                'schema' => [
                    'type' => 'integer',
                ],
            ],
        ];
        $this->responses = [
            '200' => [
                'description' => 'Details of the assignment activity.',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'description' => 'Details of the assignment activity.',
                            'properties' => [
                                'activity_name' => [
                                    'type' => 'string',
                                    'description' => 'Name of the assignment activity.',
                                ],
                                'activity_id' => [
                                    'type' => 'integer',
                                    'description' => 'ID of the assignment activity.',
                                ],
                                'activity_link' => [
                                    'type' => 'string',
                                    'format' => 'uri',
                                    'description' => 'Link to the assignment activity.',
                                ],
                                'activity_description' => [
                                    'type' => 'string',
                                    'description' => 'Description of the assignment activity in HTML format.',
                                ],
                                'due_date' => [
                                    'type' => 'integer',
                                    'description' => 'Assignment submission due date in unix timestamp.',
                                ],
                                'completion_enabled' => [
                                    'type' => 'boolean',
                                    'description' => 'Whether completion tracking is enabled in the assignment activity.',
                                ],
                                'instructions' => [
                                    'type' => 'string',
                                    'description' => 'Assignment activity instructions.',
                                ],
                                'course_name' => [
                                    'type' => 'string',
                                    'description' => 'The name of the course that the assignment activity is in.',
                                ],
                                'course_id' => [
                                    'type' => 'integer',
                                    'description' => 'The ID of the course that the assignment activity is in.',
                                ],
                                'course_link' => [
                                    'type' => 'string',
                                    'format' => 'uri',
                                    'description' => 'The link to the course that the assignment activity is in.',
                                ],
                                'section_name' => [
                                    'type' => 'string',
                                    'description' => 'The name of the section that the assignment activity is in.',
                                ],
                                'use_card' => [
                                    'type' => 'boolean',
                                    'description' => 'Whether to use card view for the assignment activity.',
                                ],
                                'submissions' => [
                                    'type' => 'array',
                                    'description' => 'List of submissions of the assignment activity.',
                                    'items' => [
                                        'type' => 'object',
                                        'description' => 'Details of a submission of the assignment activity.',
                                        'properties' => [
                                            /* Exclude student details in the current phase.
                                            'student_full_name' => [
                                                'type' => 'string',
                                                'description' => 'Full name of the student who submitted the assignment.',
                                            ],
                                            */
                                            'student_user_id' => [
                                                'type' => 'integer',
                                                'description' => 'ID of the student who submitted the assignment.',
                                            ],
                                            'submitted' => [
                                                'type' => 'boolean',
                                                'description' => 'Whether the assignment has been submitted by the student.',
                                            ],
                                            'submission_datetime' => [
                                                'type' => 'integer',
                                                'description' => 'If a submission has been made, ' .
                                                    'the submission date time in unix timestamp.',
                                            ],
                                            'activity_grade' => [
                                                'type' => 'string',
                                                'description' => 'The grade of the submission.',
                                            ],
                                            'completed' => [
                                                'type' => 'boolean',
                                                'description' => 'Whether the assignment activity has been completed by ' .
                                                    'the student.',
                                            ],
                                            'completion_datetime' => [
                                                'type' => 'integer',
                                                'description' => 'If the student has completed the assignment activity, ' .
                                                    'the completion date time in unix timestamp.',
                                            ],
                                        ],
                                    ],
                                ],
                                'submissions_count' => [
                                    'type' => 'integer',
                                    'description' => 'The number of submissions for the assignment activity.',
                                ],
                                'graded_submissions_count' => [
                                    'type' => 'integer',
                                    'description' => 'The number of graded submissions for the assignment activity.',
                                ],
                                'average_grade' => [
                                    'type' => 'string',
                                    'description' => 'Average grade for the assignment activity, ' .
                                        'or \'n/a\' if no grades have been given.',
                                ],
                                'completed_users_count' => [
                                    'type' => 'integer',
                                    'description' => 'The number of students who have completed the assignment activity.',
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
                'description' => 'User, assignment, or enrolment not found in Moodle.',
            ],
            '500' => [
                'description' => 'Internal server error.',
            ],
        ];
        $this->responsesemantics = [
            'data_path' => '$',
            'properties' => [
                'title' => '$.activity_name',
                'url' => '$.activity_link',
            ],
            'static_template' => [
                'type' => 'AdaptiveCard',
                '$schema' => 'http://adaptivecards.io/schemas/adaptive-card.json',
                'version' => '1.5',
                'isVisible' => '${use_card}',
                'body' => [
                    [
                        'type' => 'TextBlock',
                        'text' => '${activity_name}',
                        'size' => 'large',
                        'weight' => 'bolder',
                        'wrap' => true,
                    ],
                    [
                        'type' => 'Container',
                        '$data' => '${$root}',
                        'items' => [
                            [
                                'type' => 'FactSet',
                                'facts' => [
                                    [
                                        'title' => 'Course',
                                        'value' => '[${course_name}](${course_link})',
                                    ],
                                    [
                                        'title' => 'Section',
                                        'value' => '${section_name}',
                                    ],
                                    [
                                        'title' => 'Due date',
                                        'value' => '{{DATE(${formatEpoch(due_date, \'yyyy-MM-ddTHH:mm:ssZ\')}, SHORT)}} ' .
                                            '{{TIME(${formatEpoch(due_date, \'yyyy-MM-ddTHH:mm:ssZ\')})}}',
                                    ],
                                    [
                                        'title' => 'Submitted students',
                                        'value' => '${submissions_count} ',
                                    ],
                                    [
                                        'title' => 'Graded students',
                                        'value' => '${graded_submissions_count} ',
                                    ],
                                ],
                                'spacing' => 'none',
                            ],
                            [
                                'type' => 'FactSet',
                                'facts' => [
                                    [
                                        'title' => 'Completed students',
                                        'value' => '${completed_users_count} ',
                                    ],
                                ],
                                'spacing' => 'none',
                                'isVisible' => '${if(completion_enabled, true, false)}',
                            ],
                            [
                                'type' => 'FactSet',
                                'facts' => [
                                    [
                                        'title' => 'Average grade',
                                        'value' => '${average_grade} ',
                                    ],
                                ],
                                'spacing' => 'none',
                                'isVisible' => '${if(average_grade != \'n/a\', true, false)}',
                            ],
                        ],
                    ],
                ],
                'actions' => [
                    [
                        'type' => 'Action.OpenUrl',
                        'title' => 'Open assignment',
                        'url' => '${activity_link}',
                        'isVisible' => '${if(activity_link, true, false)}',
                    ],
                    [
                        'type' => 'Action.OpenUrl',
                        'title' => 'Open course',
                        'url' => '${course_link}',
                        'isVisible' => '${if(course_link, true, false)}',
                    ],
                ],
            ],
        ];
        $this->confirmation = [
            'type' => 'AdaptiveCard',
            'title' => 'Get assignment details',
            'body' => '**Do you want to get the details of assignment activities in Moodle?**',
        ];
        $this->instructions = 'You can use the getAssignmentDetailsForTeacher action to get details of an assignment activity ' .
            'for teacher. The "activity_id" parameter takes the assignment activity ID from the "activity_id" attribute ' .
            'of the activity object.' . PHP_EOL .
            'If a question is about assignment activity type, always use the getAssignmentDetailsForTeacher action, NOT the ' .
            'getActivitiesByTypeForTeacher action, because it returns more information specific to assignment activity type. ' .
            'When a user asks a question such as "Find assignments for", prompt them to provide the course they are referring to.' .
            ' Once the course is specified, use getCourseContentForTeacher to retrieve all assignment activity IDs, and then use ' .
            'getAssignmentDetailsForTeacher for each assignment activity to retrieve the full assignment details.';
        $this->sortorder = 7;
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
