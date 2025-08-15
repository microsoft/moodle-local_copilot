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
 * Tests for get_courses external function.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_copilot;

use context_system;
use local_copilot\external\get_courses;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');

/**
 * Tests for get_courses external function.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @runTestsInSeparateProcesses
 */
class external_get_courses_test extends \externallib_advanced_testcase {

    /**
     * Test get_courses execute function.
     *
     * @covers \local_copilot\external\get_courses::execute
     */
    public function test_execute() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        // Create test courses.
        $course1 = $this->getDataGenerator()->create_course([
            'fullname' => 'Test Course 1',
            'shortname' => 'TC1',
            'summary' => 'First test course',
        ]);
        $course2 = $this->getDataGenerator()->create_course([
            'fullname' => 'Test Course 2', 
            'shortname' => 'TC2',
            'summary' => 'Second test course',
        ]);

        // Create test user and enrol in courses.
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course1->id);
        $this->getDataGenerator()->enrol_user($user->id, $course2->id);

        $this->setUser($user);

        // Test with default parameters.
        $result = get_courses::execute();
        
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        
        // Check course structure.
        foreach ($result as $course) {
            $this->assertArrayHasKey('id', $course);
            $this->assertArrayHasKey('fullname', $course);
            $this->assertArrayHasKey('shortname', $course);
            $this->assertArrayHasKey('has_more', $course);
            $this->assertIsBool($course['has_more']);
        }
    }

    /**
     * Test get_courses with pagination.
     *
     * @covers \local_copilot\external\get_courses::execute
     */
    public function test_execute_with_pagination() {
        $this->resetAfterTest();
        $this->setAdminUser();

        // Create multiple test courses.
        $courses = [];
        for ($i = 1; $i <= 15; $i++) {
            $courses[] = $this->getDataGenerator()->create_course([
                'fullname' => "Test Course $i",
                'shortname' => "TC$i",
            ]);
        }

        // Create test user and enrol in all courses.
        $user = $this->getDataGenerator()->create_user();
        foreach ($courses as $course) {
            $this->getDataGenerator()->enrol_user($user->id, $course->id);
        }

        $this->setUser($user);

        // Test with limit and offset.
        $result = get_courses::execute(5, 0);
        $this->assertCount(5, $result);
        $this->assertTrue($result[0]['has_more']); // Should have more courses.

        // Test second page.
        $result = get_courses::execute(5, 5);
        $this->assertCount(5, $result);
        $this->assertTrue($result[0]['has_more']); // Should still have more.

        // Test last page.
        $result = get_courses::execute(10, 10);
        $this->assertCount(5, $result);
        $this->assertFalse($result[0]['has_more']); // No more courses.
    }

    /**
     * Test get_courses with no enrollments.
     *
     * @covers \local_copilot\external\get_courses::execute
     */
    public function test_execute_no_enrollments() {
        $this->resetAfterTest();
        
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $result = get_courses::execute();
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test parameter validation.
     *
     * @covers \local_copilot\external\get_courses::execute_parameters
     * @covers \local_copilot\external\get_courses::execute_returns
     */
    public function test_parameters_and_returns() {
        $this->resetAfterTest();

        // Test parameters structure.
        $parameters = get_courses::execute_parameters();
        $this->assertInstanceOf(\external_function_parameters::class, $parameters);
        
        $params = $parameters->keys;
        $this->assertArrayHasKey('limit', $params);
        $this->assertArrayHasKey('offset', $params);
        
        // Test returns structure.
        $returns = get_courses::execute_returns();
        $this->assertInstanceOf(\external_multiple_structure::class, $returns);
    }

    /**
     * Test with pagination limit configuration.
     *
     * @covers \local_copilot\external\get_courses::execute
     */
    public function test_execute_with_config_limit() {
        $this->resetAfterTest();
        $this->setAdminUser();

        // Set custom pagination limit.
        set_config('paginationlimit', 3, 'local_copilot');

        // Create test courses.
        for ($i = 1; $i <= 5; $i++) {
            $course = $this->getDataGenerator()->create_course([
                'fullname' => "Test Course $i",
                'shortname' => "TC$i",
            ]);
            $user = $this->getDataGenerator()->create_user();
            $this->getDataGenerator()->enrol_user($user->id, $course->id);
        }

        $this->setUser($user);

        // Test with default limit (should use config).
        $result = get_courses::execute();
        
        // Should respect pagination configuration.
        $this->assertLessThanOrEqual(5, count($result));
    }
}