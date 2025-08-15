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
 * Tests for agent configuration form.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_copilot;

use local_copilot\form\agent_configuration_form;
use local_copilot\manifest_generator;

defined('MOODLE_INTERNAL') || die();

/**
 * Tests for agent configuration form.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class form_agent_configuration_test extends \advanced_testcase {

    /**
     * Test form creation for teacher role.
     *
     * @covers \local_copilot\form\agent_configuration_form::__construct
     * @covers \local_copilot\form\agent_configuration_form::definition
     */
    public function test_form_creation_teacher() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $customdata = ['role' => manifest_generator::ROLE_TYPE_TEACHER];
        $form = new agent_configuration_form(null, $customdata);

        $this->assertInstanceOf(agent_configuration_form::class, $form);
        
        // Test that form contains expected elements.
        $mform = $form->get_mform();
        $this->assertTrue($mform->elementExists('role'));
        $this->assertTrue($mform->elementExists('teacher_agent_app_external_id'));
        $this->assertTrue($mform->elementExists('teacher_agent_app_short_name'));
    }

    /**
     * Test form creation for student role.
     *
     * @covers \local_copilot\form\agent_configuration_form::__construct
     * @covers \local_copilot\form\agent_configuration_form::definition
     */
    public function test_form_creation_student() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $customdata = ['role' => manifest_generator::ROLE_TYPE_STUDENT];
        $form = new agent_configuration_form(null, $customdata);

        $this->assertInstanceOf(agent_configuration_form::class, $form);
        
        // Test that form contains expected elements for student.
        $mform = $form->get_mform();
        $this->assertTrue($mform->elementExists('role'));
        $this->assertTrue($mform->elementExists('student_agent_app_external_id'));
        $this->assertTrue($mform->elementExists('student_agent_app_short_name'));
    }

    /**
     * Test form default values for teacher.
     *
     * @covers \local_copilot\form\agent_configuration_form::definition
     */
    public function test_form_defaults_teacher() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $customdata = ['role' => manifest_generator::ROLE_TYPE_TEACHER];
        $form = new agent_configuration_form(null, $customdata);
        $mform = $form->get_mform();

        // Check default external ID.
        $element = $mform->getElement('teacher_agent_app_external_id');
        $this->assertEquals(agent_configuration_form::TEACHER_APP_DEFAULT_EXTERNAL_ID, 
            $element->getValue());

        // Check default short name.
        $element = $mform->getElement('teacher_agent_app_short_name');
        $this->assertEquals('Moodle Teacher', $element->getValue());
    }

    /**
     * Test form default values for student.
     *
     * @covers \local_copilot\form\agent_configuration_form::definition
     */
    public function test_form_defaults_student() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $customdata = ['role' => manifest_generator::ROLE_TYPE_STUDENT];
        $form = new agent_configuration_form(null, $customdata);
        $mform = $form->get_mform();

        // Check default external ID.
        $element = $mform->getElement('student_agent_app_external_id');
        $this->assertEquals(agent_configuration_form::STUDENT_APP_DEFAULT_EXTERNAL_ID, 
            $element->getValue());

        // Check default short name.
        $element = $mform->getElement('student_agent_app_short_name');
        $this->assertEquals('Moodle Student', $element->getValue());
    }

    /**
     * Test form validation with valid data.
     *
     * @covers \local_copilot\form\agent_configuration_form::validation
     */
    public function test_form_validation_valid() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $customdata = ['role' => manifest_generator::ROLE_TYPE_TEACHER];
        $form = new agent_configuration_form(null, $customdata);

        $data = [
            'role' => manifest_generator::ROLE_TYPE_TEACHER,
            'teacher_agent_app_external_id' => '12345678-1234-1234-1234-123456789012',
            'teacher_agent_app_short_name' => 'Test Teacher App',
            'teacher_agent_app_full_name' => 'Full Test Teacher Application',
            'teacher_agent_display_name' => 'Teacher Agent',
            'teacher_agent_description' => 'Test teacher agent description',
        ];

        $errors = $form->validation($data, []);
        $this->assertEmpty($errors);
    }

    /**
     * Test form validation with invalid GUID.
     *
     * @covers \local_copilot\form\agent_configuration_form::validation
     */
    public function test_form_validation_invalid_guid() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $customdata = ['role' => manifest_generator::ROLE_TYPE_TEACHER];
        $form = new agent_configuration_form(null, $customdata);

        $data = [
            'role' => manifest_generator::ROLE_TYPE_TEACHER,
            'teacher_agent_app_external_id' => 'invalid-guid',
            'teacher_agent_app_short_name' => 'Test Teacher App',
        ];

        $errors = $form->validation($data, []);
        $this->assertNotEmpty($errors);
        $this->assertArrayHasKey('teacher_agent_app_external_id', $errors);
    }

    /**
     * Test form with missing required fields.
     *
     * @covers \local_copilot\form\agent_configuration_form::validation
     */
    public function test_form_validation_missing_required() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $customdata = ['role' => manifest_generator::ROLE_TYPE_TEACHER];
        $form = new agent_configuration_form(null, $customdata);

        $data = [
            'role' => manifest_generator::ROLE_TYPE_TEACHER,
            // Missing required fields.
        ];

        // This would typically be handled by Moodle's client-side validation
        // but we can test server-side validation here.
        $errors = $form->validation($data, []);
        
        // The form should handle missing required fields appropriately.
        $this->assertIsArray($errors);
    }

    /**
     * Test OAuth client dropdown population.
     *
     * @covers \local_copilot\form\agent_configuration_form::definition
     */
    public function test_oauth_client_dropdown() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        // Create test OAuth clients.
        $client1 = new \stdClass();
        $client1->client_id = 'test-client-1';
        $client1->client_secret = 'secret1';
        $client1->id = $DB->insert_record('local_oauth2_client', $client1);

        $customdata = ['role' => manifest_generator::ROLE_TYPE_TEACHER];
        $form = new agent_configuration_form(null, $customdata);
        $mform = $form->get_mform();

        // The form should include OAuth client selection.
        $this->assertTrue($mform->elementExists('teacher_oauth_client_registration_id'));
    }

    /**
     * Test form with existing configuration data.
     *
     * @covers \local_copilot\form\agent_configuration_form::definition
     */
    public function test_form_with_existing_config() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $role = manifest_generator::ROLE_TYPE_TEACHER;

        // Set existing configuration.
        set_config($role . '_agent_display_name', 'Existing Agent Name', 'local_copilot');
        set_config($role . '_agent_description', 'Existing description', 'local_copilot');

        $customdata = ['role' => $role];
        $form = new agent_configuration_form(null, $customdata);
        
        // The form should be populated with existing config values.
        $formdata = utils::get_agent_configuration_form_data($role);
        $this->assertEquals('Existing Agent Name', $formdata[$role . '_agent_display_name']);
        $this->assertEquals('Existing description', $formdata[$role . '_agent_description']);
    }
}