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
 * Get courses API for teachers and students.
 *
 * @package local_copilot
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2024 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot\api_functions;

use local_copilot\manifest_generator;

/**
 * Get courses API for teachers and students.
 */
class local_copilot_get_courses extends api_function_base {
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->path = '/local_copilot_get_courses';
        $this->method = 'get';
        $this->summary = 'Return the list of all courses the current user is enrolled in.';
        $this->description = 'This API function returns a list of courses that the current user is enrolled in. ' .
            'Information returned for each course includes course name, course ID, course short name, course link, ' .
            'course summary, category, visibility, start date, end date, and a list of roles that the user has in the course, ' .
            'separated by commas.';
        $this->operationid = 'getCourses';
        $this->scopesuffix = 'read';
        $this->parameters = [
            [
                'name' => 'limit',
                'in' => 'query',
                'description' => 'The maximum number of courses to return.',
                'required' => false,
                'schema' => [
                    'type' => 'integer',
                    'default' => 10,
                ],
            ],
            [
                'name' => 'offset',
                'in' => 'query',
                'description' => 'The number of courses to skip before starting to collect the result set.',
                'required' => false,
                'schema' => [
                    'type' => 'integer',
                    'default' => 0,
                ],
            ],
        ];
        $this->responses = [
            '200' => [
                'description' => 'A list of courses that the user is enrolled in.',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'array',
                            'description' => 'A list of courses that the user is enrolled in.',
                            'items' => [
                                'type' => 'object',
                                'description' => 'Details of a course.',
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
                                    'course_image' => [
                                        'type' => 'string',
                                        'format' => 'uri',
                                        'description' => 'Link to the course image.',
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
                                        'description' => 'The name of the roles that the user has in course, ' .
                                            'separated by commas.',
                                    ],
                                    'has_more' => [
                                        'type' => 'boolean',
                                        'description' => 'Flag indicating if there are more courses to be fetched.',
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
                        'items' => [
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
                                ],
                                'spacing' => 'none',
                            ],
                            [
                                'type' => 'FactSet',
                                'facts' => [
                                    [
                                        'title' => 'End date',
                                        'value' => '{{DATE(${formatEpoch(end_datetime, \'yyyy-MM-ddTHH:mm:ssZ\')}, SHORT)}}',
                                    ],
                                ],
                                'isVisible' => '${if(end_datetime != 0, true, false)}',
                                'spacing' => 'none',
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
            'title' => 'Get courses',
            'body' => '**Do you want to get the list of your enrolled courses in Moodle?**',
        ];
        $this->instructions = 'You can use the getCourses action to find Moodle courses that the user is enrolled in. ' .
            'Don\'t show course images in the result because they may be at different sizes.';
        $this->supportpagination = true;
        $this->sortorder = 1;
    }

    /**
     * Check if the API function is applicable to the given role type.
     *
     * @param string $roletype
     * @return bool
     */
    public static function check_applicable_role_type(string $roletype): bool {
        if (in_array($roletype, [manifest_generator::ROLE_TYPE_TEACHER, manifest_generator::ROLE_TYPE_STUDENT])) {
            return true;
        }

        return false;
    }
}
