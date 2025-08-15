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

defined('MOODLE_INTERNAL') || die();

/**
 * Tests for local_copilot_get_activities_by_type_for_student API function class.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_api_functions_local_copilot_get_activities_by_type_for_student_test extends base_test {

    /**
     * Test API function instantiation.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_student::__construct
     */
    public function test_api_function_instantiation() {
        $apifunction = new local_copilot_get_activities_by_type_for_student();
        $this->assertInstanceOf(local_copilot_get_activities_by_type_for_student::class, $apifunction);
    }

    /**
     * Test API function properties are set correctly.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_student::__construct
     */
    public function test_api_function_properties() {
        $apifunction = new class extends local_copilot_get_activities_by_type_for_student {
            public function getPath() { return $this->path; }
            public function getMethod() { return $this->method; }
            public function getSummary() { return $this->summary; }
            public function getDescription() { return $this->description; }
            public function getOperationId() { return $this->operationid; }
            public function getScopeSuffix() { return $this->scopesuffix; }
        };

        $this->assertEquals('/local_copilot_get_activities_by_type_for_student', $apifunction->getPath());
        $this->assertEquals('get', $apifunction->getMethod());
        $this->assertStringContainsString('activities', strtolower($apifunction->getSummary()));
        $this->assertStringContainsString('student', strtolower($apifunction->getDescription()));
        $this->assertEquals('getActivitiesByTypeForStudent', $apifunction->getOperationId());
        $this->assertEquals('read', $apifunction->getScopeSuffix());
    }

    /**
     * Test API function parameters are defined correctly.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_student::__construct
     */
    public function test_api_function_parameters() {
        $apifunction = new class extends local_copilot_get_activities_by_type_for_student {
            public function getParameters() { return $this->parameters; }
        };

        $parameters = $apifunction->getParameters();
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
    public function test_activity_type_parameter() {
        $apifunction = new class extends local_copilot_get_activities_by_type_for_student {
            public function getParameters() { return $this->parameters; }
        };

        $parameters = $apifunction->getParameters();
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
    public function test_limit_parameter() {
        $apifunction = new class extends local_copilot_get_activities_by_type_for_student {
            public function getParameters() { return $this->parameters; }
        };

        $parameters = $apifunction->getParameters();
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
    public function test_offset_parameter() {
        $apifunction = new class extends local_copilot_get_activities_by_type_for_student {
            public function getParameters() { return $this->parameters; }
        };

        $parameters = $apifunction->getParameters();
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
    public function test_response_definitions() {
        $apifunction = new class extends local_copilot_get_activities_by_type_for_student {
            public function getResponses() { return $this->responses; }
        };

        $responses = $apifunction->getResponses();
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
    public function test_completion_properties() {
        $apifunction = new class extends local_copilot_get_activities_by_type_for_student {
            public function getResponses() { return $this->responses; }
        };

        $responses = $apifunction->getResponses();
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
    public function test_response_semantics() {
        $apifunction = new class extends local_copilot_get_activities_by_type_for_student {
            public function getResponseSemantics() { return $this->responsesemantics; }
        };

        $semantics = $apifunction->getResponseSemantics();
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
    public function test_adaptive_card_template() {
        $apifunction = new class extends local_copilot_get_activities_by_type_for_student {
            public function getResponseSemantics() { return $this->responsesemantics; }
        };

        $semantics = $apifunction->getResponseSemantics();
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
    public function test_confirmation_message() {
        $apifunction = new class extends local_copilot_get_activities_by_type_for_student {
            public function getConfirmation() { return $this->confirmation; }
        };

        $confirmation = $apifunction->getConfirmation();
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
    public function test_instructions() {
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
    public function test_sort_order() {
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
    public function test_role_type_validation() {
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
    public function test_pagination_support() {
        $apifunction = new local_copilot_get_activities_by_type_for_student();

        $this->assertTrue($apifunction->support_pagination());
    }

    /**
     * Test API function is enabled by default.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_student
     */
    public function test_enabled_by_default() {
        $apifunction = new local_copilot_get_activities_by_type_for_student();

        $this->assertTrue($apifunction->is_enabled());
    }

    /**
     * Test course and section context properties.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_student::__construct
     */
    public function test_course_and_section_context() {
        $apifunction = new class extends local_copilot_get_activities_by_type_for_student {
            public function getResponses() { return $this->responses; }
        };

        $responses = $apifunction->getResponses();
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
    public function test_activity_metadata_properties() {
        $apifunction = new class extends local_copilot_get_activities_by_type_for_student {
            public function getResponses() { return $this->responses; }
        };

        $responses = $apifunction->getResponses();
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
    public function test_api_plugin_function_content() {
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