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
 * Tests for get_course_content external functions.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_copilot;

use local_copilot\external\get_course_content_for_teacher;
use local_copilot\external\get_course_content_for_student;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/local/copilot/tests/base_testcase.php');

/**
 * Tests for get_course_content external functions.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @runTestsInSeparateProcesses
 */
final class get_course_content_test extends base_test {
    /**
     * Test get_course_content_for_teacher function.
     *
     * @covers \local_copilot\external\get_course_content_for_teacher::execute
     */
    public function test_get_course_content_for_teacher(): void {
        // Create some activities in the course.
        $assignment = $this->create_test_assignment();
        $forum = $this->create_test_forum();

        $this->set_user_as_teacher();

        $result = get_course_content_for_teacher::execute($this->course->id);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('fullname', $result);
        $this->assertArrayHasKey('sections', $result);
        $this->assertEquals($this->course->id, $result['id']);
        $this->assertEquals($this->course->fullname, $result['fullname']);

        // Check sections structure.
        $this->assertIsArray($result['sections']);
        $this->assertNotEmpty($result['sections']);

        // Each section should have activities.
        foreach ($result['sections'] as $section) {
            $this->assertArrayHasKey('id', $section);
            $this->assertArrayHasKey('name', $section);
            $this->assertArrayHasKey('activities', $section);
            $this->assertIsArray($section['activities']);
        }
    }

    /**
     * Test get_course_content_for_student function.
     *
     * @covers \local_copilot\external\get_course_content_for_student::execute
     */
    public function test_get_course_content_for_student(): void {
        // Create some activities in the course.
        $assignment = $this->create_test_assignment();
        $forum = $this->create_test_forum();

        $this->set_user_as_student();

        $result = get_course_content_for_student::execute($this->course->id);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('fullname', $result);
        $this->assertArrayHasKey('sections', $result);
        $this->assertEquals($this->course->id, $result['id']);

        // Student should see sections and activities (filtered for their permissions).
        $this->assertIsArray($result['sections']);
    }

    /**
     * Test get_course_content_for_teacher with invalid course ID.
     *
     * @covers \local_copilot\external\get_course_content_for_teacher::execute
     */
    public function test_get_course_content_teacher_invalid_course(): void {
        $this->set_user_as_teacher();

        $this->expectException(\dml_missing_record_exception::class);
        get_course_content_for_teacher::execute(99999);
    }

    /**
     * Test get_course_content_for_student with invalid course ID.
     *
     * @covers \local_copilot\external\get_course_content_for_student::execute
     */
    public function test_get_course_content_student_invalid_course(): void {
        $this->set_user_as_student();

        $this->expectException(\dml_missing_record_exception::class);
        get_course_content_for_student::execute(99999);
    }

    /**
     * Test get_course_content_for_teacher without enrollment.
     *
     * @covers \local_copilot\external\get_course_content_for_teacher::execute
     */
    public function test_get_course_content_teacher_not_enrolled(): void {
        // Create a new course without enrolling the teacher.
        $newcourse = $this->getDataGenerator()->create_course();
        $this->set_user_as_teacher();

        $this->expectException(\required_capability_exception::class);
        get_course_content_for_teacher::execute($newcourse->id);
    }

    /**
     * Test get_course_content_for_student without enrollment.
     *
     * @covers \local_copilot\external\get_course_content_for_student::execute
     */
    public function test_get_course_content_student_not_enrolled(): void {
        // Create a new course without enrolling the student.
        $newcourse = $this->getDataGenerator()->create_course();
        $this->set_user_as_student();

        $this->expectException(\required_capability_exception::class);
        get_course_content_for_student::execute($newcourse->id);
    }

    /**
     * Test parameter validation for teacher function.
     *
     * @covers \local_copilot\external\get_course_content_for_teacher::execute_parameters
     * @covers \local_copilot\external\get_course_content_for_teacher::execute_returns
     */
    public function test_teacher_parameters_and_returns(): void {
        $parameters = get_course_content_for_teacher::execute_parameters();
        $this->assert_external_parameters($parameters, ['courseid']);

        $returns = get_course_content_for_teacher::execute_returns();
        $this->assert_external_returns($returns, 'single');
    }

    /**
     * Test parameter validation for student function.
     *
     * @covers \local_copilot\external\get_course_content_for_student::execute_parameters
     * @covers \local_copilot\external\get_course_content_for_student::execute_returns
     */
    public function test_student_parameters_and_returns(): void {
        $parameters = get_course_content_for_student::execute_parameters();
        $this->assert_external_parameters($parameters, ['courseid']);

        $returns = get_course_content_for_student::execute_returns();
        $this->assert_external_returns($returns, 'single');
    }

    /**
     * Test course content includes activity details.
     *
     * @covers \local_copilot\external\get_course_content_for_teacher::execute
     */
    public function test_course_content_includes_activities(): void {
        // Create activities with specific properties.
        $assignment = $this->create_test_assignment([
            'name' => 'Test Assignment Activity',
            'intro' => 'Assignment description',
        ]);

        $forum = $this->create_test_forum([
            'name' => 'Test Forum Activity',
            'intro' => 'Forum description',
        ]);

        $this->set_user_as_teacher();

        $result = get_course_content_for_teacher::execute($this->course->id);

        // Find activities in sections.
        $foundactivities = [];
        foreach ($result['sections'] as $section) {
            foreach ($section['activities'] as $activity) {
                $foundactivities[$activity['name']] = $activity;
            }
        }

        // Should find our created activities.
        $this->assertArrayHasKey('Test Assignment Activity', $foundactivities);
        $this->assertArrayHasKey('Test Forum Activity', $foundactivities);

        // Check activity structure.
        foreach ($foundactivities as $activity) {
            $this->assertArrayHasKey('id', $activity);
            $this->assertArrayHasKey('name', $activity);
            $this->assertArrayHasKey('modname', $activity);
            $this->assertArrayHasKey('url', $activity);
        }
    }

    /**
     * Test course content with hidden activities for student.
     *
     * @covers \local_copilot\external\get_course_content_for_student::execute
     */
    public function test_course_content_hidden_activities_student(): void {
        global $DB;

        // Create an assignment and hide it.
        $assignment = $this->create_test_assignment();

        // Hide the course module.
        $cm = get_coursemodule_from_instance('assign', $assignment->id);
        $DB->update_record('course_modules', ['id' => $cm->id, 'visible' => 0]);

        $this->set_user_as_student();

        $result = get_course_content_for_student::execute($this->course->id);

        // Hidden activities should not be visible to students.
        $visibleactivities = [];
        foreach ($result['sections'] as $section) {
            foreach ($section['activities'] as $activity) {
                if ($activity['id'] == $cm->id) {
                    $visibleactivities[] = $activity;
                }
            }
        }

        // Student should not see hidden activities.
        $this->assertEmpty($visibleactivities);
    }

    /**
     * Test course content with completion tracking.
     *
     * @covers \local_copilot\external\get_course_content_for_student::execute
     */
    public function test_course_content_with_completion(): void {
        global $CFG;

        if (!$CFG->enablecompletion) {
            $this->markTestSkipped('Completion tracking not enabled');
        }

        // Enable completion for the course.
        $this->course->enablecompletion = 1;
        $DB = \database::get();
        $DB->update_record('course', $this->course);

        // Create assignment with completion tracking.
        $assignment = $this->create_test_assignment([
            'completion' => COMPLETION_TRACKING_MANUAL,
        ]);

        $this->set_user_as_student();

        $result = get_course_content_for_student::execute($this->course->id);

        // Should include completion information in activities.
        $this->assertIsArray($result['sections']);
    }
}
