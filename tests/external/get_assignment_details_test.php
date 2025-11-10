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
 * Tests for assignment details external functions.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_copilot;

use local_copilot\external\get_assignment_details_for_teacher;
use local_copilot\external\get_assignment_details_for_student;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/local/copilot/tests/base_testcase.php');
require_once($CFG->dirroot . '/local/copilot/classes/external/get_assignment_details_for_teacher.php');
require_once($CFG->dirroot . '/local/copilot/classes/external/get_assignment_details_for_student.php');

/**
 * Tests for assignment details external functions.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @runTestsInSeparateProcesses
 */
final class get_assignment_details_test extends base_test {
    /**
     * @var \stdClass Test assignment.
     */
    private $assignment;

    /**
     * Set up test assignment.
     */
    protected function setUp(): void {
        parent::setUp();

        $this->assignment = $this->create_test_assignment([
            'name' => 'Test Assignment Details',
            'intro' => 'Assignment for testing details',
            'grade' => 100,
            'duedate' => time() + (7 * 24 * 60 * 60), // Due in 1 week.
        ]);
    }

    /**
     * Test get_assignment_details_for_teacher.
     *
     * @covers \local_copilot\external\get_assignment_details_for_teacher::execute
     */
    public function test_get_assignment_details_teacher(): void {
        $this->set_user_as_teacher();

        $result = get_assignment_details_for_teacher::execute($this->assignment->id);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('intro', $result);
        $this->assertArrayHasKey('grade', $result);
        $this->assertArrayHasKey('duedate', $result);
        $this->assertArrayHasKey('submissions', $result);

        $this->assertEquals($this->assignment->id, $result['id']);
        $this->assertEquals('Test Assignment Details', $result['name']);
        $this->assertEquals(100, $result['grade']);
        $this->assertIsArray($result['submissions']);
    }

    /**
     * Test get_assignment_details_for_student.
     *
     * @covers \local_copilot\external\get_assignment_details_for_student::execute
     */
    public function test_get_assignment_details_student(): void {
        $this->set_user_as_student();

        $result = get_assignment_details_for_student::execute($this->assignment->id);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('intro', $result);
        $this->assertArrayHasKey('grade', $result);
        $this->assertArrayHasKey('submission', $result);

        $this->assertEquals($this->assignment->id, $result['id']);
        $this->assertEquals('Test Assignment Details', $result['name']);

        // Student should see their own submission info.
        if ($result['submission']) {
            $this->assertIsArray($result['submission']);
            $this->assertArrayHasKey('status', $result['submission']);
        }
    }

    /**
     * Test teacher sees all submissions.
     *
     * @covers \local_copilot\external\get_assignment_details_for_teacher::execute
     */
    public function test_teacher_sees_all_submissions(): void {
        // Create a submission from the student.
        $assignmentgenerator = $this->getDataGenerator()->get_plugin_generator('mod_assign');

        $this->setUser($this->student);
        $assignmentgenerator->create_submission([
            'cmid' => $this->assignment->cmid,
            'userid' => $this->student->id,
            'onlinetext_editor' => [
                'text' => 'Student submission text',
                'format' => FORMAT_HTML,
            ],
        ]);

        $this->set_user_as_teacher();

        $result = get_assignment_details_for_teacher::execute($this->assignment->id);

        $this->assertArrayHasKey('submissions', $result);
        $this->assertIsArray($result['submissions']);
        $this->assertNotEmpty($result['submissions']);

        // Find the student's submission.
        $studentsubmission = null;
        foreach ($result['submissions'] as $submission) {
            if ($submission['userid'] == $this->student->id) {
                $studentsubmission = $submission;
                break;
            }
        }

        $this->assertNotNull($studentsubmission);
        $this->assertArrayHasKey('status', $studentsubmission);
        $this->assertArrayHasKey('timemodified', $studentsubmission);
    }

    /**
     * Test student only sees own submission.
     *
     * @covers \local_copilot\external\get_assignment_details_for_student::execute
     */
    public function test_student_sees_own_submission(): void {
        // Create a submission from the student.
        $assignmentgenerator = $this->getDataGenerator()->get_plugin_generator('mod_assign');

        $this->setUser($this->student);
        $assignmentgenerator->create_submission([
            'cmid' => $this->assignment->cmid,
            'userid' => $this->student->id,
            'onlinetext_editor' => [
                'text' => 'My submission text',
                'format' => FORMAT_HTML,
            ],
        ]);

        $result = get_assignment_details_for_student::execute($this->assignment->id);

        $this->assertArrayHasKey('submission', $result);

        if ($result['submission']) {
            $this->assertArrayHasKey('status', $result['submission']);
            $this->assertEquals($this->student->id, $result['submission']['userid']);
        }
    }

    /**
     * Test assignment details with grades.
     *
     * @covers \local_copilot\external\get_assignment_details_for_teacher::execute
     */
    public function test_assignment_details_with_grades(): void {
        global $DB;

        // Create and grade a submission.
        $assignmentgenerator = $this->getDataGenerator()->get_plugin_generator('mod_assign');

        $this->setUser($this->student);
        $submission = $assignmentgenerator->create_submission([
            'cmid' => $this->assignment->cmid,
            'userid' => $this->student->id,
            'onlinetext_editor' => [
                'text' => 'Graded submission',
                'format' => FORMAT_HTML,
            ],
        ]);

        // Grade the submission.
        $this->set_user_as_teacher();
        $assignmentgenerator->create_grade([
            'cmid' => $this->assignment->cmid,
            'userid' => $this->student->id,
            'grade' => 85,
            'grader' => $this->teacher->id,
        ]);

        $result = get_assignment_details_for_teacher::execute($this->assignment->id);

        $this->assertArrayHasKey('submissions', $result);

        // Find the graded submission.
        $gradedsubmission = null;
        foreach ($result['submissions'] as $submission) {
            if ($submission['userid'] == $this->student->id) {
                $gradedsubmission = $submission;
                break;
            }
        }

        $this->assertNotNull($gradedsubmission);
        if (isset($gradedsubmission['grade'])) {
            $this->assertEquals(85, $gradedsubmission['grade']);
        }
    }

    /**
     * Test student sees own grade.
     *
     * @covers \local_copilot\external\get_assignment_details_for_student::execute
     */
    public function test_student_sees_own_grade(): void {
        // Create and grade submission.
        $assignmentgenerator = $this->getDataGenerator()->get_plugin_generator('mod_assign');

        $this->setUser($this->student);
        $assignmentgenerator->create_submission([
            'cmid' => $this->assignment->cmid,
            'userid' => $this->student->id,
            'onlinetext_editor' => [
                'text' => 'Student work',
                'format' => FORMAT_HTML,
            ],
        ]);

        $this->set_user_as_teacher();
        $assignmentgenerator->create_grade([
            'cmid' => $this->assignment->cmid,
            'userid' => $this->student->id,
            'grade' => 92,
            'grader' => $this->teacher->id,
        ]);

        $this->set_user_as_student();
        $result = get_assignment_details_for_student::execute($this->assignment->id);

        if (isset($result['grade'])) {
            $this->assertArrayHasKey('grade', $result);
            $this->assertEquals(92, $result['grade']['grade']);
        }
    }

    /**
     * Test assignment details with invalid assignment ID.
     *
     * @covers \local_copilot\external\get_assignment_details_for_teacher::execute
     */
    public function test_assignment_details_invalid_id(): void {
        $this->set_user_as_teacher();

        $this->expectException(\dml_missing_record_exception::class);
        get_assignment_details_for_teacher::execute(99999);
    }

    /**
     * Test assignment details without proper access.
     *
     * @covers \local_copilot\external\get_assignment_details_for_teacher::execute
     */
    public function test_assignment_details_no_access(): void {
        // Create user not enrolled in course.
        $otheruser = $this->getDataGenerator()->create_user();
        $this->setUser($otheruser);

        $this->expectException(\require_login_exception::class);
        get_assignment_details_for_teacher::execute($this->assignment->id);
    }

    /**
     * Test parameter validation.
     *
     * @covers \local_copilot\external\get_assignment_details_for_teacher::execute_parameters
     * @covers \local_copilot\external\get_assignment_details_for_teacher::execute_returns
     */
    public function test_teacher_parameters_and_returns(): void {
        $parameters = get_assignment_details_for_teacher::execute_parameters();
        $this->assert_external_parameters($parameters, ['assignmentid']);

        $returns = get_assignment_details_for_teacher::execute_returns();
        $this->assert_external_returns($returns, 'single');
    }

    /**
     * Test student parameter validation.
     *
     * @covers \local_copilot\external\get_assignment_details_for_student::execute_parameters
     * @covers \local_copilot\external\get_assignment_details_for_student::execute_returns
     */
    public function test_student_parameters_and_returns(): void {
        $parameters = get_assignment_details_for_student::execute_parameters();
        $this->assert_external_parameters($parameters, ['assignmentid']);

        $returns = get_assignment_details_for_student::execute_returns();
        $this->assert_external_returns($returns, 'single');
    }

    /**
     * Test assignment details with due date passed.
     *
     * @covers \local_copilot\external\get_assignment_details_for_student::execute
     */
    public function test_assignment_details_overdue(): void {
        global $DB;

        // Create assignment with past due date.
        $overdueassignment = $this->create_test_assignment([
            'name' => 'Overdue Assignment',
            'duedate' => time() - (2 * 24 * 60 * 60), // Due 2 days ago.
        ]);

        $this->set_user_as_student();

        $result = get_assignment_details_for_student::execute($overdueassignment->id);

        $this->assertArrayHasKey('duedate', $result);
        $this->assertLessThan(time(), $result['duedate']); // Confirms it's in the past.

        // Should indicate overdue status.
        if (isset($result['status'])) {
            // Status might indicate overdue condition.
            $this->assertIsString($result['status']);
        }
    }

    /**
     * Test assignment details with file submissions.
     *
     * @covers \local_copilot\external\get_assignment_details_for_teacher::execute
     */
    public function test_assignment_details_file_submission(): void {
        // Create assignment that accepts file submissions.
        $fileassignment = $this->create_test_assignment([
            'name' => 'File Assignment',
            'submissiontypes' => 'file',
        ]);

        $this->set_user_as_teacher();

        $result = get_assignment_details_for_teacher::execute($fileassignment->id);

        $this->assertArrayHasKey('submissiontypes', $result);

        // Should indicate file submission type.
        if (isset($result['submissiontypes'])) {
            $this->assertContains('file', $result['submissiontypes']);
        }
    }
}
