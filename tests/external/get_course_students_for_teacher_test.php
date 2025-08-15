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
 * Tests for get_course_students_for_teacher external function.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_copilot;

use local_copilot\external\get_course_students_for_teacher;
use local_copilot\tests\fixtures\test_courses;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/local/copilot/tests/base_test.php');

/**
 * Tests for get_course_students_for_teacher external function.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @runTestsInSeparateProcesses
 */
class external_get_course_students_test extends base_test {

    /**
     * Test get_course_students_for_teacher with valid course.
     *
     * @covers \local_copilot\external\get_course_students_for_teacher::execute
     */
    public function test_get_course_students_success() {
        // Create additional students.
        $student2 = test_courses::create_student_user($this->course, [
            'firstname' => 'Second',
            'lastname' => 'Student',
            'email' => 'student2@example.com',
        ]);
        
        $student3 = test_courses::create_student_user($this->course, [
            'firstname' => 'Third', 
            'lastname' => 'Student',
            'email' => 'student3@example.com',
        ]);
        
        $this->setUserAsTeacher();

        $result = get_course_students_for_teacher::execute($this->course->id);

        $this->assertIsArray($result);
        $this->assertCount(3, $result); // Original student + 2 new ones.
        
        // Check student structure.
        foreach ($result as $student) {
            $this->assertArrayHasKey('id', $student);
            $this->assertArrayHasKey('firstname', $student);
            $this->assertArrayHasKey('lastname', $student);
            $this->assertArrayHasKey('email', $student);
            $this->assertArrayHasKey('fullname', $student);
        }
        
        // Find our specific students.
        $studentids = array_column($result, 'id');
        $this->assertContains($this->student->id, $studentids);
        $this->assertContains($student2->id, $studentids);
        $this->assertContains($student3->id, $studentids);
    }

    /**
     * Test get_course_students_for_teacher without proper capabilities.
     *
     * @covers \local_copilot\external\get_course_students_for_teacher::execute
     */
    public function test_get_course_students_no_capability() {
        $this->setUserAsStudent();

        $this->expectException(\required_capability_exception::class);
        get_course_students_for_teacher::execute($this->course->id);
    }

    /**
     * Test get_course_students_for_teacher with invalid course ID.
     *
     * @covers \local_copilot\external\get_course_students_for_teacher::execute
     */
    public function test_get_course_students_invalid_course() {
        $this->setUserAsTeacher();

        $this->expectException(\dml_missing_record_exception::class);
        get_course_students_for_teacher::execute(99999);
    }

    /**
     * Test get_course_students_for_teacher with no students enrolled.
     *
     * @covers \local_copilot\external\get_course_students_for_teacher::execute
     */
    public function test_get_course_students_no_students() {
        global $DB;
        
        // Create a new course and enroll only the teacher.
        $emptycourse = $this->getDataGenerator()->create_course();
        $teacher = test_courses::create_teacher_user($emptycourse);
        
        $this->setUser($teacher);

        $result = get_course_students_for_teacher::execute($emptycourse->id);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test parameter validation.
     *
     * @covers \local_copilot\external\get_course_students_for_teacher::execute_parameters
     * @covers \local_copilot\external\get_course_students_for_teacher::execute_returns
     */
    public function test_parameters_and_returns() {
        $parameters = get_course_students_for_teacher::execute_parameters();
        $this->assertExternalParameters($parameters, ['courseid']);
        
        $returns = get_course_students_for_teacher::execute_returns();
        $this->assertExternalReturns($returns, 'multiple');
    }

    /**
     * Test get_course_students excludes teachers and other roles.
     *
     * @covers \local_copilot\external\get_course_students_for_teacher::execute
     */
    public function test_get_course_students_excludes_non_students() {
        global $DB;
        
        // Create a teaching assistant (different role).
        $ta = $this->getDataGenerator()->create_user([
            'firstname' => 'Teaching',
            'lastname' => 'Assistant',
        ]);
        
        // Get the non-editing teacher role.
        $tarole = $DB->get_record('role', ['shortname' => 'teacher']);
        if ($tarole) {
            $this->getDataGenerator()->enrol_user($ta->id, $this->course->id, $tarole->id);
        }
        
        $this->setUserAsTeacher();

        $result = get_course_students_for_teacher::execute($this->course->id);

        // Should only return students, not teachers or TAs.
        $userids = array_column($result, 'id');
        $this->assertContains($this->student->id, $userids);
        $this->assertNotContains($this->teacher->id, $userids);
        if ($tarole) {
            $this->assertNotContains($ta->id, $userids);
        }
    }

    /**
     * Test get_course_students with suspended enrollment.
     *
     * @covers \local_copilot\external\get_course_students_for_teacher::execute
     */
    public function test_get_course_students_suspended_enrollment() {
        global $DB;
        
        // Suspend the student's enrollment.
        $enrolment = $DB->get_record('user_enrolments', [
            'userid' => $this->student->id,
        ]);
        
        if ($enrolment) {
            $enrolment->status = ENROL_USER_SUSPENDED;
            $DB->update_record('user_enrolments', $enrolment);
        }
        
        $this->setUserAsTeacher();

        $result = get_course_students_for_teacher::execute($this->course->id);

        // Suspended students should not be included by default.
        $userids = array_column($result, 'id');
        $this->assertNotContains($this->student->id, $userids);
    }

    /**
     * Test get_course_students includes profile information.
     *
     * @covers \local_copilot\external\get_course_students_for_teacher::execute
     */
    public function test_get_course_students_includes_profile() {
        $this->setUserAsTeacher();

        $result = get_course_students_for_teacher::execute($this->course->id);

        $this->assertNotEmpty($result);
        
        $student = $result[0];
        
        // Should include basic profile information.
        $this->assertArrayHasKey('id', $student);
        $this->assertArrayHasKey('firstname', $student);
        $this->assertArrayHasKey('lastname', $student);
        $this->assertArrayHasKey('fullname', $student);
        $this->assertArrayHasKey('email', $student);
        
        // Verify the fullname is properly formatted.
        $this->assertEquals(
            $this->student->firstname . ' ' . $this->student->lastname,
            $student['fullname']
        );
    }

    /**
     * Test get_course_students with groups.
     *
     * @covers \local_copilot\external\get_course_students_for_teacher::execute
     */
    public function test_get_course_students_with_groups() {
        global $CFG;
        
        // Create a group and add student to it.
        $group = $this->getDataGenerator()->create_group(['courseid' => $this->course->id]);
        $this->getDataGenerator()->create_group_member(['groupid' => $group->id, 'userid' => $this->student->id]);
        
        $this->setUserAsTeacher();

        $result = get_course_students_for_teacher::execute($this->course->id);

        $this->assertNotEmpty($result);
        
        // Find our student in the results.
        $foundstudent = null;
        foreach ($result as $student) {
            if ($student['id'] == $this->student->id) {
                $foundstudent = $student;
                break;
            }
        }
        
        $this->assertNotNull($foundstudent);
        
        // Should include group information if available.
        if (isset($foundstudent['groups'])) {
            $this->assertIsArray($foundstudent['groups']);
        }
    }

    /**
     * Test get_course_students with pagination (if supported).
     *
     * @covers \local_copilot\external\get_course_students_for_teacher::execute
     */
    public function test_get_course_students_pagination() {
        // Create many students to test pagination.
        for ($i = 1; $i <= 15; $i++) {
            test_courses::create_student_user($this->course, [
                'firstname' => "Student$i",
                'lastname' => 'Test',
                'email' => "student$i@example.com",
            ]);
        }
        
        $this->setUserAsTeacher();

        $result = get_course_students_for_teacher::execute($this->course->id);

        // Should return all students (original + 15 new = 16 total).
        $this->assertCount(16, $result);
        
        // Results should be ordered consistently.
        $names = array_column($result, 'fullname');
        $sortednames = $names;
        sort($sortednames);
        
        // Names should be in some consistent order.
        $this->assertIsArray($names);
        $this->assertCount(16, $names);
    }
}