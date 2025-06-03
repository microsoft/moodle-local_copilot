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
 * Get activities by type API for teachers.
 *
 * @package local_copilot
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license https://opensource.org/license/MIT MIT License
 * @copyright (C) 2024 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot\api_functions;

use local_copilot\manifest_generator;

/**
 * Get activities by type API for students.
 */
class local_copilot_get_activities_by_type_for_teacher extends api_function_base {
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->path = '/local_copilot_get_activities_by_type_for_teacher';
        $this->method = 'get';
        $this->summary = 'Return a list of activities in the given type in all courses for teacher.';
        $this->description = 'This API function looks for the activity type with the name provided, and returns a list of ' .
            'activities in the given type in all courses for the teacher. The activity details returned include ' .
            'the activity name, activity ID, link, type, description, instructions, availability, ' .
            'and the number of students graded and completed. It also includes the course name, ' .
            'ID and link that the activity belongs to, as well as the name of the section that the activity is in.';
        $this->operationid = 'getActivitiesByTypeForTeacher';
        $this->scopesuffix = 'read';
        $this->parameters = [
            [
                'name' => 'activity_type',
                'in' => 'query',
                'description' => 'The code name or display name of the activity type, ' .
                    'e.g. forum, assignment, quiz, etc. Use singular form.',
                'required' => true,
                'schema' => [
                    'type' => 'string',
                ],
            ],
            [
                'name' => 'course_id',
                'in' => 'query',
                'description' => 'Moodle course id.',
                'required' => false,
                'schema' => [
                    'type' => 'integer',
                    'default' => 0,
                ],
            ],
            [
                'name' => 'limit',
                'in' => 'query',
                'description' => 'The maximum number of activities to return.',
                'required' => false,
                'schema' => [
                    'type' => 'integer',
                    'default' => 10,
                ],
            ],
            [
                'name' => 'offset',
                'in' => 'query',
                'description' => 'The number of activities to skip before starting to collect the result set.',
                'required' => false,
                'schema' => [
                    'type' => 'integer',
                    'default' => 0,
                ],
            ],
        ];
        $this->responses = [
            '200' => [
                'description' => 'List of activities with details.',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'array',
                            'description' => 'List of activities in the given type in all courses for the student.',
                            'items' => [
                                'type' => 'object',
                                'description' => 'Details of an activity.',
                                'properties' => [
                                    'activity_name' => [
                                        'type' => 'string',
                                        'description' => 'Name of the activity.',
                                    ],
                                    'activity_id' => [
                                        'type' => 'integer',
                                        'description' => 'ID of the activity.',
                                    ],
                                    'activity_link' => [
                                        'type' => 'string',
                                        'format' => 'uri',
                                        'description' => 'Link to the activity.',
                                    ],
                                    'activity_type' => [
                                        'type' => 'string',
                                        'description' => 'Activity type, also referred as module name. ' .
                                            'For example, forum, assignment, quiz, etc.',
                                    ],
                                    'activity_description' => [
                                        'type' => 'string',
                                        'description' => 'Description of the activity in HTML format.',
                                    ],
                                    'completion_enabled' => [
                                        'type' => 'boolean',
                                        'description' => 'Whether completion tracking is enabled for the activity.',
                                    ],
                                    'instructions' => [
                                        'type' => 'string',
                                        'description' => 'Activity instructions.',
                                    ],
                                    'availability' => [
                                        'type' => 'string',
                                        'description' => 'Activity availability information.',
                                    ],
                                    'graded_users_count' => [
                                        'type' => 'integer',
                                        'description' => 'Number of users who have received a grade for the activity.',
                                    ],
                                    'average_grade' => [
                                        'type' => 'number',
                                        'description' => 'Average grade for the activity, or \'n/a\' if no grades have been given.',
                                    ],
                                    'completed_users_count' => [
                                        'type' => 'integer',
                                        'description' => 'The number of users who have completed the activity.',
                                    ],
                                    'course_name' => [
                                        'type' => 'string',
                                        'description' => 'The full name of the course that the activity is in.',
                                    ],
                                    'course_id' => [
                                        'type' => 'integer',
                                        'description' => 'The ID of the course that the activity is in.',
                                    ],
                                    'course_link' => [
                                        'type' => 'string',
                                        'format' => 'uri',
                                        'description' => 'The link to the course that the activity is in.',
                                    ],
                                    'section_name' => [
                                        'type' => 'string',
                                        'description' => 'The name of the section that the activity is in.',
                                    ],
                                    'has_more' => [
                                        'type' => 'boolean',
                                        'description' => 'Flag indicating if there are more activities to be fetched.',
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
                'description' => 'User or activity type not found in Moodle.',
            ],
            '500' => [
                'description' => 'Internal server error.',
            ],
        ];
        $this->responsesemantics = [
            'data_path' => '$',
            'properties' => [
                'title' => '$.activity_name',
                'subtitle' => '$.activity_type',
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
                        'items' => [
                            [
                                'type' => 'FactSet',
                                'facts' => [
                                    [
                                        'title' => 'Activity type',
                                        'value' => '${activity_type}',
                                    ],
                                    [
                                        'title' => 'Course',
                                        'value' => '[${course_name}](${course_link})',
                                    ],
                                    [
                                        'title' => 'Section',
                                        'value' => '${section_name}',
                                    ],
                                    [
                                        'title' => 'Graded students',
                                        'value' => '${graded_users_count} ',
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
                        'title' => 'Open activity',
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
            'title' => 'Get activities',
            'body' => '**Do you want to get the list of your activities in Moodle?**',
        ];
        $this->instructions = 'You can use the getActivitiesByTypeForTeacher action to list all activities in a single ' .
            'type in all courses. The response contains activity details, student grading and completion statistics, ' .
            'as well course and section that the activity is in. This API returns average grade of the activity; ' .
            'if there are no users who have received grades, it will return "n/a". Don\'t display "n/a" to users.';
        $this->supportpagination = true;
        $this->sortorder = 5;
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
