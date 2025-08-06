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
 * Get course content API for students.
 *
 * @package local_copilot
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2024 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot\local\api_functions;

use local_copilot\manifest_generator;

/**
 * Get course content API for students.
 */
class local_copilot_get_course_content_for_student extends api_function_base {
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->path = '/local_copilot_get_course_content_for_student';
        $this->method = 'get';
        $this->summary = 'Return content of a course with the course ID for student.';
        $this->description = 'Return content of a course, including course details, ' .
            'its sections and activities within the sections for student.';
        $this->operationid = 'getCourseContentForStudent';
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
                                        'If the course does not have an end date, it is set to 0.',
                                ],
                                'completion_enabled' => [
                                    'type' => 'boolean',
                                    'description' => 'Whether completion tracking is enabled for the course.',
                                ],
                                'roles' => [
                                    'type' => 'string',
                                    'description' => 'The name of the roles that the user has in course, separated by commas.',
                                ],
                                'grade' => [
                                    'type' => 'string',
                                    'description' => 'The grade of the student in the course. ' .
                                        'If the student has not received a grade, it is set to \'-1\'.',
                                ],
                                'completed' => [
                                    'type' => 'boolean',
                                    'description' => 'Whether the student has completed the course.',
                                ],
                                'completion_datetime' => [
                                    'type' => 'integer',
                                    'description' => 'If the student has completed the course, ' .
                                        'the completion date time in unix timestamp.',
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
                                                        'completed' => [
                                                            'type' => 'boolean',
                                                            'description' => 'If completion tracking is enabled in the activity, ' .
                                                                'whether the student has completed the activity.',
                                                        ],
                                                        'completion_datetime' => [
                                                            'type' => 'integer',
                                                            'description' => 'If completion tracking is enabled in the activity, ' .
                                                                'and the student has completed the activity, ' .
                                                                'the completion date time in unix timestamp.',
                                                        ],
                                                        'activity_grade' => [
                                                            'type' => 'string',
                                                            'description' => 'If the student has received a grade for the ' .
                                                                'activity, the grade; otherwise \'-1\'.',
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
                                        'title' => 'Completed',
                                        'value' => '${if(completion_enabled, if(completed, \'Yes\', \'No\'), ' .
                                            '\'Completion is not enabled\')}',
                                    ],
                                    [
                                        'title' => 'Grade',
                                        'value' => '${if(grade != \'-1\', grade, \'Not graded\')}',
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
        $this->instructions = 'You can use the getCourseContentForStudent action to find the content of a course for ' .
            'student. The response includes course details, sections, and activities within each section. ' .
            'It only contains sections and activities that are accessible by the user.' . PHP_EOL .
            'After showing course contents using the getCourseContentForStudent action, always ask the user if they want the ' .
            'agent to create a study plan for the course.' . PHP_EOL .
            'If the user uses the phrase "List the activities I have in course" without specifying a course name, ' .
            'always ask them to provide the course name before calling getCourseContentForStudent. ' .
            'For example: "Which course would you like me to check?"';
        $this->sortorder = 4;
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
