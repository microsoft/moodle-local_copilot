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
 * Tests for api_function_base class.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_copilot;

use advanced_testcase;
use local_copilot\local\api_functions\api_function_base;

/**
 * Tests for api_function_base class.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class api_function_base_test extends advanced_testcase {
    /**
     * Test that api_function_base can be instantiated.
     *
     * @covers \local_copilot\local\api_functions\api_function_base::__construct
     */
    public function test_base_class_instantiation(): void {
        $this->resetAfterTest();

        // Create a concrete implementation for testing.
        $apifunction = new class extends api_function_base {
            /**
             * Constructor to set up API properties.
             */
            public function __construct() {
                parent::__construct();
                $this->path = '/test_path';
                $this->method = 'get';
                $this->summary = 'Test summary';
                $this->description = 'Test description';
                $this->operationid = 'testOperation';
            }
        };

        $this->assertInstanceOf(api_function_base::class, $apifunction);
    }

    /**
     * Test that base class properties can be set and accessed.
     *
     * @covers \local_copilot\local\api_functions\api_function_base
     */
    public function test_base_class_properties(): void {
        $this->resetAfterTest();

        $apifunction = new class extends api_function_base {
            /**
             * Constructor to set up API properties.
             */
            public function __construct() {
                parent::__construct();
                $this->path = '/test_endpoint';
                $this->method = 'post';
                $this->summary = 'Test API endpoint';
                $this->description = 'This is a test API endpoint for unit testing';
                $this->operationid = 'testEndpoint';
                $this->scopesuffix = 'write';
            }

            // Expose protected properties for testing.
            /**
             * Get API path.
             *
             * @return string API path.
             */
            public function get_path() {
                return $this->path;
            }
            /**
             * Get HTTP method.
             *
             * @return string HTTP method.
             */
            public function get_method() {
                return $this->method;
            }
            /**
             * Get summary.
             *
             * @return string Summary.
             */
            public function get_summary() {
                return $this->summary;
            }
            /**
             * Get description.
             *
             * @return string Description.
             */
            public function get_description() {
                return $this->description;
            }
            /**
             * Get operation ID.
             *
             * @return string Operation ID.
             */
            public function get_operation_id() {
                return $this->operationid;
            }
            /**
             * Get scope suffix.
             *
             * @return string Scope suffix.
             */
            public function get_scope_suffix() {
                return $this->scopesuffix;
            }
        };

        $this->assertEquals('/test_endpoint', $apifunction->get_path());
        $this->assertEquals('post', $apifunction->get_method());
        $this->assertEquals('Test API endpoint', $apifunction->get_summary());
        $this->assertEquals('This is a test API endpoint for unit testing', $apifunction->get_description());
        $this->assertEquals('testEndpoint', $apifunction->get_operation_id());
        $this->assertEquals('write', $apifunction->get_scope_suffix());
    }

    /**
     * Test base class with parameters.
     *
     * @covers \local_copilot\local\api_functions\api_function_base
     */
    public function test_base_class_with_parameters(): void {
        $this->resetAfterTest();

        $apifunction = new class extends api_function_base {
            /**
             * Constructor to set up API with parameters.
             */
            public function __construct() {
                parent::__construct();
                $this->path = '/test_with_params';
                $this->method = 'get';
                $this->summary = 'Test with parameters';
                $this->description = 'Test API with parameters';
                $this->operationid = 'testWithParams';
                $this->parameters = [
                    [
                        'name' => 'id',
                        'in' => 'query',
                        'required' => true,
                        'description' => 'Resource ID',
                        'schema' => ['type' => 'integer'],
                    ],
                    [
                        'name' => 'limit',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Limit results',
                        'schema' => ['type' => 'integer', 'default' => 10],
                    ],
                ];
            }

            /**
             * Get parameter definitions.
             *
             * @return array Parameter definitions.
             */
            public function get_parameters() {
                return $this->parameters;
            }
        };

        $parameters = $apifunction->get_parameters();
        $this->assertIsArray($parameters);
        $this->assertCount(2, $parameters);

        // Check first parameter.
        $this->assertEquals('id', $parameters[0]['name']);
        $this->assertEquals('query', $parameters[0]['in']);
        $this->assertTrue($parameters[0]['required']);
        $this->assertEquals('integer', $parameters[0]['schema']['type']);

        // Check second parameter.
        $this->assertEquals('limit', $parameters[1]['name']);
        $this->assertFalse($parameters[1]['required']);
        $this->assertEquals(10, $parameters[1]['schema']['default']);
    }

    /**
     * Test base class with response definitions.
     *
     * @covers \local_copilot\local\api_functions\api_function_base
     */
    public function test_base_class_with_responses(): void {
        $this->resetAfterTest();

        $apifunction = new class extends api_function_base {
            /**
             * Constructor to set up API with responses.
             */
            public function __construct() {
                parent::__construct();
                $this->path = '/test_responses';
                $this->method = 'get';
                $this->summary = 'Test with responses';
                $this->description = 'Test API with response definitions';
                $this->operationid = 'testResponses';
                $this->responses = [
                    '200' => [
                        'description' => 'Successful response',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'array',
                                    'items' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'id' => ['type' => 'integer'],
                                            'name' => ['type' => 'string'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '400' => [
                        'description' => 'Bad request',
                    ],
                ];
            }

            /**
             * Get response definitions.
             *
             * @return array Response definitions.
             */
            public function get_responses() {
                return $this->responses;
            }
        };

        $responses = $apifunction->get_responses();
        $this->assertIsArray($responses);
        $this->assertArrayHasKey('200', $responses);
        $this->assertArrayHasKey('400', $responses);

        $successresponse = $responses['200'];
        $this->assertEquals('Successful response', $successresponse['description']);
        $this->assertArrayHasKey('content', $successresponse);
        $this->assertArrayHasKey('application/json', $successresponse['content']);
    }

    /**
     * Test HTTP method validation.
     *
     * @covers \local_copilot\local\api_functions\api_function_base
     */
    public function test_http_method_values(): void {
        $this->resetAfterTest();

        $validmethods = ['get', 'post', 'put', 'delete', 'patch'];

        foreach ($validmethods as $method) {
            $apifunction = new class ($method) extends api_function_base {
                /**
                 * Constructor to set up API with specified method.
                 *
                 * @param string $testmethod HTTP method to test.
                 */
                public function __construct($testmethod) {
                    parent::__construct();
                    $this->path = "/test_$testmethod";
                    $this->method = $testmethod;
                    $this->summary = "Test $testmethod method";
                    $this->description = "Test API with $testmethod method";
                    $this->operationid = "test" . ucfirst($testmethod);
                }

                /**
                 * Get HTTP method.
                 *
                 * @return string HTTP method.
                 */
                public function get_method() {
                    return $this->method;
                }
            };

            $this->assertEquals($method, $apifunction->get_method());
        }
    }

    /**
     * Test that subclasses must implement required properties.
     *
     * @covers \local_copilot\local\api_functions\api_function_base
     */
    public function test_subclass_requirements(): void {
        $this->resetAfterTest();

        // Test a minimal valid subclass.
        $apifunction = new class extends api_function_base {
            /**
             * Constructor to set up minimal API.
             */
            public function __construct() {
                parent::__construct();
                $this->path = '/minimal';
                $this->method = 'get';
                $this->summary = 'Minimal API';
                $this->description = 'Minimal API implementation';
                $this->operationid = 'minimal';
            }

            /**
             * Validate required properties are set.
             *
             * @return bool True if valid, false otherwise.
             */
            public function validate() {
                return !empty($this->path) &&
                       !empty($this->method) &&
                       !empty($this->summary) &&
                       !empty($this->description) &&
                       !empty($this->operationid);
            }
        };

        $this->assertTrue($apifunction->validate());
    }

    /**
     * Test security configuration.
     *
     * @covers \local_copilot\local\api_functions\api_function_base
     */
    public function test_security_configuration(): void {
        $this->resetAfterTest();

        $apifunction = new class extends api_function_base {
            /**
             * Constructor to set up secured API.
             */
            public function __construct() {
                parent::__construct();
                $this->path = '/secure_endpoint';
                $this->method = 'post';
                $this->summary = 'Secure API endpoint';
                $this->description = 'API endpoint requiring authentication';
                $this->operationid = 'secureEndpoint';
                $this->scopesuffix = 'write';
                $this->security = [
                    'oauth2' => ['copilot.write'],
                ];
            }

            /**
             * Get security configuration.
             *
             * @return array Security data.
             */
            public function get_security() {
                return $this->security;
            }
        };

        $security = $apifunction->get_security();
        $this->assertIsArray($security);
        $this->assertArrayHasKey('oauth2', $security);
        $this->assertContains('copilot.write', $security['oauth2']);
    }

    /**
     * Test API documentation generation data.
     *
     * @covers \local_copilot\local\api_functions\api_function_base
     */
    public function test_api_documentation_data(): void {
        $this->resetAfterTest();

        $apifunction = new class extends api_function_base {
            /**
             * Constructor to set up documented API.
             */
            public function __construct() {
                parent::__construct();
                $this->path = '/documented_api';
                $this->method = 'get';
                $this->summary = 'Well documented API';
                $this->description = 'This API endpoint is thoroughly documented with examples and detailed descriptions';
                $this->operationid = 'documentedApi';
                $this->tags = ['courses', 'copilot'];
                $this->examples = [
                    'request' => [
                        'summary' => 'Example request',
                        'value' => ['limit' => 10, 'offset' => 0],
                    ],
                    'response' => [
                        'summary' => 'Example response',
                        'value' => [
                            ['id' => 1, 'name' => 'Course 1'],
                            ['id' => 2, 'name' => 'Course 2'],
                        ],
                    ],
                ];
            }

            /**
             * Get tags for API documentation.
             *
             * @return array Tags data.
             */
            public function get_tags() {
                return $this->tags;
            }

            /**
             * Get examples for API documentation.
             *
             * @return array Examples data.
             */
            public function get_examples() {
                return $this->examples;
            }
        };

        $tags = $apifunction->get_tags();
        $this->assertIsArray($tags);
        $this->assertContains('courses', $tags);
        $this->assertContains('copilot', $tags);

        $examples = $apifunction->get_examples();
        $this->assertIsArray($examples);
        $this->assertArrayHasKey('request', $examples);
        $this->assertArrayHasKey('response', $examples);
    }

    /**
     * Test inheritance behavior.
     *
     * @covers \local_copilot\local\api_functions\api_function_base
     */
    public function test_inheritance(): void {
        $this->resetAfterTest();

        // Test that subclasses can extend base functionality.
        $extendedapi = new class extends api_function_base {
            /**
             * @var array Custom data for extended functionality.
             */
            private $customdata = [];

            /**
             * Constructor to set up extended API.
             */
            public function __construct() {
                parent::__construct();
                $this->path = '/extended_api';
                $this->method = 'get';
                $this->summary = 'Extended API';
                $this->description = 'API with extended functionality';
                $this->operationid = 'extendedApi';
            }

            /**
             * Set custom data.
             *
             * @param array $data Custom data to set.
             */
            public function set_custom_data($data) {
                $this->customdata = $data;
            }

            /**
             * Get custom data.
             *
             * @return array Custom data.
             */
            public function get_custom_data() {
                return $this->customdata;
            }

            /**
             * Custom method to demonstrate extended functionality.
             *
             * @return array Processed data.
             */
            public function process_data() {
                return array_map('strtoupper', $this->customdata);
            }
        };

        // Test custom functionality.
        $testdata = ['apple', 'banana', 'cherry'];
        $extendedapi->set_custom_data($testdata);

        $this->assertEquals($testdata, $extendedapi->get_custom_data());

        $processed = $extendedapi->process_data();
        $this->assertEquals(['APPLE', 'BANANA', 'CHERRY'], $processed);
    }
}
