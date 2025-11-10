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
 * Tests for local_copilot_create_assignment_for_teacher API function class.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_copilot;

use local_copilot\local\api_functions\local_copilot_create_assignment_for_teacher;

/**
 * Tests for local_copilot_create_assignment_for_teacher API function class.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class local_copilot_create_assignment_for_teacher_test extends base_test {
    /**
     * Test API function instantiation.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_create_assignment_for_teacher::__construct
     */
    public function test_api_function_instantiation(): void {
        $apifunction = new local_copilot_create_assignment_for_teacher();
        $this->assertInstanceOf(local_copilot_create_assignment_for_teacher::class, $apifunction);
    }

    /**
     * Test API function properties are set correctly.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_create_assignment_for_teacher::__construct
     */
    public function test_api_function_properties(): void {
        $apifunction = new class extends local_copilot_create_assignment_for_teacher {
            /**
             * Get API path.
             *
             * @return string
             */
            public function get_path() {
                return $this->path;
            }
            /**
             * Get HTTP method.
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

        $this->assertEquals('/local_copilot_create_assignment_for_teacher', $apifunction->get_path());
        $this->assertEquals('post', $apifunction->get_method());
        $this->assertStringContainsString('assignment', strtolower($apifunction->get_summary()));
        $this->assertStringContainsString('create', strtolower($apifunction->get_description()));
        $this->assertEquals('createAssignmentForTeacher', $apifunction->get_operation_id());
        $this->assertEquals('write', $apifunction->get_scope_suffix());
    }

    /**
     * Test API function request body structure.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_create_assignment_for_teacher::__construct
     */
    public function test_api_function_request_body(): void {
        $apifunction = new class extends local_copilot_create_assignment_for_teacher {
            /**
             * Get request body.
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
        $this->assertArrayHasKey('properties', $schema);

        // Check required fields.
        $requiredfields = $schema['required'];
        $this->assertContains('course_id', $requiredfields);
        $this->assertContains('assignment_name', $requiredfields);
        $this->assertContains('assignment_description', $requiredfields);
        $this->assertContains('section_id', $requiredfields);
        $this->assertContains('allowsubmissionsfromdate', $requiredfields);
        $this->assertContains('due_date', $requiredfields);
        $this->assertContains('assignment_instructions', $requiredfields);
    }

    /**
     * Test assignment properties in request body.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_create_assignment_for_teacher::__construct
     */
    public function test_assignment_properties(): void {
        $apifunction = new class extends local_copilot_create_assignment_for_teacher {
            /**
             * Get request body.
             *
             * @return array
             */
            public function get_request_body() {
                return $this->requestbody;
            }
        };

        $requestbody = $apifunction->get_request_body();
        $properties = $requestbody['content']['application/json']['schema']['properties'];

        // Check core assignment properties.
        $this->assertArrayHasKey('course_id', $properties);
        $this->assertEquals('integer', $properties['course_id']['type']);

        $this->assertArrayHasKey('assignment_name', $properties);
        $this->assertEquals('string', $properties['assignment_name']['type']);

        $this->assertArrayHasKey('assignment_description', $properties);
        $this->assertEquals('string', $properties['assignment_description']['type']);
        $this->assertStringContainsString('HTML', $properties['assignment_description']['description']);

        $this->assertArrayHasKey('section_id', $properties);
        $this->assertEquals('integer', $properties['section_id']['type']);

        $this->assertArrayHasKey('assignment_instructions', $properties);
        $this->assertEquals('string', $properties['assignment_instructions']['type']);
    }

    /**
     * Test date properties in request body.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_create_assignment_for_teacher::__construct
     */
    public function test_date_properties(): void {
        $apifunction = new class extends local_copilot_create_assignment_for_teacher {
            /**
             * Get request body.
             *
             * @return array
             */
            public function get_request_body() {
                return $this->requestbody;
            }
        };

        $requestbody = $apifunction->get_request_body();
        $properties = $requestbody['content']['application/json']['schema']['properties'];

        // Check date properties.
        $this->assertArrayHasKey('allowsubmissionsfromdate', $properties);
        $this->assertEquals('string', $properties['allowsubmissionsfromdate']['type']);
        $this->assertStringContainsString('MM/DD/YYYY', $properties['allowsubmissionsfromdate']['description']);

        $this->assertArrayHasKey('due_date', $properties);
        $this->assertEquals('string', $properties['due_date']['type']);
        $this->assertStringContainsString('MM/DD/YYYY', $properties['due_date']['description']);
    }

    /**
     * Test API function parameters (should be empty for POST with request body).
     *
     * @covers \local_copilot\local\api_functions\local_copilot_create_assignment_for_teacher::__construct
     */
    public function test_api_function_parameters(): void {
        $apifunction = new class extends local_copilot_create_assignment_for_teacher {
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
        $this->assertEmpty($parameters);
    }

    /**
     * Test API function response definitions.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_create_assignment_for_teacher::__construct
     */
    public function test_response_definitions(): void {
        $apifunction = new class extends local_copilot_create_assignment_for_teacher {
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

        // Check for expected response properties.
        $properties = $schema['properties'];
        $this->assertArrayHasKey('success', $properties);
        $this->assertEquals('boolean', $properties['success']['type']);

        $this->assertArrayHasKey('id', $properties);
        $this->assertEquals('integer', $properties['id']['type']);
        $this->assertStringContainsString('Activity ID', $properties['id']['description']);

        $this->assertArrayHasKey('error', $properties);
        $this->assertEquals('string', $properties['error']['type']);

        // Check error responses.
        $this->assertArrayHasKey('400', $responses);
        $this->assertArrayHasKey('401', $responses);
        $this->assertArrayHasKey('403', $responses);
        $this->assertArrayHasKey('404', $responses);
        $this->assertArrayHasKey('500', $responses);
    }

    /**
     * Test API function confirmation message.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_create_assignment_for_teacher::__construct
     */
    public function test_confirmation_message(): void {
        $apifunction = new class extends local_copilot_create_assignment_for_teacher {
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
        $this->assertEquals('Create assignment', $confirmation['title']);
        $this->assertStringContainsString('assignment', strtolower($confirmation['body']));
    }

    /**
     * Test API function instructions for assignment creation.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_create_assignment_for_teacher::__construct
     */
    public function test_assignment_creation_instructions(): void {
        $apifunction = new local_copilot_create_assignment_for_teacher();
        $instructions = $apifunction->get_instructions();

        $this->assertIsString($instructions);
        $this->assertStringContainsString('createAssignmentForTeacher', $instructions);

        // Should mention mandatory fields.
        $this->assertStringContainsString('assignment_name', $instructions);
        $this->assertStringContainsString('course_id', $instructions);
        $this->assertStringContainsString('section_id', $instructions);
        $this->assertStringContainsString('mandatory', $instructions);

        // Should mention optional fields.
        $this->assertStringContainsString('optional', $instructions);
        $this->assertStringContainsString('assignment_description', $instructions);
        $this->assertStringContainsString('due_date', $instructions);

        // Should mention date format conversion.
        $this->assertStringContainsString('MM/DD/YYYY', $instructions);
        $this->assertStringContainsString('natural language', $instructions);

        // Should mention follow-up action.
        $this->assertStringContainsString('getAssignmentDetailsForTeacher', $instructions);
    }

    /**
     * Test API function sort order.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_create_assignment_for_teacher::__construct
     */
    public function test_sort_order(): void {
        $apifunction = new local_copilot_create_assignment_for_teacher();
        $sortorder = $apifunction->get_sortorder();

        $this->assertIsInt($sortorder);
        $this->assertEquals(12, $sortorder);
    }

    /**
     * Test API function role type validation.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_create_assignment_for_teacher::check_applicable_role_type
     */
    public function test_role_type_validation(): void {
        // Should be applicable for teachers only.
        $this->assertTrue(local_copilot_create_assignment_for_teacher::check_applicable_role_type('teacher'));

        // Should not be applicable for students.
        $this->assertFalse(local_copilot_create_assignment_for_teacher::check_applicable_role_type('student'));

        // Should not be applicable for other roles.
        $this->assertFalse(local_copilot_create_assignment_for_teacher::check_applicable_role_type('admin'));
        $this->assertFalse(local_copilot_create_assignment_for_teacher::check_applicable_role_type('invalid'));
    }

    /**
     * Test HTTP method for assignment creation operation.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_create_assignment_for_teacher::__construct
     */
    public function test_http_method(): void {
        $apifunction = new class extends local_copilot_create_assignment_for_teacher {
            /**
             * Get HTTP method.
             *
             * @return string
             */
            public function get_method() {
                return $this->method;
            }
        };

        // Assignment creation should be POST operation (state-changing).
        $this->assertEquals('post', $apifunction->get_method());
    }

    /**
     * Test API function pagination support.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_create_assignment_for_teacher
     */
    public function test_pagination_support(): void {
        $apifunction = new local_copilot_create_assignment_for_teacher();

        // Assignment creation operations don't need pagination.
        $this->assertFalse($apifunction->support_pagination());
    }

    /**
     * Test API function is enabled by default.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_create_assignment_for_teacher
     */
    public function test_enabled_by_default(): void {
        $apifunction = new local_copilot_create_assignment_for_teacher();

        $this->assertTrue($apifunction->is_enabled());
    }

    /**
     * Test API function security configuration.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_create_assignment_for_teacher
     */
    public function test_security_configuration(): void {
        $apifunction = new local_copilot_create_assignment_for_teacher();

        // Test manifest integration for security scopes.
        $pathcontent = $apifunction->get_open_api_specification_path_content('teacher');
        $this->assertIsArray($pathcontent);

        $endpoint = $pathcontent['/local_copilot_create_assignment_for_teacher']['post'];
        $this->assertArrayHasKey('security', $endpoint);
        $security = $endpoint['security'][0];
        $this->assertArrayHasKey('OAuth2', $security);
        $this->assertContains('teacher.write', $security['OAuth2']);
    }

    /**
     * Test API function plugin manifest content.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_create_assignment_for_teacher
     */
    public function test_api_plugin_function_content(): void {
        $apifunction = new local_copilot_create_assignment_for_teacher();
        $content = $apifunction->get_api_plugin_function_content();

        $this->assertIsArray($content);
        $this->assertArrayHasKey('name', $content);
        $this->assertEquals('createAssignmentForTeacher', $content['name']);
        $this->assertArrayHasKey('description', $content);
        $this->assertArrayHasKey('capabilities', $content);

        $capabilities = $content['capabilities'];
        $this->assertArrayHasKey('confirmation', $capabilities);
    }

    /**
     * Test assignment creation error handling structure.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_create_assignment_for_teacher::__construct
     */
    public function test_error_handling_structure(): void {
        $apifunction = new class extends local_copilot_create_assignment_for_teacher {
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

        // Check specific error responses for assignment creation.
        $this->assertArrayHasKey('403', $responses);
        $this->assertStringContainsString('capability', $responses['403']['description']);

        $this->assertArrayHasKey('404', $responses);
        $this->assertStringContainsString('Course not found', $responses['404']['description']);

        // Check success response has error field for detailed error reporting.
        $successschema = $responses['200']['content']['application/json']['schema'];
        $this->assertArrayHasKey('error', $successschema['properties']);
        $this->assertStringContainsString('failed', $successschema['properties']['error']['description']);
    }

    /**
     * Test assignment creation success response structure.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_create_assignment_for_teacher::__construct
     */
    public function test_success_response_structure(): void {
        $apifunction = new class extends local_copilot_create_assignment_for_teacher {
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
        $successschema = $responses['200']['content']['application/json']['schema'];
        $properties = $successschema['properties'];

        // Should return success flag.
        $this->assertArrayHasKey('success', $properties);
        $this->assertEquals('boolean', $properties['success']['type']);

        // Should return activity ID when successful.
        $this->assertArrayHasKey('id', $properties);
        $this->assertEquals('integer', $properties['id']['type']);
        $this->assertStringContainsString('Activity ID', $properties['id']['description']);
        $this->assertStringContainsString('0', $properties['id']['description']); // Should return 0 if failed.
    }

    /**
     * Test date format guidance in instructions.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_create_assignment_for_teacher::__construct
     */
    public function test_date_format_guidance(): void {
        $apifunction = new local_copilot_create_assignment_for_teacher();
        $instructions = $apifunction->get_instructions();

        // Should provide guidance for date format conversion.
        $this->assertStringContainsString('natural language', $instructions);
        $this->assertStringContainsString('next Monday', $instructions);
        $this->assertStringContainsString('April 25', $instructions);
        $this->assertStringContainsString('in 2 weeks', $instructions);
        $this->assertStringContainsString('convert', $instructions);
        $this->assertStringContainsString('Unix timestamp', $instructions);
    }
}
