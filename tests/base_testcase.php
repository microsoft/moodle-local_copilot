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
 * Base test case for local_copilot tests.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_copilot;

use advanced_testcase;
use external_function_parameters;
use external_single_structure;
use local_copilot\tests\fixtures\test_courses;
use stdClass;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/local/copilot/tests/fixtures/test_courses.php');

/**
 * Base test case for local_copilot tests.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class base_test extends advanced_testcase {
    /**
     * @var stdClass Test course.
     */
    protected $course;

    /**
     * @var stdClass Test teacher user.
     */
    protected $teacher;

    /**
     * @var stdClass Test student user.
     */
    protected $student;

    /**
     * @var stdClass Test OAuth client.
     */
    protected $oauthclient;

    /**
     * Set up test environment.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();

        // Create test course.
        $this->course = test_courses::create_test_course();

        // Create test users.
        $this->teacher = test_courses::create_teacher_user($this->course);
        $this->student = test_courses::create_student_user($this->course);

        // Set up OAuth client.
        $this->oauthclient = test_courses::create_oauth_client();

        // Set up basic Copilot configuration.
        test_courses::setup_copilot_config('teacher');
        test_courses::setup_copilot_config('student');
    }

    /**
     * Clean up test environment.
     */
    protected function tearDown(): void {
        test_courses::cleanup();
        parent::tearDown();
    }

    /**
     * Assert that web service result has expected structure.
     *
     * @param array $result Web service result.
     * @param array $expectedkeys Expected keys in the result.
     */
    protected function assert_web_service_result(array $result, array $expectedkeys): void {
        $this->assertIsArray($result);

        foreach ($expectedkeys as $key) {
            $this->assertArrayHasKey($key, $result, "Expected key '$key' not found in web service result");
        }
    }

    /**
     * Assert that external function parameters are properly defined.
     *
     * @param external_function_parameters $parameters Parameters object.
     * @param array $expectedparams Expected parameter names.
     */
    protected function assert_external_parameters(external_function_parameters $parameters, array $expectedparams): void {
        $this->assertInstanceOf(external_function_parameters::class, $parameters);

        $params = $parameters->keys;
        foreach ($expectedparams as $paramname) {
            $this->assertArrayHasKey($paramname, $params, "Expected parameter '$paramname' not found");
        }
    }

    /**
     * Assert that external function return structure is properly defined.
     *
     * @param mixed $returns Return structure.
     * @param string $expectedtype Expected type of return structure.
     */
    protected function assert_external_returns($returns, string $expectedtype): void {
        switch ($expectedtype) {
            case 'single':
                $this->assertInstanceOf(external_single_structure::class, $returns);
                break;
            case 'multiple':
                $this->assertInstanceOf(\external_multiple_structure::class, $returns);
                break;
            case 'value':
                $this->assertInstanceOf(\external_value::class, $returns);
                break;
            default:
                $this->fail("Unknown expected return type: $expectedtype");
        }
    }

    /**
     * Create a test assignment in the course.
     *
     * @param array $overrides Assignment properties to override.
     * @return stdClass Assignment object.
     */
    protected function create_test_assignment(array $overrides = []): stdClass {
        return test_courses::create_test_assignment($this->course, $overrides);
    }

    /**
     * Create a test forum in the course.
     *
     * @param array $overrides Forum properties to override.
     * @return stdClass Forum object.
     */
    protected function create_test_forum(array $overrides = []): stdClass {
        return test_courses::create_test_forum($this->course, $overrides);
    }

    /**
     * Get OAuth client options for testing.
     *
     * @return array OAuth client options.
     */
    protected function get_oauth_client_options(): array {
        return [$this->oauthclient->id => $this->oauthclient->client_id];
    }

    /**
     * Set user as teacher.
     */
    protected function set_user_as_teacher(): void {
        $this->setUser($this->teacher);
    }

    /**
     * Set user as student.
     */
    protected function set_user_as_student(): void {
        $this->setUser($this->student);
    }

    /**
     * Enable web services for testing.
     */
    protected function enable_web_services(): void {
        global $DB;

        set_config('enablewebservices', 1);
        set_config('webserviceprotocols', 'restful');

        // Create the Copilot web service if it doesn't exist.
        $webservicemanager = new \webservice();

        try {
            $service = $webservicemanager->get_external_service_by_shortname('copilot_webservices');
        } catch (\dml_missing_record_exception $e) {
            // Create the service.
            $service = new stdClass();
            $service->name = 'Microsoft 365 Copilot Web Services';
            $service->shortname = 'copilot_webservices';
            $service->enabled = 1;
            $service->restrictedusers = 0;
            $service->component = 'local_copilot';
            $service->timecreated = time();
            $service->timemodified = time();
            $service->downloadfiles = 0;
            $service->uploadfiles = 0;

            $service->id = $DB->insert_record('external_services', $service);
        }

        // Ensure the service is enabled.
        if (!$service->enabled) {
            $service->enabled = 1;
            $DB->update_record('external_services', $service);
        }
    }

    /**
     * Grant web service capabilities to authenticated users.
     */
    protected function grant_web_service_capabilities(): void {
        global $DB;

        $authenticatedrole = $DB->get_record('role', ['shortname' => 'user']);
        if (!$authenticatedrole) {
            return;
        }

        set_config('defaultuserroleid', $authenticatedrole->id);

        $systemcontext = \context_system::instance();

        // Grant required capabilities.
        assign_capability('moodle/webservice:createtoken', CAP_ALLOW, $authenticatedrole->id, $systemcontext);
        assign_capability('webservice/restful:use', CAP_ALLOW, $authenticatedrole->id, $systemcontext);

        // Refresh capabilities.
        accesslib_clear_all_caches_for_unit_testing();
    }
}
