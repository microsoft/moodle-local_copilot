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
 * Tests for content creation external functions.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_copilot;

use local_copilot\external\create_announcement_for_teacher;
use local_copilot\external\create_forum_for_teacher;
use local_copilot\external\set_course_image_for_teacher;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/local/copilot/tests/base_testcase.php');

/**
 * Tests for content creation external functions.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @runTestsInSeparateProcesses
 */
final class create_content_for_teacher_test extends base_test {
    /**
     * Test create_announcement_for_teacher.
     *
     * @covers \local_copilot\external\create_announcement_for_teacher::execute
     */
    public function test_create_announcement_success(): void {
        global $DB;

        $this->set_user_as_teacher();

        $result = create_announcement_for_teacher::execute(
            $this->course->id,
            'Important Announcement',
            'This is an important announcement for all students.',
            0,
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

        // Verify announcement forum discussion was created.
        $discussions = $DB->get_records('forum_discussions', ['forum' => $result['id']]);
        $this->assertNotEmpty($discussions);

        $discussion = reset($discussions);
        $this->assertEquals('Important Announcement', $discussion->name);

        // Verify the post was created.
        $post = $DB->get_record('forum_posts', ['discussion' => $discussion->id, 'parent' => 0]);
        $this->assertNotFalse($post);
        $this->assertStringContainsString('This is an important announcement', $post->message);
    }

    /**
     * Test create_forum_for_teacher.
     *
     * @covers \local_copilot\external\create_forum_for_teacher::execute
     */
    public function test_create_forum_success(): void {
        global $DB;

        $this->set_user_as_teacher();

        $result = create_forum_for_teacher::execute(
            $this->course->id,
            'Discussion Forum',
            0,
            'A forum for class discussions'
        );

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('error', $result);
        $this->assertTrue($result['success']);
        $this->assertGreaterThan(0, $result['id']);
        $this->assertEquals('', $result['error']);

        // Verify forum was created.
        $forum = $DB->get_record('forum', ['id' => $result['id']]);
        $this->assertNotFalse($forum);
        $this->assertEquals('Discussion Forum', $forum->name);
        $this->assertEquals('A forum for class discussions', $forum->intro);
        $this->assertEquals($this->course->id, $forum->course);
    }

    /**
     * Test set_course_image_for_teacher.
     *
     * @covers \local_copilot\external\set_course_image_for_teacher::execute
     */
    public function test_set_course_image_success(): void {
        $this->set_user_as_teacher();

        // Use a sample image URL.
        $imageurl = 'https://example.com/course-image.jpg';

        $result = set_course_image_for_teacher::execute($this->course->id, $imageurl);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);

        // In a real implementation, this would actually download and set the image.
        // For testing, we just verify the function completes without error.
    }

    /**
     * Test create_announcement without proper capabilities.
     *
     * @covers \local_copilot\external\create_announcement_for_teacher::execute
     */
    public function test_create_announcement_no_capability(): void {
        $this->set_user_as_student();

        $this->expectOutputRegex('/403/');
        create_announcement_for_teacher::execute(
            $this->course->id,
            'Unauthorized Announcement',
            'This should fail.',
            0,
            null,
            null
        );
    }

    /**
     * Test create_forum without proper capabilities.
     *
     * @covers \local_copilot\external\create_forum_for_teacher::execute
     */
    public function test_create_forum_no_capability(): void {
        $this->set_user_as_student();

        $this->expectOutputRegex('/403/');
        create_forum_for_teacher::execute(
            $this->course->id,
            'Unauthorized Forum',
            0,
            'This should fail.'
        );
    }

    /**
     * Test set_course_image without proper capabilities.
     *
     * @covers \local_copilot\external\set_course_image_for_teacher::execute
     */
    public function test_set_course_image_no_capability(): void {
        $this->set_user_as_student();

        $this->expectException(\required_capability_exception::class);
        set_course_image_for_teacher::execute($this->course->id, 'https://example.com/image.jpg');
    }

    /**
     * Test create_announcement with invalid course.
     *
     * @covers \local_copilot\external\create_announcement_for_teacher::execute
     */
    public function test_create_announcement_invalid_course(): void {
        $this->setAdminUser();

        $this->expectOutputRegex('/404/');
        create_announcement_for_teacher::execute(
            99999,
            'Test Announcement',
            'Test message.',
            0,
            null,
            null
        );
    }

    /**
     * Test create_forum with invalid course.
     *
     * @covers \local_copilot\external\create_forum_for_teacher::execute
     */
    public function test_create_forum_invalid_course(): void {
        $this->setAdminUser();

        $this->expectOutputRegex('/404/');
        create_forum_for_teacher::execute(
            99999,
            'Test Forum',
            0,
            'Test intro.'
        );
    }

    /**
     * Test parameter validation for announcement creation.
     *
     * @covers \local_copilot\external\create_announcement_for_teacher::execute_parameters
     * @covers \local_copilot\external\create_announcement_for_teacher::execute_returns
     */
    public function test_announcement_parameters_and_returns(): void {
        $parameters = create_announcement_for_teacher::execute_parameters();
        $this->assert_external_parameters($parameters, ['course_id', 'announcement_subject', 'announcement_message']);

        $returns = create_announcement_for_teacher::execute_returns();
        $this->assert_external_returns($returns, 'single');
    }

    /**
     * Test parameter validation for forum creation.
     *
     * @covers \local_copilot\external\create_forum_for_teacher::execute_parameters
     * @covers \local_copilot\external\create_forum_for_teacher::execute_returns
     */
    public function test_forum_parameters_and_returns(): void {
        $parameters = create_forum_for_teacher::execute_parameters();
        $this->assert_external_parameters($parameters, ['course_id', 'forum_name', 'section_id']);

        $returns = create_forum_for_teacher::execute_returns();
        $this->assert_external_returns($returns, 'single');
    }

    /**
     * Test parameter validation for course image setting.
     *
     * @covers \local_copilot\external\set_course_image_for_teacher::execute_parameters
     * @covers \local_copilot\external\set_course_image_for_teacher::execute_returns
     */
    public function test_course_image_parameters_and_returns(): void {
        $parameters = set_course_image_for_teacher::execute_parameters();
        $this->assert_external_parameters($parameters, ['courseid', 'imageurl']);

        $returns = set_course_image_for_teacher::execute_returns();
        $this->assert_external_returns($returns, 'single');
    }

    /**
     * Test create_announcement with empty subject.
     *
     * @covers \local_copilot\external\create_announcement_for_teacher::execute
     */
    public function test_create_announcement_empty_subject(): void {
        $this->set_user_as_teacher();

        $this->expectException(\invalid_parameter_exception::class);
        create_announcement_for_teacher::execute(
            $this->course->id,
            '',
            'Message without subject.',
            0,
            null,
            null
        );
    }

    /**
     * Test create_forum with empty name.
     *
     * @covers \local_copilot\external\create_forum_for_teacher::execute
     */
    public function test_create_forum_empty_name(): void {
        $this->set_user_as_teacher();

        $this->expectException(\invalid_parameter_exception::class);
        create_forum_for_teacher::execute(
            $this->course->id,
            '',
            0,
            'Forum without name.'
        );
    }

    /**
     * Test set_course_image with invalid URL.
     *
     * @covers \local_copilot\external\set_course_image_for_teacher::execute
     */
    public function test_set_course_image_invalid_url(): void {
        $this->set_user_as_teacher();

        $this->expectException(\invalid_parameter_exception::class);
        set_course_image_for_teacher::execute($this->course->id, 'not-a-valid-url');
    }

    /**
     * Test create_announcement in different course sections.
     *
     * @covers \local_copilot\external\create_announcement_for_teacher::execute
     */
    public function test_create_announcement_different_sections(): void {
        $this->set_user_as_teacher();

        // Announcements are posted to the news forum, not a specific section.
        // This test verifies the announcement was created successfully.
        $result = create_announcement_for_teacher::execute(
            $this->course->id,
            'Section 1 Announcement',
            'Announcement for section 1.',
            0,
            null,
            null
        );

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertGreaterThan(0, $result['id']);
    }

    /**
     * Test create_forum with specific options.
     *
     * @covers \local_copilot\external\create_forum_for_teacher::execute
     */
    public function test_create_forum_with_options(): void {
        global $DB;

        $this->set_user_as_teacher();

        $result = create_forum_for_teacher::execute(
            $this->course->id,
            'Advanced Discussion Forum',
            2,
            'Forum with specific settings'
        );

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertGreaterThan(0, $result['id']);

        // Verify forum was created.
        $forum = $DB->get_record('forum', ['id' => $result['id']]);
        $this->assertNotFalse($forum);
        $this->assertEquals('Advanced Discussion Forum', $forum->name);
    }
}
