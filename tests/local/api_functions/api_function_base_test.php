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

use local_copilot\local\api_functions\api_function_base;

defined('MOODLE_INTERNAL') || die();

/**
 * Tests for api_function_base class.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_api_functions_api_function_base_test extends \advanced_testcase {

    /**
     * Test that api_function_base can be instantiated.
     *
     * @covers \local_copilot\local\api_functions\api_function_base::__construct
     */
    public function test_base_class_instantiation() {
        $this->resetAfterTest();

        // Create a concrete implementation for testing.
        $apifunction = new class extends api_function_base {
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
    public function test_base_class_properties() {
        $this->resetAfterTest();

        $apifunction = new class extends api_function_base {
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
            public function getPath() { return $this->path; }
            public function getMethod() { return $this->method; }
            public function getSummary() { return $this->summary; }
            public function getDescription() { return $this->description; }
            public function getOperationId() { return $this->operationid; }
            public function getScopeSuffix() { return $this->scopesuffix; }
        };

        $this->assertEquals('/test_endpoint', $apifunction->getPath());
        $this->assertEquals('post', $apifunction->getMethod());
        $this->assertEquals('Test API endpoint', $apifunction->getSummary());
        $this->assertEquals('This is a test API endpoint for unit testing', $apifunction->getDescription());
        $this->assertEquals('testEndpoint', $apifunction->getOperationId());
        $this->assertEquals('write', $apifunction->getScopeSuffix());
    }

    /**
     * Test base class with parameters.
     *
     * @covers \local_copilot\local\api_functions\api_function_base
     */
    public function test_base_class_with_parameters() {
        $this->resetAfterTest();

        $apifunction = new class extends api_function_base {
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

            public function getParameters() { return $this->parameters; }
        };

        $parameters = $apifunction->getParameters();
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
    public function test_base_class_with_responses() {
        $this->resetAfterTest();

        $apifunction = new class extends api_function_base {
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

            public function getResponses() { return $this->responses; }
        };

        $responses = $apifunction->getResponses();
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
    public function test_http_method_values() {
        $this->resetAfterTest();

        $validmethods = ['get', 'post', 'put', 'delete', 'patch'];

        foreach ($validmethods as $method) {
            $apifunction = new class($method) extends api_function_base {
                public function __construct($testmethod) {
                    parent::__construct();
                    $this->path = "/test_$testmethod";
                    $this->method = $testmethod;
                    $this->summary = "Test $testmethod method";
                    $this->description = "Test API with $testmethod method";
                    $this->operationid = "test" . ucfirst($testmethod);
                }

                public function getMethod() { return $this->method; }
            };

            $this->assertEquals($method, $apifunction->getMethod());
        }
    }

    /**
     * Test that subclasses must implement required properties.
     *
     * @covers \local_copilot\local\api_functions\api_function_base
     */
    public function test_subclass_requirements() {
        $this->resetAfterTest();

        // Test a minimal valid subclass.
        $apifunction = new class extends api_function_base {
            public function __construct() {
                parent::__construct();
                $this->path = '/minimal';
                $this->method = 'get';
                $this->summary = 'Minimal API';
                $this->description = 'Minimal API implementation';
                $this->operationid = 'minimal';
            }

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
    public function test_security_configuration() {
        $this->resetAfterTest();

        $apifunction = new class extends api_function_base {
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

            public function getSecurity() { return $this->security; }
        };

        $security = $apifunction->getSecurity();
        $this->assertIsArray($security);
        $this->assertArrayHasKey('oauth2', $security);
        $this->assertContains('copilot.write', $security['oauth2']);
    }

    /**
     * Test API documentation generation data.
     *
     * @covers \local_copilot\local\api_functions\api_function_base
     */
    public function test_api_documentation_data() {
        $this->resetAfterTest();

        $apifunction = new class extends api_function_base {
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

            public function getTags() { return $this->tags; }
            public function getExamples() { return $this->examples; }
        };

        $tags = $apifunction->getTags();
        $this->assertIsArray($tags);
        $this->assertContains('courses', $tags);
        $this->assertContains('copilot', $tags);

        $examples = $apifunction->getExamples();
        $this->assertIsArray($examples);
        $this->assertArrayHasKey('request', $examples);
        $this->assertArrayHasKey('response', $examples);
    }

    /**
     * Test inheritance behavior.
     *
     * @covers \local_copilot\local\api_functions\api_function_base
     */
    public function test_inheritance() {
        $this->resetAfterTest();

        // Test that subclasses can extend base functionality.
        $extendedapi = new class extends api_function_base {
            private $customdata = [];

            public function __construct() {
                parent::__construct();
                $this->path = '/extended_api';
                $this->method = 'get';
                $this->summary = 'Extended API';
                $this->description = 'API with extended functionality';
                $this->operationid = 'extendedApi';
            }

            public function setCustomData($data) {
                $this->customdata = $data;
            }

            public function getCustomData() {
                return $this->customdata;
            }

            public function processData() {
                return array_map('strtoupper', $this->customdata);
            }
        };

        // Test custom functionality.
        $testdata = ['apple', 'banana', 'cherry'];
        $extendedapi->setCustomData($testdata);
        
        $this->assertEquals($testdata, $extendedapi->getCustomData());
        
        $processed = $extendedapi->processData();
        $this->assertEquals(['APPLE', 'BANANA', 'CHERRY'], $processed);
    }
}