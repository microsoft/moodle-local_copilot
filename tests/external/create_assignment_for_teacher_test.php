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
 * Tests for create_assignment_for_teacher external function.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_copilot;

use context_course;
use local_copilot\external\create_assignment_for_teacher;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');

/**
 * Tests for create_assignment_for_teacher external function.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @runTestsInSeparateProcesses
 */
final class create_assignment_for_teacher_test extends \externallib_advanced_testcase {
    /**
     * Test create assignment with valid parameters.
     *
     * @covers \local_copilot\external\create_assignment_for_teacher::execute
     */
    public function test_create_assignment_success(): void {
        global $DB;
        $this->resetAfterTest();

        // Create test course and user.
        $course = $this->getDataGenerator()->create_course();
        $teacher = $this->getDataGenerator()->create_user();

        // Enrol teacher with editing capabilities.
        $teacherrole = $DB->get_record('role', ['shortname' => 'editingteacher']);
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, $teacherrole->id);

        $this->setUser($teacher);

        $result = create_assignment_for_teacher::execute(
            $course->id,
            'Test Assignment',
            0,
            'This is a test assignment',
            null,
            null,
            null
        );

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('error', $result);
        $this->assertTrue($result['success']);
        $this->assertGreaterThan(0, $result['id']);
        $this->assertEquals('', $result['error']);

        // Verify assignment was created in database.
        $assignment = $DB->get_record('assign', ['id' => $result['id']]);
        $this->assertNotFalse($assignment);
        $this->assertEquals('Test Assignment', $assignment->name);
        $this->assertEquals('This is a test assignment', $assignment->intro);
        $this->assertEquals($course->id, $assignment->course);
    }

    /**
     * Test create assignment without proper capabilities.
     *
     * @covers \local_copilot\external\create_assignment_for_teacher::execute
     */
    public function test_create_assignment_no_capability(): void {
        $this->resetAfterTest();

        // Create test course and user without editing capabilities.
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');

        $this->setUser($user);

        $this->expectOutputRegex('/403/');

        create_assignment_for_teacher::execute(
            $course->id,
            'Test Assignment',
            0,
            'This is a test assignment',
            null,
            null,
            null
        );
    }

    /**
     * Test create assignment with invalid course.
     *
     * @covers \local_copilot\external\create_assignment_for_teacher::execute
     */
    public function test_create_assignment_invalid_course(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $this->expectOutputRegex('/404/');

        create_assignment_for_teacher::execute(
            99999, // Non-existent course.
            'Test Assignment',
            0,
            'This is a test assignment',
            null,
            null,
            null
        );
    }

    /**
     * Test parameter validation.
     *
     * @covers \local_copilot\external\create_assignment_for_teacher::execute_parameters
     * @covers \local_copilot\external\create_assignment_for_teacher::execute_returns
     */
    public function test_parameters_and_returns(): void {
        $this->resetAfterTest();

        // Test parameters structure.
        $parameters = create_assignment_for_teacher::execute_parameters();
        $this->assertInstanceOf(\external_function_parameters::class, $parameters);

        $params = $parameters->keys;
        $this->assertArrayHasKey('course_id', $params);
        $this->assertArrayHasKey('assignment_name', $params);
        $this->assertArrayHasKey('section_id', $params);
        $this->assertArrayHasKey('assignment_description', $params);
        $this->assertArrayHasKey('allowsubmissionsfromdate', $params);
        $this->assertArrayHasKey('due_date', $params);
        $this->assertArrayHasKey('assignment_instructions', $params);

        // Test returns structure.
        $returns = create_assignment_for_teacher::execute_returns();
        $this->assertInstanceOf(\external_single_structure::class, $returns);

        $returnkeys = $returns->keys;
        $this->assertArrayHasKey('success', $returnkeys);
        $this->assertArrayHasKey('id', $returnkeys);
        $this->assertArrayHasKey('error', $returnkeys);
    }

    /**
     * Test create assignment with optional parameters.
     *
     * @covers \local_copilot\external\create_assignment_for_teacher::execute
     */
    public function test_create_assignment_with_options(): void {
        global $DB;
        $this->resetAfterTest();

        // Create test course with multiple sections and unique shortname.
        $course = $this->getDataGenerator()->create_course([
            'numsections' => 3,
            'shortname' => 'TESTASSIGN' . time() . rand(1000, 9999),
        ]);
        $teacher = $this->getDataGenerator()->create_user();

        // Enrol teacher with editing capabilities.
        $teacherrole = $DB->get_record('role', ['shortname' => 'editingteacher']);
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, $teacherrole->id);

        $this->setUser($teacher);

        // Use date format MM/DD/YYYY as per the API spec.
        $allowfromdate = date('m/d/Y', time());
        $duedate = date('m/d/Y', time() + (7 * 24 * 60 * 60)); // Due in 1 week.

        $result = create_assignment_for_teacher::execute(
            $course->id,
            'Advanced Test Assignment',
            1,
            'This is an advanced test assignment with options',
            $allowfromdate,
            $duedate,
            'Follow these instructions carefully'
        );

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertGreaterThan(0, $result['id']);

        // Verify assignment was created with correct options.
        $assignment = $DB->get_record('assign', ['id' => $result['id']]);
        $this->assertNotFalse($assignment);
        $this->assertEquals('Advanced Test Assignment', $assignment->name);
        $this->assertEquals('This is an advanced test assignment with options', $assignment->intro);
        $this->assertGreaterThan(0, $assignment->duedate);
        $this->assertGreaterThan(0, $assignment->allowsubmissionsfromdate);
        $this->assertEquals('Follow these instructions carefully', $assignment->activity);
    }

    /**
     * Test create assignment with invalid date format.
     *
     * @covers \local_copilot\external\create_assignment_for_teacher::execute
     */
    public function test_create_assignment_invalid_date(): void {
        global $DB;
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $teacher = $this->getDataGenerator()->create_user();

        $teacherrole = $DB->get_record('role', ['shortname' => 'editingteacher']);
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, $teacherrole->id);

        $this->setUser($teacher);

        $result = create_assignment_for_teacher::execute(
            $course->id,
            'Test Assignment',
            0,
            'Test description',
            'invalid-date-format',
            null,
            null
        );

        $this->assertIsArray($result);
        $this->assertFalse($result['success']);
        $this->assertEquals(0, $result['id']);
        $this->assertStringContainsString('Invalid date format', $result['error']);
    }
}
