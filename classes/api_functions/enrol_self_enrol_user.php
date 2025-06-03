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
 * Enrol self to a course using self enrolment method API function.
 *
 * @package local_copilot
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license https://opensource.org/license/MIT MIT License
 * @copyright (C) 2024 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot\api_functions;

use local_copilot\manifest_generator;

/**
 * Self enrol to course API for students.
 */
class enrol_self_enrol_user extends api_function_base {
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->path = '/enrol_self_enrol_user';
        $this->method = 'post';
        $this->summary = 'Enrol the user in a course using self enrolment method.';
        $this->description = 'This API function enrols the current user to the course with the provided course ID using ' .
            'self enrolment method.';
        $this->operationid = 'enrolSelfEnrolUser';
        $this->scopesuffix = 'write';
        $this->parameters = [];
        $this->requestbody = [
            'content' => [
                'application/json' => [
                    'schema' => [
                        'type' => 'object',
                        'required' => ['courseid'],
                        'properties' => [
                            'courseid' => [
                                'type' => 'integer',
                                'description' => 'ID of the course.',
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->responses = [
            '200' => [
                'description' => 'Information about enrolment results.',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'description' => 'Information about enrolment results.',
                            'properties' => [
                                'status' => [
                                    'type' => 'boolean',
                                    'description' => 'Whether the user was enrolled successfully.',
                                ],
                                'warnings' => [
                                    'type' => 'array',
                                    'description' => 'List of warnings.',
                                    'items' => [
                                        'type' => 'object',
                                        'description' => 'Warning details.',
                                        'properties' => [
                                            'item' => [
                                                'type' => 'string',
                                                'description' => 'Instance.',
                                            ],
                                            'itemid' => [
                                                'type' => 'integer',
                                                'description' => 'ID of the self enrolment method.',
                                            ],
                                            'warningcode' => [
                                                'type' => 'string',
                                                'description' => 'Warning code. Can be 1, 2, 3, or 4.',
                                            ],
                                            'message' => [
                                                'type' => 'string',
                                                'description' => 'Warning message, details of warning.',
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
            '403' => [
                'description' => 'The user does not have capability to self enrol.',
            ],
            '404' => [
                'description' => 'Course not found in Moodle.',
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
                        'text' => 'Congratulations on your enrolment! Time to explore, learn, and grow.',
                        'size' => 'medium',
                        'weight' => 'bolder',
                        'wrap' => true,
                    ],
                ],
            ],
        ];
        $this->confirmation = [
            'type' => 'AdaptiveCard',
            'title' => 'Course enrolment',
            'body' => '**Do you want to enrol in this course?**',
        ];
        $this->instructions = 'You can use the enrolSelfEnrolUser action to enrol the user to a course with the given ID. ' .
            'If the enrolment is successful, show information of the course, which can be accessed using the ' .
            'getCourseContentForStudent action, to the user.';
        $this->sortorder = 11;
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
