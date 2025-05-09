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
 * Get assignment details API for students.
 *
 * @package local_copilot
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2024 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot\api_functions;

use local_copilot\manifest_generator;

/**
 * Get assignment details API for students.
 */
class local_copilot_get_assignment_details_for_student extends api_function_base {
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->path = '/local_copilot_get_assignment_details_for_student';
        $this->method = 'get';
        $this->summary = 'Return details of an assignment activity with the assignment ID for student.';
        $this->description = 'This API returns details of an assignment activity with the assignment ID for student. ' .
            'The assignment details include the assignment name, assignment ID, link, ' .
            'description, due date, instructions, and availability. ' .
            'It also includes the course name, ID and link that the assignment belongs to, ' .
            'as well as the name of the section that the assignment is in.';
        $this->operationid = 'getAssignmentDetailsForStudent';
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
                                'completed' => [
                                    'type' => 'boolean',
                                    'description' => 'If completion tracking is enabled in the assignment activity, ' .
                                        'whether the student has completed the activity.',
                                ],
                                'completion_datetime' => [
                                    'type' => 'integer',
                                    'description' => 'If completion tracking is enabled in the assignment activity, ' .
                                        'and the student has completed the activity, the completion date time in unix timestamp.',
                                ],
                                'submitted' => [
                                    'type' => 'boolean',
                                    'description' => 'Whether the student has submitted the assignment.',
                                ],
                                'submission_datetime' => [
                                    'type' => 'integer',
                                    'description' => 'If the student has submitted the assignment, ' .
                                        'the submission date time in unix timestamp.',
                                ],
                                'activity_grade' => [
                                    'type' => 'string',
                                    'description' => 'If the student has received a grade for the assignment activity, ' .
                                        'the grade; otherwise \'-1\'.',
                                ],
                                'instructions' => [
                                    'type' => 'string',
                                    'description' => 'Assignment activity instructions.',
                                ],
                                'availability' => [
                                    'type' => 'string',
                                    'description' => 'Availability of the assignment activity.',
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
                        'spacing' => 'none',
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
                                ],
                                'spacing' => 'none',
                            ],
                            [
                                'type' => 'FactSet',
                                'facts' => [
                                    [
                                        'title' => 'Submission date',
                                        'value' =>
                                            '{{DATE(${formatEpoch(submission_datetime, \'yyyy-MM-ddTHH:mm:ssZ\')}, SHORT)}} ' .
                                            '{{TIME(${formatEpoch(submission_datetime, \'yyyy-MM-ddTHH:mm:ssZ\')})}}',
                                    ],
                                ],
                                'isVisible' => '${if(submission_datetime != 0, true, false)}',
                                'spacing' => 'none',
                            ],
                            [
                                'type' => 'FactSet',
                                'facts' => [
                                    [
                                        'title' => 'Completed',
                                        'value' => '${if(completion_enabled, if(completed, \'Yes\', \'No\'), ' .
                                            '\'Completion is not enabled\')}',
                                    ],
                                    [
                                        'title' => 'Grade',
                                        'value' => '${if(activity_grade != \'-1\', activity_grade, \'Not graded\')}',
                                    ],
                                ],
                                'spacing' => 'none',
                            ],
                            [
                                'type' => 'TextBlock',
                                'text' => '**Not submitted**',
                                'wrap' => true,
                                'color' => 'attention',
                                'isVisible' => '${if(submitted, false, true)}',
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
            'body' => '**Do you want to get assignment details?**',
        ];
        $this->instructions = 'You can use the getAssignmentDetailsForStudent action to get details of an assignment for ' .
            'student. The "assignment_id" parameter takes the assignment activity ID from the "activity_id" attribute of ' .
            'the activity object.' . PHP_EOL .
            'If a question is about assignment activity type, use the getAssignmentDetailsForStudent action, ' .
            'rather than the getActivitiesByTypeForStudent action.' . PHP_EOL .
            'If a question asks you to list all overdue assignments, follow these steps:' . PHP_EOL .
            '1. Use the getActivitiesByTypeForStudent action to find the full list of assignment activities the user has.' .
            PHP_EOL .
            '2. Go through the list, and for each assignment activity on the list, use the getAssignmentDetailsForStudent ' .
            'action to find details of the assignment activity.' . PHP_EOL .
            '3. An assignment is overdue only if the due date has passed, and the user hasn\'t made a submission. If the ' .
            '"submitted" attribute returned is 1, it means the user has made submission, therefore the assignment is not overdue.';
        $this->sortorder = 8;
    }

    /**
     * Check if the API function is applicable to the given role type.
     *
     * @param string $roletype
     * @return bool
     */
    public static function check_applicable_role_type(string $roletype): bool {
        if (in_array($roletype, [manifest_generator::ROLE_TYPE_STUDENT])) {
            return true;
        }

        return false;
    }
}
