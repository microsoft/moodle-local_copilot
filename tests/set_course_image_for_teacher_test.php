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
 * Unit tests for the set_course_image_for_teacher external function.
 *
 * @package local_copilot
 * @category test
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2026 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot;

use advanced_testcase;

/**
 * Unit tests for local_copilot\external\set_course_image_for_teacher.
 *
 * The download is served by a curl test double, so no network access is needed. Blocking of internal destinations is
 * done by core's curl security helper, which is covered by core tests.
 *
 * @covers \local_copilot\external\set_course_image_for_teacher
 */
final class set_course_image_for_teacher_test extends advanced_testcase {
    /** @var string base64-encoded 1x1 transparent PNG image. */
    private const PNG_1X1 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    /** @var string base64-encoded 1x1 WebP image. */
    private const WEBP_1X1 = 'UklGRhoAAABXRUJQVlA4TA0AAAAvAAAAEAcQERGIiP4HAA==';

    /**
     * Reset state and load the test fixtures before each test.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);

        require_once(__DIR__ . '/fixtures/testable_curl.php');
        require_once(__DIR__ . '/fixtures/testable_set_course_image_for_teacher.php');
    }

    /**
     * Get the files in the course overview files area.
     *
     * @param int $courseid
     * @return \stored_file[]
     */
    private function get_overview_files(int $courseid): array {
        $context = \context_course::instance($courseid);
        return get_file_storage()->get_area_files($context->id, 'course', 'overviewfiles', 0, 'filename', false);
    }

    /**
     * Test that the stored file name is derived from the detected content, not from the URL extension.
     */
    public function test_execute_uses_detected_type_for_extension(): void {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();

        $png = base64_decode(self::PNG_1X1);
        testable_set_course_image_for_teacher::$curl = new testable_curl($png, 200, ['Content-Type' => 'image/png']);

        $result = testable_set_course_image_for_teacher::execute($course->id, 'https://example.com/image.html');
        $this->assertTrue($result['success']);

        $files = $this->get_overview_files($course->id);
        $this->assertCount(1, $files);
        $file = reset($files);
        $this->assertSame('course_image.png', $file->get_filename());
        $this->assertSame('image/png', $file->get_mimetype());
        $this->assertSame(sha1($png), $file->get_contenthash());
    }

    /**
     * Test that a WebP image is stored with a .jpg extension, as Moodle 4.5 does not display .webp course images.
     */
    public function test_execute_stores_webp_as_jpg(): void {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();

        $webp = base64_decode(self::WEBP_1X1);
        testable_set_course_image_for_teacher::$curl = new testable_curl($webp, 200, ['Content-Type' => 'image/webp']);

        $result = testable_set_course_image_for_teacher::execute($course->id, 'https://example.com/image.webp');
        $this->assertTrue($result['success']);

        $files = $this->get_overview_files($course->id);
        $this->assertCount(1, $files);
        $file = reset($files);
        $this->assertSame('course_image.jpg', $file->get_filename());
        $this->assertSame(sha1($webp), $file->get_contenthash());
    }

    /**
     * Test that HTML content is rejected even when the response advertises an image Content-Type header.
     *
     * Regression test: the content type must be detected from the downloaded bytes, not trusted from the remote header,
     * and a rejected download must leave the existing course image untouched.
     */
    public function test_execute_rejects_html_with_image_content_type(): void {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);

        // Give the course an existing course image.
        $existing = get_file_storage()->create_file_from_string([
            'contextid' => $context->id,
            'component' => 'course',
            'filearea' => 'overviewfiles',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'existing.png',
        ], base64_decode(self::PNG_1X1));

        testable_set_course_image_for_teacher::$curl = new testable_curl(
            '<html><body><script>alert(1)</script></body></html>',
            200,
            ['Content-Type' => 'image/png']
        );

        try {
            testable_set_course_image_for_teacher::execute($course->id, 'https://example.com/image.png');
            $this->fail('Expected the download to be rejected.');
        } catch (\RuntimeException $e) {
            $this->assertSame('415 Unsupported Media Type', $e->getMessage());
        }

        // The existing course image must be unchanged.
        $files = $this->get_overview_files($course->id);
        $this->assertCount(1, $files);
        $file = reset($files);
        $this->assertSame('existing.png', $file->get_filename());
        $this->assertSame($existing->get_contenthash(), $file->get_contenthash());
    }

    /**
     * Test that a real PNG image is accepted even when the response advertises a non-image Content-Type header.
     */
    public function test_execute_accepts_png_with_non_image_content_type(): void {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();

        $png = base64_decode(self::PNG_1X1);
        testable_set_course_image_for_teacher::$curl = new testable_curl($png, 200, ['Content-Type' => 'text/html']);

        $result = testable_set_course_image_for_teacher::execute($course->id, 'https://example.com/image');
        $this->assertTrue($result['success']);

        $files = $this->get_overview_files($course->id);
        $this->assertCount(1, $files);
        $file = reset($files);
        $this->assertSame('course_image.png', $file->get_filename());
        $this->assertSame('image/png', $file->get_mimetype());
        $this->assertSame(sha1($png), $file->get_contenthash());
    }

    /**
     * Test that an unsuccessful download is reported as not found, without storing anything.
     */
    public function test_execute_rejects_unsuccessful_download(): void {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();

        testable_set_course_image_for_teacher::$curl = new testable_curl(
            base64_decode(self::PNG_1X1),
            500,
            ['Content-Type' => 'image/png']
        );

        try {
            testable_set_course_image_for_teacher::execute($course->id, 'https://example.com/image.png');
            $this->fail('Expected the download to be rejected.');
        } catch (\RuntimeException $e) {
            $this->assertSame('404 Not Found', $e->getMessage());
        }

        $this->assertEmpty($this->get_overview_files($course->id));
    }
}
