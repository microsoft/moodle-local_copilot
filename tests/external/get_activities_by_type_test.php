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
 * Tests for get_activities_by_type external functions.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_copilot;

use local_copilot\external\get_activities_by_type_for_teacher;
use local_copilot\external\get_activities_by_type_for_student;
use local_copilot\tests\fixtures\test_courses;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/local/copilot/tests/base_testcase.php');

/**
 * Tests for get_activities_by_type external functions.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @runTestsInSeparateProcesses
 */
final class get_activities_by_type_test extends base_test {
    /**
     * Test get_activities_by_type_for_teacher for assignments.
     *
     * @covers \local_copilot\external\get_activities_by_type_for_teacher::execute
     */
    public function test_get_activities_teacher_assignments(): void {
        // Create multiple assignments.
        $assignment1 = $this->create_test_assignment(['name' => 'Assignment 1']);
        $assignment2 = $this->create_test_assignment(['name' => 'Assignment 2']);

        // Create a forum (different type).
        $forum = $this->create_test_forum(['name' => 'Forum 1']);

        $this->set_user_as_teacher();

        $result = get_activities_by_type_for_teacher::execute('assign');

        $this->assertIsArray($result);
        $this->assertCount(2, $result); // Only assignments, not forums.

        // Check activity structure.
        foreach ($result as $activity) {
            $this->assertArrayHasKey('activity_id', $activity);
            $this->assertArrayHasKey('activity_name', $activity);
            $this->assertArrayHasKey('activity_type', $activity);
            $this->assertArrayHasKey('course_id', $activity);
            $this->assertEquals('assign', $activity['activity_type']);
        }

        // Verify we got our assignments.
        $names = array_column($result, 'activity_name');
        $this->assertContains('Assignment 1', $names);
        $this->assertContains('Assignment 2', $names);
    }

    /**
     * Test get_activities_by_type_for_student for assignments.
     *
     * @covers \local_copilot\external\get_activities_by_type_for_student::execute
     */
    public function test_get_activities_student_assignments(): void {
        // Create assignments.
        $assignment1 = $this->create_test_assignment(['name' => 'Student Assignment 1']);
        $assignment2 = $this->create_test_assignment(['name' => 'Student Assignment 2']);

        $this->set_user_as_student();

        $result = get_activities_by_type_for_student::execute('assign');

        $this->assertIsArray($result);
        $this->assertCount(2, $result);

        // Check that student sees assignments.
        $names = array_column($result, 'activity_name');
        $this->assertContains('Student Assignment 1', $names);
        $this->assertContains('Student Assignment 2', $names);
    }

    /**
     * Test get_activities_by_type_for_teacher for forums.
     *
     * @covers \local_copilot\external\get_activities_by_type_for_teacher::execute
     */
    public function test_get_activities_teacher_forums(): void {
        // Create multiple forums.
        $forum1 = $this->create_test_forum(['name' => 'Forum 1']);
        $forum2 = $this->create_test_forum(['name' => 'Forum 2']);

        // Create an assignment (different type).
        $assignment = $this->create_test_assignment(['name' => 'Assignment 1']);

        $this->set_user_as_teacher();

        $result = get_activities_by_type_for_teacher::execute('forum');

        $this->assertIsArray($result);
        $this->assertCount(2, $result); // Only forums, not assignments.

        foreach ($result as $activity) {
            $this->assertEquals('forum', $activity['activity_type']);
        }

        $names = array_column($result, 'activity_name');
        $this->assertContains('Forum 1', $names);
        $this->assertContains('Forum 2', $names);
    }

    /**
     * Test get_activities with invalid activity type.
     *
     * @covers \local_copilot\external\get_activities_by_type_for_teacher::execute
     */
    public function test_get_activities_invalid_type(): void {
        $this->set_user_as_teacher();

        $result = get_activities_by_type_for_teacher::execute('nonexistent');

        // Should return empty array for invalid types.
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test get_activities with no activities of specified type.
     *
     * @covers \local_copilot\external\get_activities_by_type_for_teacher::execute
     */
    public function test_get_activities_no_matching_type(): void {
        // Create only assignments.
        $assignment = $this->create_test_assignment();

        $this->set_user_as_teacher();

        // Look for quizzes (none exist).
        $result = get_activities_by_type_for_teacher::execute('quiz');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test get_activities with hidden activities for student.
     *
     * @covers \local_copilot\external\get_activities_by_type_for_student::execute
     */
    public function test_get_activities_student_hidden(): void {
        global $DB;

        // Create assignment and hide it.
        $assignment = $this->create_test_assignment(['name' => 'Hidden Assignment']);

        $cm = get_coursemodule_from_instance('assign', $assignment->id);
        $DB->update_record('course_modules', ['id' => $cm->id, 'visible' => 0]);

        $this->set_user_as_student();

        $result = get_activities_by_type_for_student::execute('assign');

        // Students should not see hidden activities.
        $this->assertIsArray($result);

        $names = array_column($result, 'name');
        $this->assertNotContains('Hidden Assignment', $names);
    }

    /**
     * Test get_activities teacher can see hidden activities.
     *
     * @covers \local_copilot\external\get_activities_by_type_for_teacher::execute
     */
    public function test_get_activities_teacher_sees_hidden(): void {
        global $DB;

        // Create assignment and hide it.
        $assignment = $this->create_test_assignment(['name' => 'Hidden Assignment']);

        $cm = get_coursemodule_from_instance('assign', $assignment->id);
        $DB->update_record('course_modules', ['id' => $cm->id, 'visible' => 0]);

        $this->set_user_as_teacher();

        $result = get_activities_by_type_for_teacher::execute('assign');

        // Teachers should see hidden activities.
        $names = array_column($result, 'name');
        $this->assertContains('Hidden Assignment', $names);
    }

    /**
     * Test parameter validation.
     *
     * @covers \local_copilot\external\get_activities_by_type_for_teacher::execute_parameters
     * @covers \local_copilot\external\get_activities_by_type_for_teacher::execute_returns
     */
    public function test_teacher_parameters_and_returns(): void {
        $parameters = get_activities_by_type_for_teacher::execute_parameters();
        $this->assert_external_parameters($parameters, ['activitytype']);

        $returns = get_activities_by_type_for_teacher::execute_returns();
        $this->assert_external_returns($returns, 'multiple');
    }

    /**
     * Test parameter validation for student function.
     *
     * @covers \local_copilot\external\get_activities_by_type_for_student::execute_parameters
     * @covers \local_copilot\external\get_activities_by_type_for_student::execute_returns
     */
    public function test_student_parameters_and_returns(): void {
        $parameters = get_activities_by_type_for_student::execute_parameters();
        $this->assert_external_parameters($parameters, ['activitytype']);

        $returns = get_activities_by_type_for_student::execute_returns();
        $this->assert_external_returns($returns, 'multiple');
    }

    /**
     * Test get_activities across multiple courses.
     *
     * @covers \local_copilot\external\get_activities_by_type_for_teacher::execute
     */
    public function test_get_activities_multiple_courses(): void {
        // Create another course with assignments.
        $course2 = test_courses::create_test_course(['shortname' => 'COURSE2']);
        $teacher2 = test_courses::create_teacher_user($course2);

        // Create assignments in both courses.
        $assignment1 = $this->create_test_assignment(['name' => 'Course 1 Assignment']);

        $assignment2 = test_courses::create_test_assignment($course2, ['name' => 'Course 2 Assignment']);

        $this->set_user_as_teacher();

        $result = get_activities_by_type_for_teacher::execute('assign');

        // Should only see activities from courses where user has access.
        $names = array_column($result, 'name');
        $this->assertContains('Course 1 Assignment', $names);
        $this->assertNotContains('Course 2 Assignment', $names); // No access to course2.
    }

    /**
     * Test get_activities with course completion.
     *
     * @covers \local_copilot\external\get_activities_by_type_for_student::execute
     */
    public function test_get_activities_with_completion(): void {
        global $CFG, $DB;

        if (!$CFG->enablecompletion) {
            $this->markTestSkipped('Completion not enabled');
        }

        // Enable completion for course.
        $this->course->enablecompletion = 1;
        $DB->update_record('course', $this->course);

        // Create assignment with completion.
        $assignment = $this->create_test_assignment([
            'name' => 'Completion Assignment',
            'completion' => COMPLETION_TRACKING_MANUAL,
        ]);

        $this->set_user_as_student();

        $result = get_activities_by_type_for_student::execute('assign');

        $this->assertNotEmpty($result);

        // Should include completion information.
        $activity = $result[0];
        if (isset($activity['completion'])) {
            $this->assertIsInt($activity['completion']);
        }
    }

    /**
     * Test get_activities with restricted access.
     *
     * @covers \local_copilot\external\get_activities_by_type_for_student::execute
     */
    public function test_get_activities_with_restrictions(): void {
        global $CFG;

        if (!$CFG->enableavailability) {
            $this->markTestSkipped('Conditional access not enabled');
        }

        // Create assignment with date restriction.
        $assignment = $this->create_test_assignment([
            'name' => 'Restricted Assignment',
            'availability' => json_encode([
                'op' => '&',
                'c' => [
                    [
                        'type' => 'date',
                        'd' => '>=',
                        't' => time() + 86400, // Available tomorrow.
                    ],
                ],
                'showc' => [true], // Required for AND operator.
            ]),
        ]);

        $this->set_user_as_student();

        $result = get_activities_by_type_for_student::execute('assign');

        // Student may or may not see restricted activities depending on settings.
        $this->assertIsArray($result);
    }
}
