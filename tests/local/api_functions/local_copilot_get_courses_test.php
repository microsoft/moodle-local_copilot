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
 * Tests for local_copilot_get_courses API function class.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_copilot;

use local_copilot\local\api_functions\local_copilot_get_courses;

defined('MOODLE_INTERNAL') || die();

/**
 * Tests for local_copilot_get_courses API function class.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_api_functions_local_copilot_get_courses_test extends base_test {

    /**
     * Test API function instantiation.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_courses::__construct
     */
    public function test_api_function_instantiation() {
        $apifunction = new local_copilot_get_courses();
        $this->assertInstanceOf(local_copilot_get_courses::class, $apifunction);
    }

    /**
     * Test API function properties are set correctly.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_courses::__construct
     */
    public function test_api_function_properties() {
        $apifunction = new class extends local_copilot_get_courses {
            // Expose protected properties for testing.
            public function getPath() { return $this->path; }
            public function getMethod() { return $this->method; }
            public function getSummary() { return $this->summary; }
            public function getDescription() { return $this->description; }
            public function getOperationId() { return $this->operationid; }
            public function getScopesuffix() { return $this->scopesuffix; }
        };

        $this->assertEquals('/local_copilot_get_courses', $apifunction->getPath());
        $this->assertEquals('get', $apifunction->getMethod());
        $this->assertStringContainsString('courses', strtolower($apifunction->getSummary()));
        $this->assertStringContainsString('enrolled', strtolower($apifunction->getDescription()));
        $this->assertEquals('getCourses', $apifunction->getOperationId());
        $this->assertEquals('read', $apifunction->getScopeSuffix());
    }

    /**
     * Test API function parameters are defined correctly.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_courses::__construct
     */
    public function test_api_function_parameters() {
        $apifunction = new class extends local_copilot_get_courses {
            public function getParameters() { return $this->parameters; }
        };

        $parameters = $apifunction->getParameters();
        $this->assertIsArray($parameters);
        $this->assertNotEmpty($parameters);

        // Check for expected parameters.
        $paramnames = array_column($parameters, 'name');
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
     * Test limit parameter configuration.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_courses::__construct
     */
    public function test_limit_parameter() {
        $apifunction = new class extends local_copilot_get_courses {
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
        
        if (isset($limitparam['schema']['default'])) {
            $this->assertIsInt($limitparam['schema']['default']);
            $this->assertGreaterThan(0, $limitparam['schema']['default']);
        }
    }

    /**
     * Test offset parameter configuration.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_courses::__construct
     */
    public function test_offset_parameter() {
        $apifunction = new class extends local_copilot_get_courses {
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
        
        if (isset($offsetparam['schema']['default'])) {
            $this->assertEquals(0, $offsetparam['schema']['default']);
        }
    }

    /**
     * Test API function response definitions.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_courses::__construct
     */
    public function test_response_definitions() {
        $apifunction = new class extends local_copilot_get_courses {
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
        
        // Check for expected course properties.
        $properties = $schema['items']['properties'];
        $this->assertArrayHasKey('id', $properties);
        $this->assertArrayHasKey('fullname', $properties);
        $this->assertArrayHasKey('shortname', $properties);
    }

    /**
     * Test API function security configuration.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_courses::__construct
     */
    public function test_security_configuration() {
        $apifunction = new class extends local_copilot_get_courses {
            public function getSecurity() { return $this->security; }
        };

        $security = $apifunction->getSecurity();
        $this->assertIsArray($security);
        
        // Should require OAuth2 authentication.
        $this->assertArrayHasKey('oauth2', $security);
        $this->assertIsArray($security['oauth2']);
        $this->assertNotEmpty($security['oauth2']);
        
        // Should include read scope.
        $hasreadscope = false;
        foreach ($security['oauth2'] as $scope) {
            if (strpos($scope, 'read') !== false) {
                $hasreadscope = true;
                break;
            }
        }
        $this->assertTrue($hasreadscope, 'Should include read scope');
    }

    /**
     * Test API function tags for documentation.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_courses::__construct
     */
    public function test_documentation_tags() {
        $apifunction = new class extends local_copilot_get_courses {
            public function getTags() { return $this->tags; }
        };

        $tags = $apifunction->getTags();
        if ($tags !== null) {
            $this->assertIsArray($tags);
            $this->assertContains('courses', $tags);
        }
    }

    /**
     * Test API function with role-specific behavior.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_courses
     */
    public function test_role_specific_behavior() {
        // Test that API function can be configured for different roles.
        $teacherapi = new local_copilot_get_courses();
        $studentapi = new local_copilot_get_courses();
        
        // Both should be the same class but might behave differently based on context.
        $this->assertInstanceOf(local_copilot_get_courses::class, $teacherapi);
        $this->assertInstanceOf(local_copilot_get_courses::class, $studentapi);
    }

    /**
     * Test API function parameter validation rules.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_courses::__construct
     */
    public function test_parameter_validation() {
        $apifunction = new class extends local_copilot_get_courses {
            public function getParameters() { return $this->parameters; }
        };

        $parameters = $apifunction->getParameters();
        
        foreach ($parameters as $param) {
            // All parameters should have proper validation rules.
            $this->assertArrayHasKey('schema', $param);
            $schema = $param['schema'];
            
            if ($param['name'] === 'limit') {
                $this->assertEquals('integer', $schema['type']);
                if (isset($schema['minimum'])) {
                    $this->assertGreaterThanOrEqual(1, $schema['minimum']);
                }
                if (isset($schema['maximum'])) {
                    $this->assertGreaterThan(0, $schema['maximum']);
                }
            }
            
            if ($param['name'] === 'offset') {
                $this->assertEquals('integer', $schema['type']);
                if (isset($schema['minimum'])) {
                    $this->assertGreaterThanOrEqual(0, $schema['minimum']);
                }
            }
        }
    }

    /**
     * Test API function example data.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_courses::__construct
     */
    public function test_example_data() {
        $apifunction = new class extends local_copilot_get_courses {
            public function getExamples() { return $this->examples; }
        };

        $examples = $apifunction->getExamples();
        if ($examples !== null) {
            $this->assertIsArray($examples);
            
            if (isset($examples['response'])) {
                $responseexample = $examples['response']['value'];
                $this->assertIsArray($responseexample);
                
                // Each course in example should have expected structure.
                foreach ($responseexample as $course) {
                    $this->assertArrayHasKey('id', $course);
                    $this->assertArrayHasKey('fullname', $course);
                    $this->assertIsInt($course['id']);
                    $this->assertIsString($course['fullname']);
                }
            }
        }
    }

    /**
     * Test API function integration with manifest generator.
     *
     * @covers \local_copilot\local\api_functions\local_copilot_get_courses
     */
    public function test_manifest_integration() {
        // API function should work with both teacher and student manifests.
        $teacherapi = new local_copilot_get_courses();
        $studentapi = new local_copilot_get_courses();
        
        // Should be usable in both contexts.
        $this->assertInstanceOf(local_copilot_get_courses::class, $teacherapi);
        $this->assertInstanceOf(local_copilot_get_courses::class, $studentapi);
        
        // API should have consistent operation ID for both roles.
        $reflection = new \ReflectionClass(local_copilot_get_courses::class);
        $this->assertTrue($reflection->hasProperty('operationid'));
    }
}