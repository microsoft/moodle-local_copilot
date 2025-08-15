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
 * Tests for local_copilot_get_activities_by_type_for_teacher API function class.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_copilot;

use local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_teacher;

defined('MOODLE_INTERNAL') || die();

/**
 * Tests for local_copilot_get_activities_by_type_for_teacher API function class.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_api_functions_local_copilot_get_activities_by_type_for_teacher_test extends base_test {

    /**
     * Test API function instantiation.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_teacher::__construct
     */
    public function test_api_function_instantiation() {
        $apifunction = new local_copilot_get_activities_by_type_for_teacher();
        $this->assertInstanceOf(local_copilot_get_activities_by_type_for_teacher::class, $apifunction);
    }

    /**
     * Test API function properties are set correctly.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_teacher::__construct
     */
    public function test_api_function_properties() {
        $apifunction = new class extends local_copilot_get_activities_by_type_for_teacher {
            public function getPath() { return $this->path; }
            public function getMethod() { return $this->method; }
            public function getSummary() { return $this->summary; }
            public function getDescription() { return $this->description; }
            public function getOperationId() { return $this->operationid; }
            public function getScopeSuffix() { return $this->scopesuffix; }
        };

        $this->assertEquals('/local_copilot_get_activities_by_type_for_teacher', $apifunction->getPath());
        $this->assertEquals('get', $apifunction->getMethod());
        $this->assertStringContainsString('activities', strtolower($apifunction->getSummary()));
        $this->assertStringContainsString('teacher', strtolower($apifunction->getDescription()));
        $this->assertEquals('getActivitiesByTypeForTeacher', $apifunction->getOperationId());
        $this->assertEquals('read', $apifunction->getScopeSuffix());
    }

    /**
     * Test API function parameters are defined correctly.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_teacher::__construct
     */
    public function test_api_function_parameters() {
        $apifunction = new class extends local_copilot_get_activities_by_type_for_teacher {
            public function getParameters() { return $this->parameters; }
        };

        $parameters = $apifunction->getParameters();
        $this->assertIsArray($parameters);
        $this->assertCount(4, $parameters);

        // Check for expected parameters.
        $paramnames = array_column($parameters, 'name');
        $this->assertContains('activity_type', $paramnames);
        $this->assertContains('course_id', $paramnames);
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
     * Test course_id parameter configuration.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_teacher::__construct
     */
    public function test_course_id_parameter() {
        $apifunction = new class extends local_copilot_get_activities_by_type_for_teacher {
            public function getParameters() { return $this->parameters; }
        };

        $parameters = $apifunction->getParameters();
        $courseidparam = null;

        foreach ($parameters as $param) {
            if ($param['name'] === 'course_id') {
                $courseidparam = $param;
                break;
            }
        }

        $this->assertNotNull($courseidparam, 'Course ID parameter should be defined');
        $this->assertEquals('query', $courseidparam['in']);
        $this->assertFalse($courseidparam['required']); // Should be optional for teacher.
        $this->assertEquals('integer', $courseidparam['schema']['type']);
        $this->assertEquals(0, $courseidparam['schema']['default']);
        $this->assertStringContainsString('course id', strtolower($courseidparam['description']));
    }

    /**
     * Test API function response definitions.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_teacher::__construct
     */
    public function test_response_definitions() {
        $apifunction = new class extends local_copilot_get_activities_by_type_for_teacher {
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
    }

    /**
     * Test teacher-specific grading properties in response schema.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_teacher::__construct
     */
    public function test_teacher_grading_properties() {
        $apifunction = new class extends local_copilot_get_activities_by_type_for_teacher {
            public function getResponses() { return $this->responses; }
        };

        $responses = $apifunction->getResponses();
        $properties = $responses['200']['content']['application/json']['schema']['items']['properties'];

        // Check teacher-specific grading properties.
        $this->assertArrayHasKey('graded_users_count', $properties);
        $this->assertEquals('integer', $properties['graded_users_count']['type']);
        $this->assertStringContainsString('graded', $properties['graded_users_count']['description']);

        $this->assertArrayHasKey('average_grade', $properties);
        $this->assertEquals('number', $properties['average_grade']['type']);
        $this->assertStringContainsString('average', $properties['average_grade']['description']);

        $this->assertArrayHasKey('completed_users_count', $properties);
        $this->assertEquals('integer', $properties['completed_users_count']['type']);
        $this->assertStringContainsString('completed', $properties['completed_users_count']['description']);
    }

    /**
     * Test adaptive card template for teacher view.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_teacher::__construct
     */
    public function test_teacher_adaptive_card_template() {
        $apifunction = new class extends local_copilot_get_activities_by_type_for_teacher {
            public function getResponseSemantics() { return $this->responsesemantics; }
        };

        $semantics = $apifunction->getResponseSemantics();
        $template = $semantics['static_template'];

        // Check body structure.
        $this->assertIsArray($template['body']);
        $this->assertNotEmpty($template['body']);

        // Should include grading statistics in facts.
        $container = $template['body'][1];
        $this->assertEquals('Container', $container['type']);
        $this->assertArrayHasKey('items', $container);

        // Check for grading facts.
        $factsets = $container['items'];
        $hasgradedfacts = false;
        $hasaveragegrade = false;

        foreach ($factsets as $factset) {
            if (isset($factset['facts'])) {
                foreach ($factset['facts'] as $fact) {
                    if (isset($fact['title']) && strpos($fact['title'], 'Graded') !== false) {
                        $hasgradedfacts = true;
                    }
                    if (isset($fact['title']) && strpos($fact['title'], 'Average') !== false) {
                        $hasaveragegrade = true;
                    }
                }
            }
        }

        $this->assertTrue($hasgradedfacts, 'Template should include graded students information');
        $this->assertTrue($hasaveragegrade, 'Template should include average grade information');
    }

    /**
     * Test API function instructions for teachers.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_teacher::__construct
     */
    public function test_teacher_instructions() {
        $apifunction = new local_copilot_get_activities_by_type_for_teacher();
        $instructions = $apifunction->get_instructions();

        $this->assertIsString($instructions);
        $this->assertStringContainsString('getActivitiesByTypeForTeacher', $instructions);
        $this->assertStringContainsString('grading', $instructions);
        $this->assertStringContainsString('completion statistics', $instructions);
        $this->assertStringContainsString('average grade', $instructions);
        $this->assertStringContainsString('n/a', $instructions);
    }

    /**
     * Test API function sort order.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_teacher::__construct
     */
    public function test_sort_order() {
        $apifunction = new local_copilot_get_activities_by_type_for_teacher();
        $sortorder = $apifunction->get_sortorder();

        $this->assertIsInt($sortorder);
        $this->assertEquals(5, $sortorder);
    }

    /**
     * Test API function role type validation.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_teacher::check_applicable_role_type
     */
    public function test_role_type_validation() {
        // Should be applicable for teachers only.
        $this->assertTrue(local_copilot_get_activities_by_type_for_teacher::check_applicable_role_type('teacher'));

        // Should not be applicable for students.
        $this->assertFalse(local_copilot_get_activities_by_type_for_teacher::check_applicable_role_type('student'));

        // Should not be applicable for other roles.
        $this->assertFalse(local_copilot_get_activities_by_type_for_teacher::check_applicable_role_type('admin'));
        $this->assertFalse(local_copilot_get_activities_by_type_for_teacher::check_applicable_role_type('invalid'));
    }

    /**
     * Test API function pagination support.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_teacher
     */
    public function test_pagination_support() {
        $apifunction = new local_copilot_get_activities_by_type_for_teacher();

        $this->assertTrue($apifunction->support_pagination());
    }

    /**
     * Test API function response semantics.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_teacher::__construct
     */
    public function test_response_semantics() {
        $apifunction = new class extends local_copilot_get_activities_by_type_for_teacher {
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
    }

    /**
     * Test conditional visibility in adaptive cards.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_teacher::__construct
     */
    public function test_conditional_visibility() {
        $apifunction = new class extends local_copilot_get_activities_by_type_for_teacher {
            public function getResponseSemantics() { return $this->responsesemantics; }
        };

        $semantics = $apifunction->getResponseSemantics();
        $template = $semantics['static_template'];

        // Check for conditional visibility in fact sets.
        $container = $template['body'][1];
        $factsets = $container['items'];

        $hascompletionvisibility = false;
        $hasgradevisibility = false;

        foreach ($factsets as $factset) {
            if (isset($factset['isVisible'])) {
                if (strpos($factset['isVisible'], 'completion_enabled') !== false) {
                    $hascompletionvisibility = true;
                }
                if (strpos($factset['isVisible'], 'average_grade') !== false) {
                    $hasgradevisibility = true;
                }
            }
        }

        $this->assertTrue($hascompletionvisibility, 'Should have completion-based visibility');
        $this->assertTrue($hasgradevisibility, 'Should have grade-based visibility');
    }

    /**
     * Test activity metadata properties.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_teacher::__construct
     */
    public function test_activity_metadata_properties() {
        $apifunction = new class extends local_copilot_get_activities_by_type_for_teacher {
            public function getResponses() { return $this->responses; }
        };

        $responses = $apifunction->getResponses();
        $properties = $responses['200']['content']['application/json']['schema']['items']['properties'];

        // Check availability and instructions.
        $this->assertArrayHasKey('instructions', $properties);
        $this->assertEquals('string', $properties['instructions']['type']);

        $this->assertArrayHasKey('availability', $properties);
        $this->assertEquals('string', $properties['availability']['type']);

        $this->assertArrayHasKey('activity_description', $properties);
        $this->assertEquals('string', $properties['activity_description']['type']);
    }

    /**
     * Test course and section context properties.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_teacher::__construct
     */
    public function test_course_and_section_context() {
        $apifunction = new class extends local_copilot_get_activities_by_type_for_teacher {
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
     * Test API function plugin manifest content.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_teacher
     */
    public function test_api_plugin_function_content() {
        $apifunction = new local_copilot_get_activities_by_type_for_teacher();
        $content = $apifunction->get_api_plugin_function_content();

        $this->assertIsArray($content);
        $this->assertArrayHasKey('name', $content);
        $this->assertEquals('getActivitiesByTypeForTeacher', $content['name']);
        $this->assertArrayHasKey('description', $content);
        $this->assertArrayHasKey('capabilities', $content);

        $capabilities = $content['capabilities'];
        $this->assertArrayHasKey('response_semantics', $capabilities);
        $this->assertArrayHasKey('confirmation', $capabilities);
    }

    /**
     * Test API function confirmation message.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_teacher::__construct
     */
    public function test_confirmation_message() {
        $apifunction = new class extends local_copilot_get_activities_by_type_for_teacher {
            public function getConfirmation() { return $this->confirmation; }
        };

        $confirmation = $apifunction->getConfirmation();
        $this->assertIsArray($confirmation);
        $this->assertEquals('AdaptiveCard', $confirmation['type']);
        $this->assertEquals('Get activities', $confirmation['title']);
        $this->assertStringContainsString('activities', strtolower($confirmation['body']));
    }

    /**
     * Test API function is enabled by default.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_activities_by_type_for_teacher
     */
    public function test_enabled_by_default() {
        $apifunction = new local_copilot_get_activities_by_type_for_teacher();

        $this->assertTrue($apifunction->is_enabled());
    }
}