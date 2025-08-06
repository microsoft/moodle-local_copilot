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
 * Get course content API for teachers.
 *
 * @package local_copilot
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2024 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot\local\api_functions;

use local_copilot\manifest_generator;

/**
 * Get course content API for teachers.
 */
class local_copilot_get_course_content_for_teacher extends api_function_base {
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->path = '/local_copilot_get_course_content_for_teacher';
        $this->method = 'get';
        $this->summary = 'Return content of a course with the course ID for teacher.';
        $this->description = 'This API tries to find a course with the provided ID, if one is found, it returns course details, ' .
            'its sections, and activities within each section. The activity details returned include activity name, ' .
            'activity ID, link, type, description, completion status, instructions, and availability.';
        $this->operationid = 'getCourseContentForTeacher';
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
                'description' => 'Content of the course, including sections, and activities within the sections.',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'description' => 'Course content.',
                            'properties' => [
                                'course_name' => [
                                    'type' => 'string',
                                    'description' => 'Full name of the course.',
                                ],
                                'course_id' => [
                                    'type' => 'integer',
                                    'description' => 'ID of the course.',
                                ],
                                'course_shortname' => [
                                    'type' => 'string',
                                    'description' => 'Short name of the course, which is unique on the Moodle site.',
                                ],
                                'course_link' => [
                                    'type' => 'string',
                                    'format' => 'uri',
                                    'description' => 'Link to the course.',
                                ],
                                'course_summary' => [
                                    'type' => 'string',
                                    'description' => 'Summary of the course in HTML format.',
                                ],
                                'category' => [
                                    'type' => 'string',
                                    'description' => 'Name of the direct parent category of the course.',
                                ],
                                'visibility' => [
                                    'type' => 'boolean',
                                    'description' => 'Whether the course is visible to students.',
                                ],
                                'start_datetime' => [
                                    'type' => 'integer',
                                    'description' => 'Start date time of the course in unix timestamp.',
                                ],
                                'end_datetime' => [
                                    'type' => 'integer',
                                    'description' => 'End date time of the course in unix timestamp. ' .
                                        'If the course does not have an end date, this is set to 0.',
                                ],
                                'roles' => [
                                    'type' => 'string',
                                    'description' => 'The name of the roles that the user has in course, separated by commas.',
                                ],
                                'enrolled_users_count' => [
                                    'type' => 'integer',
                                    'description' => 'Number of users enrolled in the course.',
                                ],
                                'groups_count' => [
                                    'type' => 'integer',
                                    'description' => 'Number of groups in the course.',
                                ],
                                'sections' => [
                                    'type' => 'array',
                                    'description' => 'List of sections in the course.',
                                    'items' => [
                                        'type' => 'object',
                                        'description' => 'A section in the course.',
                                        'properties' => [
                                            'section_name' => [
                                                'type' => 'string',
                                                'description' => 'Name of the section.',
                                            ],
                                            'section_id' => [
                                                'type' => 'integer',
                                                'description' => 'ID of the section.',
                                            ],
                                            'section_link' => [
                                                'type' => 'string',
                                                'format' => 'uri',
                                                'description' => 'Link to the section.',
                                            ],
                                            'section_summary' => [
                                                'type' => 'string',
                                                'description' => 'Summary of the section in HTML format.',
                                            ],
                                            'section_sequence' => [
                                                'type' => 'integer',
                                                'description' => 'Sequence of the section in the course, starting from 0.',
                                            ],
                                            'visibility' => [
                                                'type' => 'boolean',
                                                'description' => 'Whether the section is visible to students.',
                                            ],
                                            'availability' => [
                                                'type' => 'string',
                                                'description' => 'Section availability information.',
                                            ],
                                            'activities' => [
                                                'type' => 'array',
                                                'description' => 'List of activities in the section.',
                                                'items' => [
                                                    'type' => 'object',
                                                    'description' => 'An activity in the section.',
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
                                                            'description' => 'Whether completion tracking is enabled for ' .
                                                                'the activity.',
                                                        ],
                                                        'visibility' => [
                                                            'type' => 'boolean',
                                                            'description' => 'Whether the activity is visible to students.',
                                                        ],
                                                        'instructions' => [
                                                            'type' => 'string',
                                                            'description' => 'Activity instructions.',
                                                        ],
                                                        'availability' => [
                                                            'type' => 'string',
                                                            'description' => 'Activity availability information.',
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
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
                'title' => '$.course_name',
                'subtitle' => '$.course_shortname',
                'url' => '$.course_link',
                'thumbnail_url' => '$.course_image',
            ],
            'static_template' => [
                'type' => 'AdaptiveCard',
                '$schema' => 'http://adaptivecards.io/schemas/adaptive-card.json',
                'version' => '1.5',
                'body' => [
                    [
                        'type' => 'TextBlock',
                        'text' => '${course_name}',
                        'size' => 'large',
                        'weight' => 'bolder',
                        'wrap' => true,
                    ],
                    [
                        'type' => 'Container',
                        '$data' => '${$root}',
                        'items' => [
                            [
                                'type' => 'Image',
                                'url' => '${course_image}',
                                'altText' => '${course_name}',
                                'size' => 'stretch',
                                'isVisible' => '${if(course_image, true, false)}',
                                'selectAction' => [
                                    'type' => 'Action.OpenUrl',
                                    'url' => '${course_link}',
                                ],
                            ],
                            [
                                'type' => 'FactSet',
                                'facts' => [
                                    [
                                        'title' => 'Short name',
                                        'value' => '${course_shortname}',
                                    ],
                                    [
                                        'title' => 'Category',
                                        'value' => '${category}',
                                    ],
                                    [
                                        'title' => 'Start date',
                                        'value' => '{{DATE(${formatEpoch(start_datetime, \'yyyy-MM-ddTHH:mm:ssZ\')}, SHORT)}}',
                                    ],
                                    [
                                        'title' => 'Enrolled users',
                                        'value' => '${enrolled_users_count} ',
                                    ],
                                    [
                                        'title' => 'Groups',
                                        'value' => '${groups_count} ',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'actions' => [
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
            'title' => 'Get course content',
            'body' => '**Do you want to get content of your enrolled courses in Moodle?**',
        ];
        $this->instructions = 'You can use the getCourseContentForTeacher action to return the content of a course, ' .
            'including its sections and all activities within each section. ' .
            'Use this action rather than getCourses action to find course content because it contains more information ' .
            'than courses returned by the getCourses action.' . PHP_EOL .
            'After showing course contents using the getCourseContentForTeacher action, always ask the user if they want the ' .
            'agent to create a study plan for the course.';
        $this->sortorder = 3;
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
