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
 * Tests for enrol_self_enrol_user API function class.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_copilot;

use local_copilot\local\api_functions\enrol_self_enrol_user;

defined('MOODLE_INTERNAL') || die();

/**
 * Tests for enrol_self_enrol_user API function class.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_api_functions_enrol_self_enrol_user_test extends base_test {

    /**
     * Test API function instantiation.
     *
     * @covers \local_copilot\local\api_functions\enrol_self_enrol_user::__construct
     */
    public function test_api_function_instantiation() {
        $apifunction = new enrol_self_enrol_user();
        $this->assertInstanceOf(enrol_self_enrol_user::class, $apifunction);
    }

    /**
     * Test API function properties are set correctly.
     *
     * @covers \local_copilot\local\api_functions\enrol_self_enrol_user::__construct
     */
    public function test_api_function_properties() {
        $apifunction = new class extends enrol_self_enrol_user {
            public function getPath() { return $this->path; }
            public function getMethod() { return $this->method; }
            public function getSummary() { return $this->summary; }
            public function getDescription() { return $this->description; }
            public function getOperationId() { return $this->operationid; }
            public function getScopeSuffix() { return $this->scopesuffix; }
        };

        $this->assertEquals('/enrol_self_enrol_user', $apifunction->getPath());
        $this->assertEquals('post', $apifunction->getMethod());
        $this->assertStringContainsString('enrol', strtolower($apifunction->getSummary()));
        $this->assertStringContainsString('self enrolment', strtolower($apifunction->getDescription()));
        $this->assertEquals('enrolSelfEnrolUser', $apifunction->getOperationId());
        $this->assertEquals('write', $apifunction->getScopeSuffix());
    }

    /**
     * Test API function request body structure.
     *
     * @covers \local_copilot\local\api_functions\enrol_self_enrol_user::__construct
     */
    public function test_api_function_request_body() {
        $apifunction = new class extends enrol_self_enrol_user {
            public function getRequestBody() { return $this->requestbody; }
        };

        $requestbody = $apifunction->getRequestBody();
        $this->assertIsArray($requestbody);
        $this->assertArrayHasKey('content', $requestbody);
        $this->assertArrayHasKey('application/json', $requestbody['content']);
        $this->assertArrayHasKey('schema', $requestbody['content']['application/json']);

        $schema = $requestbody['content']['application/json']['schema'];
        $this->assertEquals('object', $schema['type']);
        $this->assertArrayHasKey('required', $schema);
        $this->assertContains('courseid', $schema['required']);
        $this->assertArrayHasKey('properties', $schema);
        $this->assertArrayHasKey('courseid', $schema['properties']);
        $this->assertEquals('integer', $schema['properties']['courseid']['type']);
    }

    /**
     * Test API function parameters (should be empty for POST with request body).
     *
     * @covers \local_copilot\local\api_functions\enrol_self_enrol_user::__construct
     */
    public function test_api_function_parameters() {
        $apifunction = new class extends enrol_self_enrol_user {
            public function getParameters() { return $this->parameters; }
        };

        $parameters = $apifunction->getParameters();
        $this->assertIsArray($parameters);
        $this->assertEmpty($parameters);
    }

    /**
     * Test API function response definitions.
     *
     * @covers \local_copilot\local\api_functions\enrol_self_enrol_user::__construct
     */
    public function test_response_definitions() {
        $apifunction = new class extends enrol_self_enrol_user {
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
        $this->assertEquals('object', $schema['type']);
        $this->assertArrayHasKey('properties', $schema);

        // Check for expected enrolment response properties.
        $properties = $schema['properties'];
        $this->assertArrayHasKey('status', $properties);
        $this->assertEquals('boolean', $properties['status']['type']);
        $this->assertArrayHasKey('warnings', $properties);
        $this->assertEquals('array', $properties['warnings']['type']);

        // Check error responses.
        $this->assertArrayHasKey('400', $responses);
        $this->assertArrayHasKey('401', $responses);
        $this->assertArrayHasKey('403', $responses);
        $this->assertArrayHasKey('404', $responses);
        $this->assertArrayHasKey('500', $responses);
    }

    /**
     * Test API function security configuration.
     *
     * @covers \local_copilot\local\api_functions\enrol_self_enrol_user
     */
    public function test_security_configuration() {
        $apifunction = new enrol_self_enrol_user();
        
        // Test manifest integration for security scopes.
        $pathcontent = $apifunction->get_open_api_specification_path_content('student');
        $this->assertIsArray($pathcontent);
        
        $endpoint = $pathcontent['/enrol_self_enrol_user']['post'];
        $this->assertArrayHasKey('security', $endpoint);
        $security = $endpoint['security'][0];
        $this->assertArrayHasKey('OAuth2', $security);
        $this->assertContains('student.write', $security['OAuth2']);
    }

    /**
     * Test API function response semantics.
     *
     * @covers \local_copilot\local\api_functions\enrol_self_enrol_user::__construct
     */
    public function test_response_semantics() {
        $apifunction = new class extends enrol_self_enrol_user {
            public function getResponseSemantics() { return $this->responsesemantics; }
        };

        $semantics = $apifunction->getResponseSemantics();
        $this->assertIsArray($semantics);
        $this->assertArrayHasKey('data_path', $semantics);
        $this->assertEquals('$', $semantics['data_path']);
        $this->assertArrayHasKey('static_template', $semantics);
        
        $template = $semantics['static_template'];
        $this->assertEquals('AdaptiveCard', $template['type']);
        $this->assertArrayHasKey('body', $template);
        $this->assertIsArray($template['body']);
    }

    /**
     * Test API function confirmation message.
     *
     * @covers \local_copilot\local\api_functions\enrol_self_enrol_user::__construct
     */
    public function test_confirmation_message() {
        $apifunction = new class extends enrol_self_enrol_user {
            public function getConfirmation() { return $this->confirmation; }
        };

        $confirmation = $apifunction->getConfirmation();
        $this->assertIsArray($confirmation);
        $this->assertEquals('AdaptiveCard', $confirmation['type']);
        $this->assertEquals('Course enrolment', $confirmation['title']);
        $this->assertStringContainsString('enrol', strtolower($confirmation['body']));
    }

    /**
     * Test API function instructions.
     *
     * @covers \local_copilot\local\api_functions\enrol_self_enrol_user::__construct
     */
    public function test_instructions() {
        $apifunction = new enrol_self_enrol_user();
        $instructions = $apifunction->get_instructions();
        
        $this->assertIsString($instructions);
        $this->assertStringContainsString('enrolSelfEnrolUser', $instructions);
        $this->assertStringContainsString('getCourseContentForStudent', $instructions);
    }

    /**
     * Test API function sort order.
     *
     * @covers \local_copilot\local\api_functions\enrol_self_enrol_user::__construct
     */
    public function test_sort_order() {
        $apifunction = new enrol_self_enrol_user();
        $sortorder = $apifunction->get_sortorder();
        
        $this->assertIsInt($sortorder);
        $this->assertEquals(11, $sortorder);
    }

    /**
     * Test API function role type validation.
     *
     * @covers \local_copilot\local\api_functions\enrol_self_enrol_user::check_applicable_role_type
     */
    public function test_role_type_validation() {
        // Should be applicable for students only.
        $this->assertTrue(enrol_self_enrol_user::check_applicable_role_type('student'));
        
        // Should not be applicable for teachers.
        $this->assertFalse(enrol_self_enrol_user::check_applicable_role_type('teacher'));
        
        // Should not be applicable for other roles.
        $this->assertFalse(enrol_self_enrol_user::check_applicable_role_type('admin'));
        $this->assertFalse(enrol_self_enrol_user::check_applicable_role_type('invalid'));
    }

    /**
     * Test warning structure in response schema.
     *
     * @covers \local_copilot\local\api_functions\enrol_self_enrol_user::__construct
     */
    public function test_warning_structure() {
        $apifunction = new class extends enrol_self_enrol_user {
            public function getResponses() { return $this->responses; }
        };

        $responses = $apifunction->getResponses();
        $successresponse = $responses['200'];
        $schema = $successresponse['content']['application/json']['schema'];
        $warnings = $schema['properties']['warnings'];
        
        $this->assertEquals('array', $warnings['type']);
        $this->assertArrayHasKey('items', $warnings);
        
        $warningitem = $warnings['items'];
        $this->assertEquals('object', $warningitem['type']);
        $this->assertArrayHasKey('properties', $warningitem);
        
        $warningprops = $warningitem['properties'];
        $this->assertArrayHasKey('item', $warningprops);
        $this->assertArrayHasKey('itemid', $warningprops);
        $this->assertArrayHasKey('warningcode', $warningprops);
        $this->assertArrayHasKey('message', $warningprops);
        
        $this->assertEquals('string', $warningprops['item']['type']);
        $this->assertEquals('integer', $warningprops['itemid']['type']);
        $this->assertEquals('string', $warningprops['warningcode']['type']);
        $this->assertEquals('string', $warningprops['message']['type']);
    }

    /**
     * Test API function plugin manifest content.
     *
     * @covers \local_copilot\local\api_functions\enrol_self_enrol_user
     */
    public function test_api_plugin_function_content() {
        $apifunction = new enrol_self_enrol_user();
        $content = $apifunction->get_api_plugin_function_content();
        
        $this->assertIsArray($content);
        $this->assertArrayHasKey('name', $content);
        $this->assertEquals('enrolSelfEnrolUser', $content['name']);
        $this->assertArrayHasKey('description', $content);
        $this->assertArrayHasKey('capabilities', $content);
        
        $capabilities = $content['capabilities'];
        $this->assertArrayHasKey('response_semantics', $capabilities);
        $this->assertArrayHasKey('confirmation', $capabilities);
    }

    /**
     * Test API function with course ID validation.
     *
     * @covers \local_copilot\local\api_functions\enrol_self_enrol_user::__construct
     */
    public function test_courseid_validation() {
        $apifunction = new class extends enrol_self_enrol_user {
            public function getRequestBody() { return $this->requestbody; }
        };

        $requestbody = $apifunction->getRequestBody();
        $schema = $requestbody['content']['application/json']['schema'];
        
        // Courseid should be required.
        $this->assertContains('courseid', $schema['required']);
        
        // Courseid should be integer type.
        $courseidprop = $schema['properties']['courseid'];
        $this->assertEquals('integer', $courseidprop['type']);
        $this->assertArrayHasKey('description', $courseidprop);
        $this->assertStringContainsString('course', strtolower($courseidprop['description']));
    }

    /**
     * Test HTTP method for enrolment operation.
     *
     * @covers \local_copilot\local\api_functions\enrol_self_enrol_user::__construct
     */
    public function test_http_method() {
        $apifunction = new class extends enrol_self_enrol_user {
            public function getMethod() { return $this->method; }
        };

        // Enrolment should be POST operation (state-changing).
        $this->assertEquals('post', $apifunction->getMethod());
    }

    /**
     * Test API function enables pagination support.
     *
     * @covers \local_copilot\local\api_functions\enrol_self_enrol_user
     */
    public function test_pagination_support() {
        $apifunction = new enrol_self_enrol_user();
        
        // Enrolment operations don't need pagination.
        $this->assertFalse($apifunction->support_pagination());
    }

    /**
     * Test API function is enabled by default.
     *
     * @covers \local_copilot\local\api_functions\enrol_self_enrol_user
     */
    public function test_enabled_by_default() {
        $apifunction = new enrol_self_enrol_user();
        
        $this->assertTrue($apifunction->is_enabled());
    }
}