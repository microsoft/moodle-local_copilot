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
 * Tests for utils class.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_copilot;

use advanced_testcase;

/**
 * Tests for utils class.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class utils_test extends advanced_testcase {
    /**
     * Test is_guid method with valid GUIDs.
     *
     * @covers       \local_copilot\utils::is_guid
     * @dataProvider valid_guid_provider
     * @param string $guid
     * @param bool $requirehyphens
     * @param bool $expected
     */
    public function test_is_guid_valid($guid, $requirehyphens, $expected): void {
        $result = utils::is_guid($guid, $requirehyphens);
        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider for valid GUID tests.
     *
     * @return array
     */
    public static function valid_guid_provider(): array {
        return [
            // Valid GUIDs with hyphens.
            ['550e8400-e29b-41d4-a716-446655440000', false, true],
            ['550e8400-e29b-41d4-a716-446655440000', true, true],
            // Valid GUID without hyphens.
            ['550e8400e29b41d4a716446655440000', false, true],
            ['550e8400e29b41d4a716446655440000', true, false], // Requires hyphens but none provided.
            // Invalid formats.
            ['invalid-guid', false, false],
            ['550e8400-e29b-41d4-a716', false, false], // Too short.
            ['550e8400-e29b-41d4-a716-446655440000-extra', false, false], // Too long.
            ['', false, false], // Empty string.
            ['zzz', false, false], // Invalid characters.
        ];
    }

    /**
     * Test is_guid method with invalid inputs.
     *
     * @covers \local_copilot\utils::is_guid
     */
    public function test_is_guid_invalid_types(): void {
        $this->assertFalse(utils::is_guid(123));
        $this->assertFalse(utils::is_guid(null));
        $this->assertFalse(utils::is_guid([]));
        $this->assertFalse(utils::is_guid(true));
    }

    /**
     * Test SharePoint/OneDrive URL validation.
     *
     * @covers       \local_copilot\utils::is_sharepoint_onedrive_url
     * @dataProvider sharepoint_onedrive_url_provider
     * @param string $url
     * @param bool $expectedvalid
     * @param string|null $expectedtype
     */
    public function test_is_sharepoint_onedrive_url($url, $expectedvalid, $expectedtype): void {
        $result = utils::is_sharepoint_onedrive_url($url);
        $this->assertEquals($expectedvalid, $result['is_valid']);
        $this->assertEquals($expectedtype, $result['type']);
    }

    /**
     * Data provider for SharePoint/OneDrive URL tests.
     *
     * @return array
     */
    public static function sharepoint_onedrive_url_provider(): array {
        return [
            // Valid SharePoint URLs.
            ['https://company.sharepoint.com/sites/teamsite', true, 'sharepoint'],
            ['https://company.sharepoint.com/teams/project', true, 'sharepoint'],
            ['https://company.sharepoint-df.com/sites/test', true, 'sharepoint'],
            ['https://company.sharepoint.com/_layouts/15/start.aspx', true, 'sharepoint'],

            // Valid OneDrive URLs.
            ['https://company-my.sharepoint.com/personal/user', true, 'onedrive'],
            ['https://onedrive.live.com/redir', true, 'onedrive'],
            ['https://d.docs.live.net/personal/user/_layouts/15/onedrive.aspx', true, 'onedrive'],

            // Invalid URLs.
            ['https://google.com', false, null],
            ['https://example.com', false, null],
            ['invalid-url', false, null],
            ['', false, null],
            [null, false, null],
        ];
    }

    /**
     * Test OneDrive URL detection.
     *
     * @covers       \local_copilot\utils::is_onedrive_url
     * @dataProvider onedrive_url_provider
     * @param string $host
     * @param string $path
     * @param bool $expected
     */
    public function test_is_onedrive_url($host, $path, $expected): void {
        $result = utils::is_onedrive_url($host, $path);
        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider for OneDrive URL tests.
     *
     * @return array
     */
    public static function onedrive_url_provider(): array {
        return [
            ['company-my.sharepoint.com', '/personal/user', true],
            ['onedrive.live.com', '/', true],
            ['d.docs.live.net', '/personal/user/_layouts/15/onedrive.aspx', true],
            ['example.com', '/some/path', false],
            ['company.sharepoint.com', '/sites/team', false],
        ];
    }

    /**
     * Test SharePoint URL detection.
     *
     * @covers       \local_copilot\utils::is_sharepoint_url
     * @dataProvider sharepoint_url_provider
     * @param string $host
     * @param string $path
     * @param bool $expected
     */
    public function test_is_sharepoint_url($host, $path, $expected): void {
        $result = utils::is_sharepoint_url($host, $path);
        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider for SharePoint URL tests.
     *
     * @return array
     */
    public static function sharepoint_url_provider(): array {
        return [
            ['company.sharepoint.com', '/sites/team', true],
            ['company.sharepoint-df.com', '/teams/project', true],
            ['company.sharepoint.com', '/_layouts/15/start.aspx', true],
            ['example.com', '/some/path', false],
            ['company-my.sharepoint.com', '/personal/user', false], // This would be OneDrive.
        ];
    }

    /**
     * Test basic configuration check.
     *
     * @covers \local_copilot\utils::is_basic_configuration_complete
     */
    public function test_is_basic_configuration_complete(): void {
        global $CFG, $DB;
        $this->resetAfterTest();

        // Initially should fail (web services not enabled).
        $this->assertFalse(utils::is_basic_configuration_complete());

        // Enable web services.
        set_config('enablewebservices', 1);

        // Should still fail (RESTful protocol not enabled).
        $this->assertFalse(utils::is_basic_configuration_complete());

        // Mock RESTful protocol availability.
        set_config('webserviceprotocols', 'restful');

        // Create mock copilot web service.
        $service = new \stdClass();
        $service->name = 'Microsoft 365 Copilot Web Services';
        $service->shortname = 'copilot_webservices';
        $service->enabled = 1;
        $service->restrictedusers = 0;
        $service->component = 'local_copilot';
        $service->timecreated = time();
        $service->timemodified = time();
        $service->downloadfiles = 0;
        $service->uploadfiles = 0;
        $serviceid = $DB->insert_record('external_services', $service);

        // Set default user role ID to authenticated user role.
        $authenticatedrole = $DB->get_record('role', ['shortname' => 'user']);
        if ($authenticatedrole) {
            set_config('defaultuserroleid', $authenticatedrole->id);

            // Add required capabilities to authenticated user role.
            $systemcontext = \context_system::instance();
            assign_capability('moodle/webservice:createtoken', CAP_ALLOW, $authenticatedrole->id, $systemcontext);
            assign_capability('webservice/restful:use', CAP_ALLOW, $authenticatedrole->id, $systemcontext);

            // Now it should pass.
            $this->assertTrue(utils::is_basic_configuration_complete());
        }
    }

    /**
     * Test agent configuration completeness check.
     *
     * @covers \local_copilot\utils::is_agent_configured
     */
    public function test_is_agent_configured(): void {
        $this->resetAfterTest();

        $role = 'teacher';

        // Initially should be false (no config set).
        $this->assertFalse(utils::is_agent_configured($role));

        // Set all required configurations.
        foreach (utils::APP_ROLE_CONFIGURATIONS as $config) {
            set_config($role . '_' . $config, 'test_value', 'local_copilot');
        }

        // Now should be true.
        $this->assertTrue(utils::is_agent_configured($role));

        // Remove one configuration - should be false again.
        unset_config($role . '_' . utils::APP_ROLE_CONFIGURATIONS[0], 'local_copilot');
        $this->assertFalse(utils::is_agent_configured($role));
    }

    /**
     * Test OAuth client options retrieval.
     *
     * @covers \local_copilot\utils::get_oauth_client_options
     */
    public function test_get_oauth_client_options(): void {
        global $DB;
        $this->resetAfterTest();

        // Create mock OAuth client records.
        $client1 = new \stdClass();
        $client1->client_id = 'test-client-1';
        $client1->client_secret = 'secret1';
        $client1->id = $DB->insert_record('local_oauth2_client', $client1);

        $client2 = new \stdClass();
        $client2->client_id = 'test-client-2';
        $client2->client_secret = 'secret2';
        $client2->id = $DB->insert_record('local_oauth2_client', $client2);

        $options = utils::get_oauth_client_options();

        $this->assertIsArray($options);
        $this->assertCount(2, $options);
        $this->assertEquals('test-client-1', $options[$client1->id]);
        $this->assertEquals('test-client-2', $options[$client2->id]);
    }

    /**
     * Test agent configuration form data retrieval.
     *
     * @covers \local_copilot\utils::get_agent_configuration_form_data
     */
    public function test_get_agent_configuration_form_data(): void {
        $this->resetAfterTest();

        $role = 'teacher';

        // Set some test configurations.
        set_config($role . '_agent_display_name', 'Test Agent', 'local_copilot');
        set_config($role . '_agent_description', 'Test Description', 'local_copilot');

        $formdata = utils::get_agent_configuration_form_data($role);

        $this->assertIsArray($formdata);
        $this->assertArrayHasKey($role . '_agent_display_name', $formdata);
        $this->assertEquals('Test Agent', $formdata[$role . '_agent_display_name']);
        $this->assertArrayHasKey($role . '_agent_description', $formdata);
        $this->assertEquals('Test Description', $formdata[$role . '_agent_description']);
    }

    /**
     * Test agent configuration form data with invalid role.
     *
     * @covers \local_copilot\utils::get_agent_configuration_form_data
     */
    public function test_get_agent_configuration_form_data_invalid_role(): void {
        $this->resetAfterTest();

        $formdata = utils::get_agent_configuration_form_data('invalid_role');
        $this->assertIsArray($formdata);
        $this->assertEmpty($formdata);
    }
}
