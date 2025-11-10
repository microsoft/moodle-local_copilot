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
 * Test fixtures for creating test courses and data.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_copilot\tests\fixtures;

use phpunit_util;
use stdClass;

/**
 * Test fixtures class for local_copilot tests.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class test_courses {
    /**
     * Create a standard test course with common settings.
     *
     * @param array $overrides Additional course properties to override defaults.
     * @return stdClass Course object.
     */
    public static function create_test_course(array $overrides = []): stdClass {
        global $CFG;

        $generator = phpunit_util::get_data_generator();

        $defaults = [
            'fullname' => 'Test Course for Copilot',
            'shortname' => 'TESTCOPILOT' . time() . rand(1000, 9999),
            'summary' => 'A test course for Microsoft 365 Copilot integration testing',
            'summaryformat' => FORMAT_HTML,
            'format' => 'topics',
            'numsections' => 5,
            'enablecompletion' => 1,
            'showgrades' => 1,
        ];

        $coursedata = array_merge($defaults, $overrides);
        return $generator->create_course($coursedata);
    }

    /**
     * Create a teacher user with appropriate role and enrollment.
     *
     * @param stdClass $course Course to enrol the teacher in.
     * @param array $useroverrides User properties to override defaults.
     * @return stdClass User object.
     */
    public static function create_teacher_user(stdClass $course, array $useroverrides = []): stdClass {
        global $DB;

        $generator = phpunit_util::get_data_generator();

        $defaults = [
            'firstname' => 'Test',
            'lastname' => 'Teacher',
            'username' => 'testteacher_' . time() . '_' . rand(1000, 9999),
            'email' => 'teacher@copilottest.local',
        ];

        $userdata = array_merge($defaults, $useroverrides);
        $user = $generator->create_user($userdata);

        // Enrol as editing teacher.
        $teacherrole = $DB->get_record('role', ['shortname' => 'editingteacher']);
        $generator->enrol_user($user->id, $course->id, $teacherrole->id);

        return $user;
    }

    /**
     * Create a student user with appropriate enrollment.
     *
     * @param stdClass $course Course to enrol the student in.
     * @param array $useroverrides User properties to override defaults.
     * @return stdClass User object.
     */
    public static function create_student_user(stdClass $course, array $useroverrides = []): stdClass {
        global $DB;

        $generator = phpunit_util::get_data_generator();

        $defaults = [
            'firstname' => 'Test',
            'lastname' => 'Student',
            'username' => 'teststudent_' . time() . '_' . rand(1000, 9999),
            'email' => 'student@copilottest.local',
        ];

        $userdata = array_merge($defaults, $useroverrides);
        $user = $generator->create_user($userdata);

        // Enrol as student.
        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        $generator->enrol_user($user->id, $course->id, $studentrole->id);

        return $user;
    }

    /**
     * Create a test assignment in the course.
     *
     * NOTE: When creating activities with availability restrictions, ensure the JSON structure
     * includes the required root-level fields:
     * - For AND ('&') or NOT_OR ('!|') operators: include 'showc' array with boolean values
     *   corresponding to each condition in the 'c' array.
     * - For OR ('|') or NOT_AND ('!&') operators: include 'show' boolean value.
     *
     * Example with AND operator:
     * 'availability' => json_encode([
     *     'op' => '&',
     *     'c' => [['type' => 'date', 'd' => '>=', 't' => time() + 86400]],
     *     'showc' => [true]  // Required for AND operator
     * ])
     *
     * Example with OR operator:
     * 'availability' => json_encode([
     *     'op' => '|',
     *     'c' => [['type' => 'date', 'd' => '>=', 't' => time() + 86400]],
     *     'show' => true  // Required for OR operator
     * ])
     *
     * @param stdClass $course Course to create the assignment in.
     * @param array $assignoverrides Assignment properties to override defaults.
     * @return stdClass Assignment object.
     */
    public static function create_test_assignment(stdClass $course, array $assignoverrides = []): stdClass {
        $generator = phpunit_util::get_data_generator();
        $assigngenerator = $generator->get_plugin_generator('mod_assign');

        $defaults = [
            'course' => $course->id,
            'name' => 'Test Assignment for Copilot',
            'intro' => 'This is a test assignment created for Copilot testing purposes.',
            'introformat' => FORMAT_HTML,
            'grade' => 100,
            'duedate' => time() + (7 * 24 * 60 * 60), // Due in 1 week.
            'allowsubmissionsfromdate' => time(),
        ];

        $assigndata = array_merge($defaults, $assignoverrides);
        return $assigngenerator->create_instance($assigndata);
    }

    /**
     * Create a test forum in the course.
     *
     * @param stdClass $course Course to create the forum in.
     * @param array $forumoverrides Forum properties to override defaults.
     * @return stdClass Forum object.
     */
    public static function create_test_forum(stdClass $course, array $forumoverrides = []): stdClass {
        $generator = phpunit_util::get_data_generator();
        $forumgenerator = $generator->get_plugin_generator('mod_forum');

        $defaults = [
            'course' => $course->id,
            'name' => 'Test Forum for Copilot',
            'intro' => 'This is a test forum created for Copilot testing purposes.',
            'introformat' => FORMAT_HTML,
            'type' => 'general',
        ];

        $forumdata = array_merge($defaults, $forumoverrides);
        return $forumgenerator->create_instance($forumdata);
    }

    /**
     * Set up OAuth2 client for testing.
     *
     * @return stdClass|null OAuth2 client record or null if table doesn't exist.
     */
    public static function create_oauth_client(): ?stdClass {
        global $DB;

        // Check if the OAuth2 client table exists.
        $dbman = $DB->get_manager();
        if (!$dbman->table_exists('local_oauth2_client')) {
            // Return a mock client object if the table doesn't exist.
            $client = new stdClass();
            $client->id = 1;
            $client->client_id = 'test-copilot-client-' . time();
            $client->client_secret = 'test-secret-' . rand(100000, 999999);
            $client->redirect_uris = 'https://test.local/redirect';
            $client->grant_types = 'authorization_code,refresh_token';
            $client->scope = 'openid profile email';
            $client->user_id = 0;
            $client->timecreated = time();
            $client->timemodified = time();
            return $client;
        }

        $client = new stdClass();
        $client->client_id = 'test-copilot-client-' . time();
        $client->client_secret = 'test-secret-' . rand(100000, 999999);
        $client->redirect_uris = 'https://test.local/redirect';
        $client->grant_types = 'authorization_code,refresh_token';
        $client->scope = 'openid profile email';
        $client->user_id = 0;
        $client->timecreated = time();
        $client->timemodified = time();

        $client->id = $DB->insert_record('local_oauth2_client', $client);

        return $client;
    }

    /**
     * Set up basic Copilot configuration for testing.
     *
     * @param string $role The role type (teacher/student).
     * @return void
     */
    public static function setup_copilot_config(string $role): void {
        // Enable web services.
        set_config('enablewebservices', 1);
        set_config('webserviceprotocols', 'restful');

        // Set basic agent configurations.
        $configs = [
            'agent_app_external_id' => 'test-' . $role . '-app-id',
            'agent_app_short_name' => 'Test ' . ucfirst($role) . ' App',
            'agent_app_full_name' => 'Test ' . ucfirst($role) . ' Application',
            'agent_display_name' => ucfirst($role) . ' Copilot Agent',
            'agent_description' => 'Test Copilot agent for ' . $role,
            'agent_instructions' => 'Test instructions for ' . $role . ' agent',
            'oauth_client_registration_id' => 'test-oauth-client',
            'agent_plugin_name' => 'Test Plugin',
            'agent_plugin_description' => 'Test plugin description',
        ];

        foreach ($configs as $key => $value) {
            set_config($role . '_' . $key, $value, 'local_copilot');
        }

        // Set app version.
        set_config('app_version', '1.0.0', 'local_copilot');

        // Set pagination limit.
        set_config('paginationlimit', 10, 'local_copilot');
    }

    /**
     * Clean up test data and configurations.
     *
     * @return void
     */
    public static function cleanup(): void {
        global $DB;

        // Remove test OAuth clients only if table exists.
        $dbman = $DB->get_manager();
        if ($dbman->table_exists('local_oauth2_client')) {
            $DB->delete_records_select(
                'local_oauth2_client',
                $DB->sql_like('client_id', ':pattern'),
                ['pattern' => 'test-copilot-client%']
            );
        }

        // Remove test configurations.
        $configs = $DB->get_records('config_plugins', ['plugin' => 'local_copilot']);
        foreach ($configs as $config) {
            if (strpos($config->name, 'test') === 0) {
                $DB->delete_records('config_plugins', ['id' => $config->id]);
            }
        }
    }
}
