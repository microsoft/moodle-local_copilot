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
 * Get self enrolment instances API for students.
 *
 * @package local_copilot
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license https://opensource.org/license/MIT MIT License
 * @copyright (C) 2024 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot\local\api_functions;

use local_copilot\manifest_generator;

/**
 * Get self enrolment instances API for students.
 */
class local_copilot_get_self_enrolment_instances_for_student extends api_function_base {
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->path = '/local_copilot_get_self_enrolment_instances_for_student';
        $this->method = 'get';
        $this->summary = 'Return a list of courses the current user can self-enrol in.';
        $this->description = 'This API function returns all courses the current user isn\'t enrolled in yet, but is eligible ' .
            'to self-enrol in. Essentially, this is a list of Moodle courses with the self-enrolment method enabled and visible ' .
            'to users. Information returned for each course includes course name, course ID, course link, self-enrolment method ' .
            'ID, self-enrolment method name, and self-enrolment method status.';
        $this->operationid = 'getSelfEnrolmentInstancesForStudent';
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
        $this->requestbody = [
            'content' => [
                'application/json' => [
                    'schema' => [
                        'type' => 'object',
                    ],
                ],
            ],
        ];
        $this->responses = [
            '200' => [
                'description' => 'Information about courses that the user can self enrol in.',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'array',
                            'description' => 'A list of records about courses that the user can self enrol in.',
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
                                    'course_link' => [
                                        'type' => 'string',
                                        'format' => 'uri',
                                        'description' => 'Link to the course.',
                                    ],
                                    'course_shortname' => [
                                        'type' => 'string',
                                        'description' => 'Short name of the course, which is unique on the Moodle site.',
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
                                    'self_enrolment_method_id' => [
                                        'type' => 'integer',
                                        'description' => 'ID of the self enrolment method.',
                                    ],
                                    'self_enrolment_method_name' => [
                                        'type' => 'string',
                                        'description' => 'Name of the self enrolment method.',
                                    ],
                                    'self_enrolment_method_type' => [
                                        'type' => 'string',
                                        'description' => 'Self enrolment method type. Can be either self or guest.',
                                    ],
                                    'self_enrolment_method_status' => [
                                        'type' => 'string',
                                        'description' => 'Boolean true if the user can self enrol, false if the user can\'t, ' .
                                            'or a string if there is an error.',
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
                                'spacing' => 'small',
                                'isVisible' => '${if(course_image, true, false)}',
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
                                        'title' => 'Enrolment method',
                                        'value' => '${self_enrolment_method_name}',
                                    ],
                                ],
                            ],
                            [
                                'type' => 'ActionSet',
                                'actions' => [
                                    [
                                        'type' => 'Action.OpenUrl',
                                        'title' => 'View course',
                                        'url' => '${course_link}',
                                        'isVisible' => '${if(course_link, true, false)}',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->confirmation = [
            'type' => 'AdaptiveCard',
            'title' => 'Get self enrolment',
            'body' => '**Do you want to get the list of courses that you can self enrol in?**',
        ];
        $this->instructions = 'You can use the getSelfEnrolmentInstancesForStudent action to find courses that the user is not ' .
            'enrolled in yet, but can enrol by themselves.' . PHP_EOL .
            'If a question asks you to find courses that the user can enrol in, follow these steps:' . PHP_EOL .
            '1. Use the getSelfEnrolmentInstancesForStudent action to find courses that the user can enrol in by themselves.' .
            PHP_EOL .
            '2. Ask the user if they would like to self enrol to a course, don\'t ask the user to click on the course ' .
            'names to enrol.' . PHP_EOL .
            '3. You can use the enrolSelfEnrolUser action to ensure a course to a course using self enrolment method. ' .
            'If the enrolment is successful, show information of the course, which can be accessed using the ' .
            'getCourseContentForStudent action, to the user.';
        $this->supportpagination = true;
        $this->sortorder = 10;
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
