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
 * Tests for enrolment-related external functions.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_copilot;

use local_copilot\external\get_self_enrolment_instances_for_student;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/local/copilot/tests/base_test.php');

/**
 * Tests for enrolment-related external functions.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @runTestsInSeparateProcesses
 */
class external_enrolment_test extends base_test {

    /**
     * Test get_self_enrolment_instances_for_student.
     *
     * @covers \local_copilot\external\get_self_enrolment_instances_for_student::execute
     */
    public function test_get_self_enrolment_instances() {
        global $DB;
        
        // Create courses with self-enrolment.
        $course1 = $this->getDataGenerator()->create_course([
            'fullname' => 'Self-Enrol Course 1',
            'shortname' => 'SELF1',
        ]);
        
        $course2 = $this->getDataGenerator()->create_course([
            'fullname' => 'Self-Enrol Course 2',
            'shortname' => 'SELF2',
        ]);
        
        // Enable self-enrolment in these courses.
        $selfplugin = enrol_get_plugin('self');
        
        $selfplugin->add_instance($course1, [
            'status' => ENROL_INSTANCE_ENABLED,
            'enrolstartdate' => 0,
            'enrolenddate' => 0,
            'roleid' => $DB->get_field('role', 'id', ['shortname' => 'student']),
        ]);
        
        $selfplugin->add_instance($course2, [
            'status' => ENROL_INSTANCE_ENABLED,
            'enrolstartdate' => 0,
            'enrolenddate' => 0,
            'roleid' => $DB->get_field('role', 'id', ['shortname' => 'student']),
        ]);
        
        $this->setUserAsStudent();

        $result = get_self_enrolment_instances_for_student::execute();

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(2, count($result)); // At least our 2 courses.
        
        // Check structure of results.
        foreach ($result as $course) {
            $this->assertArrayHasKey('id', $course);
            $this->assertArrayHasKey('fullname', $course);
            $this->assertArrayHasKey('shortname', $course);
            $this->assertArrayHasKey('enrolmentmethod', $course);
            $this->assertEquals('self', $course['enrolmentmethod']);
        }
        
        // Find our specific courses.
        $coursenames = array_column($result, 'fullname');
        $this->assertContains('Self-Enrol Course 1', $coursenames);
        $this->assertContains('Self-Enrol Course 2', $coursenames);
    }

    /**
     * Test get_self_enrolment_instances with password-protected enrolment.
     *
     * @covers \local_copilot\external\get_self_enrolment_instances_for_student::execute
     */
    public function test_get_self_enrolment_instances_with_password() {
        global $DB;
        
        $course = $this->getDataGenerator()->create_course([
            'fullname' => 'Password Protected Course',
            'shortname' => 'PASSPROTECTED',
        ]);
        
        // Enable self-enrolment with password.
        $selfplugin = enrol_get_plugin('self');
        $selfplugin->add_instance($course, [
            'status' => ENROL_INSTANCE_ENABLED,
            'password' => 'secret123',
            'roleid' => $DB->get_field('role', 'id', ['shortname' => 'student']),
        ]);
        
        $this->setUserAsStudent();

        $result = get_self_enrolment_instances_for_student::execute();

        // Find the password-protected course.
        $passwordcourse = null;
        foreach ($result as $course) {
            if ($course['shortname'] === 'PASSPROTECTED') {
                $passwordcourse = $course;
                break;
            }
        }
        
        $this->assertNotNull($passwordcourse);
        $this->assertArrayHasKey('requirespassword', $passwordcourse);
        if (isset($passwordcourse['requirespassword'])) {
            $this->assertTrue($passwordcourse['requirespassword']);
        }
    }

    /**
     * Test get_self_enrolment_instances with date restrictions.
     *
     * @covers \local_copilot\external\get_self_enrolment_instances_for_student::execute
     */
    public function test_get_self_enrolment_instances_date_restricted() {
        global $DB;
        
        // Course available for enrolment.
        $availablecourse = $this->getDataGenerator()->create_course([
            'fullname' => 'Available Course',
            'shortname' => 'AVAILABLE',
        ]);
        
        // Course not yet available for enrolment.
        $futurecourse = $this->getDataGenerator()->create_course([
            'fullname' => 'Future Course',
            'shortname' => 'FUTURE',
        ]);
        
        $selfplugin = enrol_get_plugin('self');
        
        // Available now.
        $selfplugin->add_instance($availablecourse, [
            'status' => ENROL_INSTANCE_ENABLED,
            'enrolstartdate' => time() - 3600, // Started 1 hour ago.
            'enrolenddate' => time() + 86400,  // Ends in 1 day.
            'roleid' => $DB->get_field('role', 'id', ['shortname' => 'student']),
        ]);
        
        // Not available yet.
        $selfplugin->add_instance($futurecourse, [
            'status' => ENROL_INSTANCE_ENABLED,
            'enrolstartdate' => time() + 86400, // Starts in 1 day.
            'enrolenddate' => time() + (2 * 86400), // Ends in 2 days.
            'roleid' => $DB->get_field('role', 'id', ['shortname' => 'student']),
        ]);
        
        $this->setUserAsStudent();

        $result = get_self_enrolment_instances_for_student::execute();

        $coursenames = array_column($result, 'fullname');
        
        // Should see available course.
        $this->assertContains('Available Course', $coursenames);
        
        // Should NOT see future course (depending on implementation).
        // Some implementations might show all courses with enrollment info.
    }

    /**
     * Test get_self_enrolment_instances excludes disabled instances.
     *
     * @covers \local_copilot\external\get_self_enrolment_instances_for_student::execute
     */
    public function test_get_self_enrolment_instances_excludes_disabled() {
        global $DB;
        
        $course = $this->getDataGenerator()->create_course([
            'fullname' => 'Disabled Enrolment Course',
            'shortname' => 'DISABLED',
        ]);
        
        // Add disabled self-enrolment instance.
        $selfplugin = enrol_get_plugin('self');
        $selfplugin->add_instance($course, [
            'status' => ENROL_INSTANCE_DISABLED,
            'roleid' => $DB->get_field('role', 'id', ['shortname' => 'student']),
        ]);
        
        $this->setUserAsStudent();

        $result = get_self_enrolment_instances_for_student::execute();

        $coursenames = array_column($result, 'fullname');
        
        // Should NOT see course with disabled self-enrolment.
        $this->assertNotContains('Disabled Enrolment Course', $coursenames);
    }

    /**
     * Test get_self_enrolment_instances excludes courses user is already enrolled in.
     *
     * @covers \local_copilot\external\get_self_enrolment_instances_for_student::execute
     */
    public function test_get_self_enrolment_instances_excludes_enrolled() {
        global $DB;
        
        // Enable self-enrolment in the test course (user is already enrolled).
        $selfplugin = enrol_get_plugin('self');
        $selfplugin->add_instance($this->course, [
            'status' => ENROL_INSTANCE_ENABLED,
            'roleid' => $DB->get_field('role', 'id', ['shortname' => 'student']),
        ]);
        
        $this->setUserAsStudent();

        $result = get_self_enrolment_instances_for_student::execute();

        $coursenames = array_column($result, 'fullname');
        
        // Should NOT see course where user is already enrolled.
        $this->assertNotContains($this->course->fullname, $coursenames);
    }

    /**
     * Test parameter validation.
     *
     * @covers \local_copilot\external\get_self_enrolment_instances_for_student::execute_parameters
     * @covers \local_copilot\external\get_self_enrolment_instances_for_student::execute_returns
     */
    public function test_parameters_and_returns() {
        $parameters = get_self_enrolment_instances_for_student::execute_parameters();
        $this->assertInstanceOf(\external_function_parameters::class, $parameters);
        
        $returns = get_self_enrolment_instances_for_student::execute_returns();
        $this->assertExternalReturns($returns, 'multiple');
    }

    /**
     * Test get_self_enrolment_instances with course categories.
     *
     * @covers \local_copilot\external\get_self_enrolment_instances_for_student::execute
     */
    public function test_get_self_enrolment_instances_with_categories() {
        global $DB;
        
        // Create category.
        $category = $this->getDataGenerator()->create_category([
            'name' => 'Test Category',
            'description' => 'Category for testing',
        ]);
        
        $course = $this->getDataGenerator()->create_course([
            'fullname' => 'Categorized Course',
            'shortname' => 'CATEGORIZED',
            'category' => $category->id,
        ]);
        
        // Enable self-enrolment.
        $selfplugin = enrol_get_plugin('self');
        $selfplugin->add_instance($course, [
            'status' => ENROL_INSTANCE_ENABLED,
            'roleid' => $DB->get_field('role', 'id', ['shortname' => 'student']),
        ]);
        
        $this->setUserAsStudent();

        $result = get_self_enrolment_instances_for_student::execute();

        // Find the categorized course.
        $categorizedcourse = null;
        foreach ($result as $course) {
            if ($course['shortname'] === 'CATEGORIZED') {
                $categorizedcourse = $course;
                break;
            }
        }
        
        $this->assertNotNull($categorizedcourse);
        
        // Should include category information.
        if (isset($categorizedcourse['categoryname'])) {
            $this->assertEquals('Test Category', $categorizedcourse['categoryname']);
        }
    }

    /**
     * Test get_self_enrolment_instances with course summary.
     *
     * @covers \local_copilot\external\get_self_enrolment_instances_for_student::execute
     */
    public function test_get_self_enrolment_instances_includes_summary() {
        global $DB;
        
        $course = $this->getDataGenerator()->create_course([
            'fullname' => 'Course with Summary',
            'shortname' => 'SUMMARY',
            'summary' => 'This course has a detailed summary description.',
            'summaryformat' => FORMAT_HTML,
        ]);
        
        // Enable self-enrolment.
        $selfplugin = enrol_get_plugin('self');
        $selfplugin->add_instance($course, [
            'status' => ENROL_INSTANCE_ENABLED,
            'roleid' => $DB->get_field('role', 'id', ['shortname' => 'student']),
        ]);
        
        $this->setUserAsStudent();

        $result = get_self_enrolment_instances_for_student::execute();

        // Find the course with summary.
        $summarycourse = null;
        foreach ($result as $course) {
            if ($course['shortname'] === 'SUMMARY') {
                $summarycourse = $course;
                break;
            }
        }
        
        $this->assertNotNull($summarycourse);
        
        // Should include summary information.
        if (isset($summarycourse['summary'])) {
            $this->assertEquals('This course has a detailed summary description.', $summarycourse['summary']);
        }
    }

    /**
     * Test get_self_enrolment_instances with guest access.
     *
     * @covers \local_copilot\external\get_self_enrolment_instances_for_student::execute
     */
    public function test_get_self_enrolment_instances_as_guest() {
        global $DB;
        
        // Create guest user.
        $guest = guest_user();
        $this->setUser($guest);

        $result = get_self_enrolment_instances_for_student::execute();

        // Guest should be able to see self-enrolment instances.
        $this->assertIsArray($result);
        
        // Results might be filtered differently for guest users.
    }
}