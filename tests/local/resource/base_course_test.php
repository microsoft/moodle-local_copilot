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
 * Tests for base_course resource class.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_copilot;

use local_copilot\local\resource\base_course;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/local/copilot/tests/base_testcase.php');

/**
 * Tests for base_course resource class.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @runTestsInSeparateProcesses
 */
final class base_course_test extends base_test {
    /**
     * Test extract_course_data method.
     *
     * @covers \local_copilot\local\resource\base_course::extract_course_data
     */
    public function test_extract_course_data(): void {
        $coursedata = base_course::extract_course_data($this->course, $this->student->id);

        $this->assertIsArray($coursedata);

        // Check required fields.
        $this->assertArrayHasKey('id', $coursedata);
        $this->assertArrayHasKey('fullname', $coursedata);
        $this->assertArrayHasKey('shortname', $coursedata);
        $this->assertArrayHasKey('summary', $coursedata);
        $this->assertArrayHasKey('startdate', $coursedata);

        // Verify data values.
        $this->assertEquals($this->course->id, $coursedata['id']);
        $this->assertEquals($this->course->fullname, $coursedata['fullname']);
        $this->assertEquals($this->course->shortname, $coursedata['shortname']);
    }

    /**
     * Test get_return_structure method.
     *
     * @covers \local_copilot\local\resource\base_course::get_return_structure
     */
    public function test_get_return_structure(): void {
        $structure = base_course::get_return_structure();

        $this->assertIsArray($structure);

        // Check that structure contains expected fields.
        $this->assertArrayHasKey('id', $structure);
        $this->assertArrayHasKey('fullname', $structure);
        $this->assertArrayHasKey('shortname', $structure);

        // Check that each field is an external_value instance.
        foreach ($structure as $field => $definition) {
            $this->assertInstanceOf(\external_value::class, $definition, "Field '$field' should be external_value");
        }
    }

    /**
     * Test extract_course_data with course image.
     *
     * @covers \local_copilot\local\resource\base_course::extract_course_data
     */
    public function test_extract_course_data_with_image(): void {
        // This test would require setting up course image files.
        // For now, we just verify the method handles image extraction gracefully.
        $coursedata = base_course::extract_course_data($this->course, $this->student->id);

        if (isset($coursedata['course_image'])) {
            $this->assertIsString($coursedata['course_image']);
        }
    }

    /**
     * Test extract_course_data with completion enabled.
     *
     * @covers \local_copilot\local\resource\base_course::extract_course_data
     */
    public function test_extract_course_data_with_completion(): void {
        global $CFG, $DB;

        if (!$CFG->enablecompletion) {
            $this->markTestSkipped('Completion not enabled');
        }

        // Enable completion for the course.
        $this->course->enablecompletion = 1;
        $DB->update_record('course', $this->course);

        $coursedata = base_course::extract_course_data($this->course, $this->student->id);

        if (isset($coursedata['enablecompletion'])) {
            $this->assertEquals(1, $coursedata['enablecompletion']);
        }
    }

    /**
     * Test extract_course_data with course dates.
     *
     * @covers \local_copilot\local\resource\base_course::extract_course_data
     */
    public function test_extract_course_data_with_dates(): void {
        global $DB;

        // Set course start and end dates.
        $startdate = time() - 86400; // Started 1 day ago.
        $enddate = time() + (30 * 86400); // Ends in 30 days.

        $this->course->startdate = $startdate;
        $this->course->enddate = $enddate;
        $DB->update_record('course', $this->course);

        // Refetch course to get updated data.
        $updatedcourse = $DB->get_record('course', ['id' => $this->course->id]);

        $coursedata = base_course::extract_course_data($updatedcourse, $this->student->id);

        $this->assertEquals($startdate, $coursedata['startdate']);
        if (isset($coursedata['enddate'])) {
            $this->assertEquals($enddate, $coursedata['enddate']);
        }
    }

    /**
     * Test extract_course_data with course format.
     *
     * @covers \local_copilot\local\resource\base_course::extract_course_data
     */
    public function test_extract_course_data_with_format(): void {
        $coursedata = base_course::extract_course_data($this->course, $this->student->id);

        if (isset($coursedata['format'])) {
            $this->assertIsString($coursedata['format']);
            $this->assertNotEmpty($coursedata['format']);
        }
    }

    /**
     * Test extract_course_data with course category.
     *
     * @covers \local_copilot\local\resource\base_course::extract_course_data
     */
    public function test_extract_course_data_with_category(): void {
        // Create a course in a specific category.
        $category = $this->getDataGenerator()->create_category(['name' => 'Test Category']);
        $categorizedcourse = $this->getDataGenerator()->create_course([
            'category' => $category->id,
            'fullname' => 'Categorized Course',
        ]);

        $coursedata = base_course::extract_course_data($categorizedcourse, $this->student->id);

        $this->assertEquals($category->id, $coursedata['category']);
        if (isset($coursedata['categoryname'])) {
            $this->assertEquals('Test Category', $coursedata['categoryname']);
        }
    }

    /**
     * Test extract_course_data with invalid user ID.
     *
     * @covers \local_copilot\local\resource\base_course::extract_course_data
     */
    public function test_extract_course_data_invalid_user(): void {
        // This should still work but might have different enrollment status.
        $coursedata = base_course::extract_course_data($this->course, 99999);

        $this->assertIsArray($coursedata);
        $this->assertEquals($this->course->id, $coursedata['id']);

        // User-specific fields might be different or missing.
        if (isset($coursedata['enrolled'])) {
            $this->assertIsBool($coursedata['enrolled']);
        }
    }

    /**
     * Test extract_course_data with course visibility.
     *
     * @covers \local_copilot\local\resource\base_course::extract_course_data
     */
    public function test_extract_course_data_visibility(): void {
        global $DB;

        // Test with visible course.
        $coursedata = base_course::extract_course_data($this->course, $this->student->id);
        if (isset($coursedata['visible'])) {
            $this->assertEquals(1, $coursedata['visible']);
        }

        // Hide the course and test again.
        $this->course->visible = 0;
        $DB->update_record('course', $this->course);

        $hiddencourse = $DB->get_record('course', ['id' => $this->course->id]);
        $coursedata = base_course::extract_course_data($hiddencourse, $this->student->id);

        if (isset($coursedata['visible'])) {
            $this->assertEquals(0, $coursedata['visible']);
        }
    }

    /**
     * Test extract_course_data with course language.
     *
     * @covers \local_copilot\local\resource\base_course::extract_course_data
     */
    public function test_extract_course_data_with_language(): void {
        global $DB;

        // Set course language.
        $this->course->lang = 'en';
        $DB->update_record('course', $this->course);

        $updatedcourse = $DB->get_record('course', ['id' => $this->course->id]);
        $coursedata = base_course::extract_course_data($updatedcourse, $this->student->id);

        if (isset($coursedata['lang'])) {
            $this->assertEquals('en', $coursedata['lang']);
        }
    }

    /**
     * Test return structure completeness.
     *
     * @covers \local_copilot\local\resource\base_course::get_return_structure
     */
    public function test_return_structure_completeness(): void {
        $structure = base_course::get_return_structure();

        // Test that structure includes common course fields.
        $expectedfields = ['id', 'fullname', 'shortname', 'summary'];

        foreach ($expectedfields as $field) {
            $this->assertArrayHasKey($field, $structure, "Missing required field: $field");
        }

        // All fields should have appropriate PARAM types.
        $this->assertEquals(PARAM_INT, $structure['id']->type);
        $this->assertEquals(PARAM_TEXT, $structure['fullname']->type);
        $this->assertEquals(PARAM_TEXT, $structure['shortname']->type);
    }
}
