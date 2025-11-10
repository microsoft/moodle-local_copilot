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
 * Tests for local_copilot_get_activities_by_type_for_student API function class.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_copilot;

use local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_student;

/**
 * Tests for local_copilot_get_activities_by_type_for_student API function class.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class local_copilot_get_activities_by_type_for_student_test extends base_test {
    /**
     * Test API function instantiation.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_student::__construct
     */
    public function test_api_function_instantiation(): void {
        $apifunction = new local_copilot_get_activities_by_type_for_student();
        $this->assertInstanceOf(local_copilot_get_activities_by_type_for_student::class, $apifunction);
    }

    /**
     * Test API function properties are set correctly.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_student::__construct
     */
    public function test_api_function_properties(): void {
        $apifunction = new class extends local_copilot_get_activities_by_type_for_student {
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

        $this->assertEquals('/local_copilot_get_activities_by_type_for_student', $apifunction->get_path());
        $this->assertEquals('get', $apifunction->get_method());
        $this->assertStringContainsString('activities', strtolower($apifunction->get_summary()));
        $this->assertStringContainsString('student', strtolower($apifunction->get_description()));
        $this->assertEquals('getActivitiesByTypeForStudent', $apifunction->get_operation_id());
        $this->assertEquals('read', $apifunction->get_scope_suffix());
    }

    /**
     * Test API function parameters are defined correctly.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_student::__construct
     */
    public function test_api_function_parameters(): void {
        $apifunction = new class extends local_copilot_get_activities_by_type_for_student {
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
        $this->assertCount(3, $parameters);

        // Check for expected parameters.
        $paramnames = array_column($parameters, 'name');
        $this->assertContains('activity_type', $paramnames);
        $this->assertContains('limit', $paramnames);
        $this->assertContains('offset', $paramnames);

        // Validate parameter structure.
        foreach ($parameters as $param) {
            $this->assertArrayHasKey('name', $param);
            $this->assertArrayHasKey('in', $param);
            $this->assertArrayHasKey('required', $param);
            $this->assertArrayHasKey('description', $param);
            $this->assertArrayHasKey('schema', $param);
        }
    }

    /**
     * Test activity_type parameter configuration.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_student::__construct
     */
    public function test_activity_type_parameter(): void {
        $apifunction = new class extends local_copilot_get_activities_by_type_for_student {
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
        $activitytypeparam = null;

        foreach ($parameters as $param) {
            if ($param['name'] === 'activity_type') {
                $activitytypeparam = $param;
                break;
            }
        }

        $this->assertNotNull($activitytypeparam, 'Activity type parameter should be defined');
        $this->assertEquals('query', $activitytypeparam['in']);
        $this->assertTrue($activitytypeparam['required']); // Should be required.
        $this->assertEquals('string', $activitytypeparam['schema']['type']);
        $this->assertStringContainsString('forum', $activitytypeparam['description']);
        $this->assertStringContainsString('assignment', $activitytypeparam['description']);
        $this->assertStringContainsString('quiz', $activitytypeparam['description']);
    }

    /**
     * Test limit parameter configuration.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_student::__construct
     */
    public function test_limit_parameter(): void {
        $apifunction = new class extends local_copilot_get_activities_by_type_for_student {
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
        $limitparam = null;

        foreach ($parameters as $param) {
            if ($param['name'] === 'limit') {
                $limitparam = $param;
                break;
            }
        }

        $this->assertNotNull($limitparam, 'Limit parameter should be defined');
        $this->assertEquals('query', $limitparam['in']);
        $this->assertFalse($limitparam['required']); // Should be optional.
        $this->assertEquals('integer', $limitparam['schema']['type']);
        $this->assertEquals(10, $limitparam['schema']['default']);
    }

    /**
     * Test offset parameter configuration.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_student::__construct
     */
    public function test_offset_parameter(): void {
        $apifunction = new class extends local_copilot_get_activities_by_type_for_student {
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
        $offsetparam = null;

        foreach ($parameters as $param) {
            if ($param['name'] === 'offset') {
                $offsetparam = $param;
                break;
            }
        }

        $this->assertNotNull($offsetparam, 'Offset parameter should be defined');
        $this->assertEquals('query', $offsetparam['in']);
        $this->assertFalse($offsetparam['required']); // Should be optional.
        $this->assertEquals('integer', $offsetparam['schema']['type']);
        $this->assertEquals(0, $offsetparam['schema']['default']);
    }

    /**
     * Test API function response definitions.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_student::__construct
     */
    public function test_response_definitions(): void {
        $apifunction = new class extends local_copilot_get_activities_by_type_for_student {
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
        $this->assertEquals('array', $schema['type']);
        $this->assertArrayHasKey('items', $schema);
        $this->assertEquals('object', $schema['items']['type']);
        $this->assertArrayHasKey('properties', $schema['items']);

        // Check for expected activity properties.
        $properties = $schema['items']['properties'];
        $this->assertArrayHasKey('activity_name', $properties);
        $this->assertArrayHasKey('activity_id', $properties);
        $this->assertArrayHasKey('activity_type', $properties);
        $this->assertArrayHasKey('activity_link', $properties);
        $this->assertArrayHasKey('completion_enabled', $properties);
        $this->assertArrayHasKey('completed', $properties);
        $this->assertArrayHasKey('course_name', $properties);
        $this->assertArrayHasKey('course_id', $properties);
        $this->assertArrayHasKey('section_name', $properties);

        // Check error responses.
        $this->assertArrayHasKey('400', $responses);
        $this->assertArrayHasKey('401', $responses);
        $this->assertArrayHasKey('404', $responses);
        $this->assertArrayHasKey('500', $responses);
    }

    /**
     * Test activity completion properties in response schema.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_student::__construct
     */
    public function test_completion_properties(): void {
        $apifunction = new class extends local_copilot_get_activities_by_type_for_student {
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
        $properties = $responses['200']['content']['application/json']['schema']['items']['properties'];

        // Check completion-related properties.
        $this->assertArrayHasKey('completion_enabled', $properties);
        $this->assertEquals('boolean', $properties['completion_enabled']['type']);

        $this->assertArrayHasKey('completed', $properties);
        $this->assertEquals('boolean', $properties['completed']['type']);

        $this->assertArrayHasKey('completion_datetime', $properties);
        $this->assertEquals('integer', $properties['completion_datetime']['type']);

        $this->assertArrayHasKey('activity_grade', $properties);
        $this->assertEquals('string', $properties['activity_grade']['type']);
    }

    /**
     * Test API function response semantics for Adaptive Cards.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_student::__construct
     */
    public function test_response_semantics(): void {
        $apifunction = new class extends local_copilot_get_activities_by_type_for_student {
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
        $this->assertEquals('$.activity_type', $props['subtitle']);
        $this->assertEquals('$.activity_link', $props['url']);

        $this->assertArrayHasKey('static_template', $semantics);
        $template = $semantics['static_template'];
        $this->assertEquals('AdaptiveCard', $template['type']);
        $this->assertArrayHasKey('body', $template);
        $this->assertArrayHasKey('actions', $template);
    }

    /**
     * Test adaptive card template structure.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_student::__construct
     */
    public function test_adaptive_card_template(): void {
        $apifunction = new class extends local_copilot_get_activities_by_type_for_student {
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

        // Check body structure.
        $this->assertIsArray($template['body']);
        $this->assertNotEmpty($template['body']);

        // Check actions structure.
        $this->assertArrayHasKey('actions', $template);
        $actions = $template['actions'];
        $this->assertCount(2, $actions);

        // Should have action for activity and course.
        $this->assertEquals('Action.OpenUrl', $actions[0]['type']);
        $this->assertEquals('Open activity', $actions[0]['title']);
        $this->assertEquals('Action.OpenUrl', $actions[1]['type']);
        $this->assertEquals('Open course', $actions[1]['title']);
    }

    /**
     * Test API function confirmation message.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_student::__construct
     */
    public function test_confirmation_message(): void {
        $apifunction = new class extends local_copilot_get_activities_by_type_for_student {
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
        $this->assertEquals('Get activities', $confirmation['title']);
        $this->assertStringContainsString('activities', strtolower($confirmation['body']));
    }

    /**
     * Test API function instructions.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_student::__construct
     */
    public function test_instructions(): void {
        $apifunction = new local_copilot_get_activities_by_type_for_student();
        $instructions = $apifunction->get_instructions();

        $this->assertIsString($instructions);
        $this->assertStringContainsString('getActivitiesByTypeForStudent', $instructions);
        $this->assertStringContainsString('completion status', $instructions);
        $this->assertStringContainsString('grade', $instructions);
    }

    /**
     * Test API function sort order.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_student::__construct
     */
    public function test_sort_order(): void {
        $apifunction = new local_copilot_get_activities_by_type_for_student();
        $sortorder = $apifunction->get_sortorder();

        $this->assertIsInt($sortorder);
        $this->assertEquals(6, $sortorder);
    }

    /**
     * Test API function role type validation.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_student::check_applicable_role_type
     */
    public function test_role_type_validation(): void {
        // Should be applicable for students only.
        $this->assertTrue(local_copilot_get_activities_by_type_for_student::check_applicable_role_type('student'));

        // Should not be applicable for teachers.
        $this->assertFalse(local_copilot_get_activities_by_type_for_student::check_applicable_role_type('teacher'));

        // Should not be applicable for other roles.
        $this->assertFalse(local_copilot_get_activities_by_type_for_student::check_applicable_role_type('admin'));
        $this->assertFalse(local_copilot_get_activities_by_type_for_student::check_applicable_role_type('invalid'));
    }

    /**
     * Test API function pagination support.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_student
     */
    public function test_pagination_support(): void {
        $apifunction = new local_copilot_get_activities_by_type_for_student();

        $this->assertTrue($apifunction->support_pagination());
    }

    /**
     * Test API function is enabled by default.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_student
     */
    public function test_enabled_by_default(): void {
        $apifunction = new local_copilot_get_activities_by_type_for_student();

        $this->assertTrue($apifunction->is_enabled());
    }

    /**
     * Test course and section context properties.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_student::__construct
     */
    public function test_course_and_section_context(): void {
        $apifunction = new class extends local_copilot_get_activities_by_type_for_student {
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
        $properties = $responses['200']['content']['application/json']['schema']['items']['properties'];

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
     * Test activity availability and instructions properties.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_student::__construct
     */
    public function test_activity_metadata_properties(): void {
        $apifunction = new class extends local_copilot_get_activities_by_type_for_student {
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
        $properties = $responses['200']['content']['application/json']['schema']['items']['properties'];

        // Check availability and instructions.
        $this->assertArrayHasKey('instructions', $properties);
        $this->assertEquals('string', $properties['instructions']['type']);
        $this->assertStringContainsString('instructions', $properties['instructions']['description']);

        $this->assertArrayHasKey('availability', $properties);
        $this->assertEquals('string', $properties['availability']['type']);
        $this->assertStringContainsString('availability', $properties['availability']['description']);

        $this->assertArrayHasKey('activity_description', $properties);
        $this->assertEquals('string', $properties['activity_description']['type']);
    }

    /**
     * Test API function plugin manifest content.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_student
     */
    public function test_api_plugin_function_content(): void {
        $apifunction = new local_copilot_get_activities_by_type_for_student();
        $content = $apifunction->get_api_plugin_function_content();

        $this->assertIsArray($content);
        $this->assertArrayHasKey('name', $content);
        $this->assertEquals('getActivitiesByTypeForStudent', $content['name']);
        $this->assertArrayHasKey('description', $content);
        $this->assertArrayHasKey('capabilities', $content);

        $capabilities = $content['capabilities'];
        $this->assertArrayHasKey('response_semantics', $capabilities);
        $this->assertArrayHasKey('confirmation', $capabilities);
    }
}
