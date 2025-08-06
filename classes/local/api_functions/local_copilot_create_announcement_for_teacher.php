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
 * Create an announcement in the news forum API for teachers.
 *
 * @package local_copilot
 * @author Dorel Manolescu <dorel.manolescu@enovation.ie>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2024 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot\local\api_functions;

use local_copilot\manifest_generator;

/**
 * Create announcement API for teachers.
 */
class local_copilot_create_announcement_for_teacher extends api_function_base {
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->path = '/local_copilot_create_announcement_for_teacher';
        $this->method = 'post';
        $this->summary = 'Create an announcement in a course.';
        $this->description = 'This API function creates an announcement in the course with the provided course ID using the ' .
            'announcement details provided in the request.';
        $this->operationid = 'createAnnouncementForTeacher';
        $this->scopesuffix = 'write';
        $this->parameters = [];
        $this->requestbody = [
            'content' => [
                'application/json' => [
                    'schema' => [
                        'type' => 'object',
                        'required' => ['course_id', 'announcement_subject', 'announcement_message'],
                        'properties' => [
                            'course_id' => [
                                'type' => 'integer',
                                'description' => 'ID of the course to create the announcement.',
                            ],
                            'announcement_subject' => [
                                'type' => 'string',
                                'description' => 'Subject of the announcement.',
                            ],
                            'announcement_message' => [
                                'type' => 'string',
                                'description' => 'Message of the announcement in HTML format.',
                            ],
                            'announcement_pinned' => [
                                'type' => 'boolean',
                                'description' => 'Whether the announcement is pinned.',
                            ],
                            'announcement_timestart' => [
                                'type' => 'string',
                                'description' => 'Start time of the announcement in American date format (MM/DD/YYYY).',
                            ],
                            'announcement_timeend' => [
                                'type' => 'string',
                                'description' => 'End time of the announcement in American date format (MM/DD/YYYY).',
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->responses = [
            '200' => [
                'description' => 'Whether the announcement was created successfully.',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'description' => 'Whether the announcement was created successfully.',
                            'properties' => [
                                'success' => [
                                    'type' => 'boolean',
                                    'description' => 'Whether the announcement was created successfully.',
                                ],
                                'id' => [
                                    'type' => 'integer',
                                    'description' => 'ID of the created announcement, if successful; otherwise 0.',
                                ],
                                'error' => [
                                    'type' => 'string',
                                    'description' => 'Error message if the announcement creation failed.',
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
                'description' => 'The user does not have capability to create an announcement.',
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
                        'text' => 'Your announcement has been posted! Keep your learners informed and engaged.',
                        'size' => 'medium',
                        'weight' => 'bolder',
                        'wrap' => true,
                    ],
                ],
            ],
        ];
        $this->confirmation = [
            'type' => 'AdaptiveCard',
            'title' => 'Create announcement',
            'body' => '**Do you want to create a new announcement in Moodle?**',
        ];
        $this->instructions = 'You can use the createAnnouncementForTeacher action to create an announcement in a specific ' .
            'course. ' .
            'Before using the createAnnouncementForTeacher action, always ensure that the announcement_subject, course_id, ' .
            'and announcement_message are provided, as they are mandatory. ' .
            'If any of these are missing, ask the user for the required information, clearly distinguishing between mandatory ' .
            'and optional fields. ' .
            'The mandatory fields are announcement_subject, course_id, and announcement_message. ' .
            'The optional fields are announcement_pinned, announcement_timestart, and announcement_timeend. ' .
            'The course_id sets the course, announcement_subject defines the subject of the announcement, and ' .
            'announcement_message contains the message in HTML format. ' .
            'If announcement_pinned is not provided, assume it is set to "false" by default. While it\'s NOT required, ' .
            'the user can choose to provide announcement_timestart or announcement_timeend in natural language ' .
            '(e.g., "next Monday", "April 25, 2025", "MM/DD/YYYY", or "in 2 weeks"). ' .
            'You should convert this input to the American date format (MM/DD/YYYY) before calling the web service. ' .
            'Do NOT ask the user to provide a Unix timestamp. ' .
            'If you used the createAnnouncementForTeacher action to create an announcement successfully, ' .
            'confirm the success to the user and provide the ID of the created announcement.';
        $this->sortorder = 13;
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
