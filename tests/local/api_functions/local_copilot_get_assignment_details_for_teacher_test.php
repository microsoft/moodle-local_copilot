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
 * Tests for local_copilot_get_assignment_details_for_teacher API function class.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_copilot;

use local_copilot\local\api_functions\local_copilot_get_assignment_details_for_teacher;

/**
 * Tests for local_copilot_get_assignment_details_for_teacher API function class.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class local_copilot_get_assignment_details_for_teacher_test extends base_test {
    /**
     * Test API function instantiation.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_assignment_details_for_teacher::__construct
     */
    public function test_api_function_instantiation(): void {
        $apifunction = new local_copilot_get_assignment_details_for_teacher();
        $this->assertInstanceOf(local_copilot_get_assignment_details_for_teacher::class, $apifunction);
    }

    /**
     * Test API function properties are set correctly.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_assignment_details_for_teacher::__construct
     */
    public function test_api_function_properties(): void {
        $apifunction = new class extends local_copilot_get_assignment_details_for_teacher {
            /**
             * Get path.
             *
             * @return string
             */
            public function get_path() {
                return $this->path;
            }
            /**
             * Get method.
             *
             * @return string
             */
            public function get_method() {
                return $this->method;
            }
            /**
             * Get summary.
             *
             * @return string
             */
            public function get_summary() {
                return $this->summary;
            }
            /**
             * Get description.
             *
             * @return string
             */
            public function get_description() {
                return $this->description;
            }
            /**
             * Get operation ID.
             *
             * @return string
             */
            public function get_operation_id() {
                return $this->operationid;
            }
            /**
             * Get scope suffix.
             *
             * @return string
             */
            public function get_scope_suffix() {
                return $this->scopesuffix;
            }
        };

        $this->assertEquals('/local_copilot_get_assignment_details_for_teacher', $apifunction->get_path());
        $this->assertEquals('get', $apifunction->get_method());
        $this->assertStringContainsString('assignment', strtolower($apifunction->get_summary()));
        $this->assertStringContainsString('teacher', strtolower($apifunction->get_description()));
        $this->assertEquals('getAssignmentDetailsForTeacher', $apifunction->get_operation_id());
        $this->assertEquals('read', $apifunction->get_scope_suffix());
    }

    /**
     * Test API function parameters are defined correctly.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_assignment_details_for_teacher::__construct
     */
    public function test_api_function_parameters(): void {
        $apifunction = new class extends local_copilot_get_assignment_details_for_teacher {
            /**
             * Get parameters.
             *
             * @return array
             */
            public function get_parameters() {
                return $this->parameters;
            }
        };

        $parameters = $apifunction->get_parameters();
        $this->assertIsArray($parameters);
        $this->assertCount(1, $parameters);

        // Check activity_id parameter.
        $param = $parameters[0];
        $this->assertEquals('activity_id', $param['name']);
        $this->assertEquals('query', $param['in']);
        $this->assertTrue($param['required']);
        $this->assertEquals('integer', $param['schema']['type']);
        $this->assertStringContainsString('assignment activity', $param['description']);
    }

    /**
     * Test API function response definitions.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_assignment_details_for_teacher::__construct
     */
    public function test_response_definitions(): void {
        $apifunction = new class extends local_copilot_get_assignment_details_for_teacher {
            /**
             * Get responses.
             *
             * @return array
             */
            public function get_responses() {
                return $this->responses;
            }
        };

        $responses = $apifunction->get_responses();
        $this->assertIsArray($responses);

        // Should have success response.
        $this->assertArrayHasKey('200', $responses);
        $successresponse = $responses['200'];
        $this->assertArrayHasKey('description', $successresponse);
        $this->assertArrayHasKey('content', $successresponse);
        $this->assertArrayHasKey('application/json', $successresponse['content']);

        // Check schema structure.
        $schema = $successresponse['content']['application/json']['schema'];
        $this->assertEquals('object', $schema['type']);
        $this->assertArrayHasKey('properties', $schema);

        // Check for expected assignment properties.
        $properties = $schema['properties'];
        $this->assertArrayHasKey('activity_name', $properties);
        $this->assertArrayHasKey('activity_id', $properties);
        $this->assertArrayHasKey('activity_link', $properties);
        $this->assertArrayHasKey('activity_description', $properties);
        $this->assertArrayHasKey('due_date', $properties);
        $this->assertArrayHasKey('instructions', $properties);

        // Check error responses.
        $this->assertArrayHasKey('400', $responses);
        $this->assertArrayHasKey('401', $responses);
        $this->assertArrayHasKey('404', $responses);
        $this->assertArrayHasKey('500', $responses);
    }

    /**
     * Test assignment submissions structure in response schema.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_assignment_details_for_teacher::__construct
     */
    public function test_submissions_structure(): void {
        $apifunction = new class extends local_copilot_get_assignment_details_for_teacher {
            /**
             * Get responses.
             *
             * @return array
             */
            public function get_responses() {
                return $this->responses;
            }
        };

        $responses = $apifunction->get_responses();
        $properties = $responses['200']['content']['application/json']['schema']['properties'];

        // Check submissions array structure.
        $this->assertArrayHasKey('submissions', $properties);
        $submissions = $properties['submissions'];
        $this->assertEquals('array', $submissions['type']);
        $this->assertArrayHasKey('items', $submissions);

        // Check submission item structure.
        $submissionitem = $submissions['items'];
        $this->assertEquals('object', $submissionitem['type']);
        $this->assertArrayHasKey('properties', $submissionitem);

        // Check submission properties.
        $submissionprops = $submissionitem['properties'];
        $this->assertArrayHasKey('student_user_id', $submissionprops);
        $this->assertArrayHasKey('submitted', $submissionprops);
        $this->assertArrayHasKey('submission_datetime', $submissionprops);
        $this->assertArrayHasKey('activity_grade', $submissionprops);
        $this->assertArrayHasKey('completed', $submissionprops);
        $this->assertArrayHasKey('completion_datetime', $submissionprops);

        $this->assertEquals('integer', $submissionprops['student_user_id']['type']);
        $this->assertEquals('boolean', $submissionprops['submitted']['type']);
        $this->assertEquals('integer', $submissionprops['submission_datetime']['type']);
        $this->assertEquals('string', $submissionprops['activity_grade']['type']);
        $this->assertEquals('boolean', $submissionprops['completed']['type']);
        $this->assertEquals('integer', $submissionprops['completion_datetime']['type']);
    }

    /**
     * Test assignment statistics properties.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_assignment_details_for_teacher::__construct
     */
    public function test_assignment_statistics_properties(): void {
        $apifunction = new class extends local_copilot_get_assignment_details_for_teacher {
            /**
             * Get responses.
             *
             * @return array
             */
            public function get_responses() {
                return $this->responses;
            }
        };

        $responses = $apifunction->get_responses();
        $properties = $responses['200']['content']['application/json']['schema']['properties'];

        // Check statistics properties.
        $this->assertArrayHasKey('submissions_count', $properties);
        $this->assertEquals('integer', $properties['submissions_count']['type']);

        $this->assertArrayHasKey('graded_submissions_count', $properties);
        $this->assertEquals('integer', $properties['graded_submissions_count']['type']);

        $this->assertArrayHasKey('average_grade', $properties);
        $this->assertEquals('string', $properties['average_grade']['type']);
        $this->assertStringContainsString('n/a', $properties['average_grade']['description']);

        $this->assertArrayHasKey('completed_users_count', $properties);
        $this->assertEquals('integer', $properties['completed_users_count']['type']);
    }

    /**
     * Test adaptive card template structure for assignments.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_assignment_details_for_teacher::__construct
     */
    public function test_adaptive_card_template(): void {
        $apifunction = new class extends local_copilot_get_assignment_details_for_teacher {
            /**
             * Get response semantics.
             *
             * @return array
             */
            public function get_response_semantics() {
                return $this->responsesemantics;
            }
        };

        $semantics = $apifunction->get_response_semantics();
        $template = $semantics['static_template'];

        // Check template visibility control.
        $this->assertArrayHasKey('isVisible', $template);
        $this->assertEquals('${use_card}', $template['isVisible']);

        // Check body structure.
        $this->assertIsArray($template['body']);
        $this->assertNotEmpty($template['body']);

        // Check actions structure.
        $this->assertArrayHasKey('actions', $template);
        $actions = $template['actions'];
        $this->assertCount(2, $actions);

        // Should have action for assignment and course.
        $this->assertEquals('Action.OpenUrl', $actions[0]['type']);
        $this->assertEquals('Open assignment', $actions[0]['title']);
        $this->assertEquals('Action.OpenUrl', $actions[1]['type']);
        $this->assertEquals('Open course', $actions[1]['title']);
    }

    /**
     * Test assignment details adaptive card facts.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_assignment_details_for_teacher::__construct
     */
    public function test_assignment_details_facts(): void {
        $apifunction = new class extends local_copilot_get_assignment_details_for_teacher {
            /**
             * Get response semantics.
             *
             * @return array
             */
            public function get_response_semantics() {
                return $this->responsesemantics;
            }
        };

        $semantics = $apifunction->get_response_semantics();
        $template = $semantics['static_template'];

        // Check container with facts.
        $container = $template['body'][1];
        $this->assertEquals('Container', $container['type']);
        $this->assertArrayHasKey('items', $container);

        // Check for assignment-specific facts.
        $factsets = $container['items'];
        $hasduedatefacts = false;
        $hassubmissionfacts = false;
        $hasgradedfacts = false;

        foreach ($factsets as $factset) {
            if (isset($factset['facts'])) {
                foreach ($factset['facts'] as $fact) {
                    if (isset($fact['title'])) {
                        if (strpos($fact['title'], 'Due date') !== false) {
                            $hasduedatefacts = true;
                        }
                        if (strpos($fact['title'], 'Submitted') !== false) {
                            $hassubmissionfacts = true;
                        }
                        if (strpos($fact['title'], 'Graded') !== false) {
                            $hasgradedfacts = true;
                        }
                    }
                }
            }
        }

        $this->assertTrue($hasduedatefacts, 'Template should include due date information');
        $this->assertTrue($hassubmissionfacts, 'Template should include submission statistics');
        $this->assertTrue($hasgradedfacts, 'Template should include grading statistics');
    }

    /**
     * Test API function response semantics.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_assignment_details_for_teacher::__construct
     */
    public function test_response_semantics(): void {
        $apifunction = new class extends local_copilot_get_assignment_details_for_teacher {
            /**
             * Get response semantics.
             *
             * @return array
             */
            public function get_response_semantics() {
                return $this->responsesemantics;
            }
        };

        $semantics = $apifunction->get_response_semantics();
        $this->assertIsArray($semantics);
        $this->assertArrayHasKey('data_path', $semantics);
        $this->assertEquals('$', $semantics['data_path']);

        $this->assertArrayHasKey('properties', $semantics);
        $props = $semantics['properties'];
        $this->assertEquals('$.activity_name', $props['title']);
        $this->assertEquals('$.activity_link', $props['url']);

        $this->assertArrayHasKey('static_template', $semantics);
        $template = $semantics['static_template'];
        $this->assertEquals('AdaptiveCard', $template['type']);
    }

    /**
     * Test API function instructions for assignment details.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_assignment_details_for_teacher::__construct
     */
    public function test_assignment_details_instructions(): void {
        $apifunction = new local_copilot_get_assignment_details_for_teacher();
        $instructions = $apifunction->get_instructions();

        $this->assertIsString($instructions);
        $this->assertStringContainsString('getAssignmentDetailsForTeacher', $instructions);
        $this->assertStringContainsString('activity_id', $instructions);

        // Should mention preference over getActivitiesByTypeForTeacher.
        $this->assertStringContainsString('getActivitiesByTypeForTeacher', $instructions);
        $this->assertStringContainsString('NOT the', $instructions);
        $this->assertStringContainsString('more information', $instructions);

        // Should mention workflow for finding assignments.
        $this->assertStringContainsString('getCourseContentForTeacher', $instructions);
        $this->assertStringContainsString('course they are referring to', $instructions);
    }

    /**
     * Test API function confirmation message.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_assignment_details_for_teacher::__construct
     */
    public function test_confirmation_message(): void {
        $apifunction = new class extends local_copilot_get_assignment_details_for_teacher {
            /**
             * Get confirmation message.
             *
             * @return array
             */
            public function get_confirmation() {
                return $this->confirmation;
            }
        };

        $confirmation = $apifunction->get_confirmation();
        $this->assertIsArray($confirmation);
        $this->assertEquals('AdaptiveCard', $confirmation['type']);
        $this->assertEquals('Get assignment details', $confirmation['title']);
        $this->assertStringContainsString('assignment', strtolower($confirmation['body']));
    }

    /**
     * Test API function sort order.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_assignment_details_for_teacher::__construct
     */
    public function test_sort_order(): void {
        $apifunction = new local_copilot_get_assignment_details_for_teacher();
        $sortorder = $apifunction->get_sortorder();

        $this->assertIsInt($sortorder);
        $this->assertEquals(7, $sortorder);
    }

    /**
     * Test API function role type validation.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_assignment_details_for_teacher::check_applicable_role_type
     */
    public function test_role_type_validation(): void {
        // Should be applicable for teachers only.
        $this->assertTrue(local_copilot_get_assignment_details_for_teacher::check_applicable_role_type('teacher'));

        // Should not be applicable for students.
        $this->assertFalse(local_copilot_get_assignment_details_for_teacher::check_applicable_role_type('student'));

        // Should not be applicable for other roles.
        $this->assertFalse(local_copilot_get_assignment_details_for_teacher::check_applicable_role_type('admin'));
        $this->assertFalse(local_copilot_get_assignment_details_for_teacher::check_applicable_role_type('invalid'));
    }

    /**
     * Test API function pagination support.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_assignment_details_for_teacher
     */
    public function test_pagination_support(): void {
        $apifunction = new local_copilot_get_assignment_details_for_teacher();

        // Assignment detail operations don't need pagination.
        $this->assertFalse($apifunction->support_pagination());
    }

    /**
     * Test API function is enabled by default.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_assignment_details_for_teacher
     */
    public function test_enabled_by_default(): void {
        $apifunction = new local_copilot_get_assignment_details_for_teacher();

        $this->assertTrue($apifunction->is_enabled());
    }

    /**
     * Test course and section context properties.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_assignment_details_for_teacher::__construct
     */
    public function test_course_and_section_context(): void {
        $apifunction = new class extends local_copilot_get_assignment_details_for_teacher {
            /**
             * Get responses.
             *
             * @return array
             */
            public function get_responses() {
                return $this->responses;
            }
        };

        $responses = $apifunction->get_responses();
        $properties = $responses['200']['content']['application/json']['schema']['properties'];

        // Check course context properties.
        $this->assertArrayHasKey('course_name', $properties);
        $this->assertEquals('string', $properties['course_name']['type']);

        $this->assertArrayHasKey('course_id', $properties);
        $this->assertEquals('integer', $properties['course_id']['type']);

        $this->assertArrayHasKey('course_link', $properties);
        $this->assertEquals('string', $properties['course_link']['type']);
        $this->assertEquals('uri', $properties['course_link']['format']);

        // Check section context properties.
        $this->assertArrayHasKey('section_name', $properties);
        $this->assertEquals('string', $properties['section_name']['type']);
    }

    /**
     * Test assignment due date and completion properties.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_assignment_details_for_teacher::__construct
     */
    public function test_assignment_due_date_and_completion(): void {
        $apifunction = new class extends local_copilot_get_assignment_details_for_teacher {
            /**
             * Get responses.
             *
             * @return array
             */
            public function get_responses() {
                return $this->responses;
            }
        };

        $responses = $apifunction->get_responses();
        $properties = $responses['200']['content']['application/json']['schema']['properties'];

        // Check due date property.
        $this->assertArrayHasKey('due_date', $properties);
        $this->assertEquals('integer', $properties['due_date']['type']);
        $this->assertStringContainsString('unix timestamp', $properties['due_date']['description']);

        // Check completion property.
        $this->assertArrayHasKey('completion_enabled', $properties);
        $this->assertEquals('boolean', $properties['completion_enabled']['type']);
    }

    /**
     * Test use_card property for conditional rendering.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_assignment_details_for_teacher::__construct
     */
    public function test_use_card_property(): void {
        $apifunction = new class extends local_copilot_get_assignment_details_for_teacher {
            /**
             * Get responses.
             *
             * @return array
             */
            public function get_responses() {
                return $this->responses;
            }
        };

        $responses = $apifunction->get_responses();
        $properties = $responses['200']['content']['application/json']['schema']['properties'];

        // Check use_card property for conditional card rendering.
        $this->assertArrayHasKey('use_card', $properties);
        $this->assertEquals('boolean', $properties['use_card']['type']);
        $this->assertStringContainsString('card view', $properties['use_card']['description']);
    }

    /**
     * Test API function plugin manifest content.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_assignment_details_for_teacher
     */
    public function test_api_plugin_function_content(): void {
        $apifunction = new local_copilot_get_assignment_details_for_teacher();
        $content = $apifunction->get_api_plugin_function_content();

        $this->assertIsArray($content);
        $this->assertArrayHasKey('name', $content);
        $this->assertEquals('getAssignmentDetailsForTeacher', $content['name']);
        $this->assertArrayHasKey('description', $content);
        $this->assertArrayHasKey('capabilities', $content);

        $capabilities = $content['capabilities'];
        $this->assertArrayHasKey('response_semantics', $capabilities);
        $this->assertArrayHasKey('confirmation', $capabilities);
    }
}
