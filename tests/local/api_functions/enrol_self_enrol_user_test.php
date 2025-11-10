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

/**
 * Tests for enrol_self_enrol_user API function class.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class enrol_self_enrol_user_test extends base_test {
    /**
     * Test API function instantiation.
     *
     * @covers \local_copilot\local\api_functions\enrol_self_enrol_user::__construct
     */
    public function test_api_function_instantiation(): void {
        $apifunction = new enrol_self_enrol_user();
        $this->assertInstanceOf(enrol_self_enrol_user::class, $apifunction);
    }

    /**
     * Test API function properties are set correctly.
     *
     * @covers \local_copilot\local\api_functions\enrol_self_enrol_user::__construct
     */
    public function test_api_function_properties(): void {
        $apifunction = new class extends enrol_self_enrol_user {
            /**
             * Expose path property for testing.
             *
             * @return string
             */
            public function get_path() {
                return $this->path;
            }
            /**
             * Expose method property for testing.
             *
             * @return string
             */
            public function get_method() {
                return $this->method;
            }
            /**
             * Expose summary for testing.
             *
             * @return string
             */
            public function get_summary() {
                return $this->summary;
            }
            /**
             * Expose description for testing.
             *
             * @return string
             */
            public function get_description() {
                return $this->description;
            }
            /**
             * Expose operation ID for testing.
             *
             * @return string
             */
            public function get_operation_id() {
                return $this->operationid;
            }
            /**
             * Expose scope suffix for testing.
             *
             * @return string
             */
            public function get_scope_suffix() {
                return $this->scopesuffix;
            }
        };

        $this->assertEquals('/enrol_self_enrol_user', $apifunction->get_path());
        $this->assertEquals('post', $apifunction->get_method());
        $this->assertStringContainsString('enrol', strtolower($apifunction->get_summary()));
        $this->assertStringContainsString('self enrolment', strtolower($apifunction->get_description()));
        $this->assertEquals('enrolSelfEnrolUser', $apifunction->get_operation_id());
        $this->assertEquals('write', $apifunction->get_scope_suffix());
    }

    /**
     * Test API function request body structure.
     *
     * @covers \local_copilot\local\api_functions\enrol_self_enrol_user::__construct
     */
    public function test_api_function_request_body(): void {
        $apifunction = new class extends enrol_self_enrol_user {
            /**
             * Expose request body for testing.
             *
             * @return array
             */
            public function get_request_body() {
                return $this->requestbody;
            }
        };

        $requestbody = $apifunction->get_request_body();
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
    public function test_api_function_parameters(): void {
        $apifunction = new class extends enrol_self_enrol_user {
            /**
             * Expose parameters for testing.
             *
             * @return array
             */
            public function get_parameters() {
                return $this->parameters;
            }
        };

        $parameters = $apifunction->get_parameters();
        $this->assertIsArray($parameters);
        $this->assertEmpty($parameters);
    }

    /**
     * Test API function response definitions.
     *
     * @covers \local_copilot\local\api_functions\enrol_self_enrol_user::__construct
     */
    public function test_response_definitions(): void {
        $apifunction = new class extends enrol_self_enrol_user {
            /**
             * Expose responses for testing.
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
    public function test_security_configuration(): void {
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
    public function test_response_semantics(): void {
        $apifunction = new class extends enrol_self_enrol_user {
            /**
             * Expose response semantics for testing.
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
    public function test_confirmation_message(): void {
        $apifunction = new class extends enrol_self_enrol_user {
            /**
             * Expose confirmation for testing.
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
        $this->assertEquals('Course enrolment', $confirmation['title']);
        $this->assertStringContainsString('enrol', strtolower($confirmation['body']));
    }

    /**
     * Test API function instructions.
     *
     * @covers \local_copilot\local\api_functions\enrol_self_enrol_user::__construct
     */
    public function test_instructions(): void {
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
    public function test_sort_order(): void {
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
    public function test_role_type_validation(): void {
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
    public function test_warning_structure(): void {
        $apifunction = new class extends enrol_self_enrol_user {
            /**
             * Expose responses for testing.
             *
             * @return array
             */
            public function get_responses() {
                return $this->responses;
            }
        };

        $responses = $apifunction->get_responses();
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
    public function test_api_plugin_function_content(): void {
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
    public function test_courseid_validation(): void {
        $apifunction = new class extends enrol_self_enrol_user {
            /**
             * Expose request body for testing.
             *
             * @return array
             */
            public function get_request_body() {
                return $this->requestbody;
            }
        };

        $requestbody = $apifunction->get_request_body();
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
    public function test_http_method(): void {
        $apifunction = new class extends enrol_self_enrol_user {
            /**
             * Expose method property for testing.
             *
             * @return string
             */
            public function get_method() {
                return $this->method;
            }
        };

        // Enrolment should be POST operation (state-changing).
        $this->assertEquals('post', $apifunction->get_method());
    }

    /**
     * Test API function enables pagination support.
     *
     * @covers \local_copilot\local\api_functions\enrol_self_enrol_user
     */
    public function test_pagination_support(): void {
        $apifunction = new enrol_self_enrol_user();

        // Enrolment operations don't need pagination.
        $this->assertFalse($apifunction->support_pagination());
    }

    /**
     * Test API function is enabled by default.
     *
     * @covers \local_copilot\local\api_functions\enrol_self_enrol_user
     */
    public function test_enabled_by_default(): void {
        $apifunction = new enrol_self_enrol_user();

        $this->assertTrue($apifunction->is_enabled());
    }
}
