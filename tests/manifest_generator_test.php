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
 * Tests for manifest generator.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_copilot;

use local_copilot\manifest_generator;

defined('MOODLE_INTERNAL') || die();

/**
 * Tests for manifest generator.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manifest_generator_test extends \advanced_testcase {

    /**
     * Test manifest generation for teacher role.
     *
     * @covers \local_copilot\manifest_generator::generate
     */
    public function test_generate_teacher_manifest() {
        $this->resetAfterTest();
        $this->setAdminUser();

        // Set required configuration.
        $role = manifest_generator::ROLE_TYPE_TEACHER;
        $this->set_required_config($role);

        $generator = new manifest_generator($role);
        $manifest = $generator->generate();

        $this->assertIsArray($manifest);
        $this->assertArrayHasKey('$schema', $manifest);
        $this->assertArrayHasKey('manifestVersion', $manifest);
        $this->assertArrayHasKey('name', $manifest);
        $this->assertArrayHasKey('id', $manifest);
        $this->assertArrayHasKey('copilotAgents', $manifest);
        
        // Test copilot agents structure.
        $this->assertArrayHasKey('agents', $manifest['copilotAgents']);
        $this->assertIsArray($manifest['copilotAgents']['agents']);
        $this->assertCount(1, $manifest['copilotAgents']['agents']);
        
        $agent = $manifest['copilotAgents']['agents'][0];
        $this->assertArrayHasKey('id', $agent);
        $this->assertArrayHasKey('name', $agent);
        $this->assertArrayHasKey('description', $agent);
    }

    /**
     * Test manifest generation for student role.
     *
     * @covers \local_copilot\manifest_generator::generate
     */
    public function test_generate_student_manifest() {
        $this->resetAfterTest();
        $this->setAdminUser();

        // Set required configuration.
        $role = manifest_generator::ROLE_TYPE_STUDENT;
        $this->set_required_config($role);

        $generator = new manifest_generator($role);
        $manifest = $generator->generate();

        $this->assertIsArray($manifest);
        $this->assertArrayHasKey('copilotAgents', $manifest);
        
        $agent = $manifest['copilotAgents']['agents'][0];
        $this->assertArrayHasKey('id', $agent);
        $this->assertEquals('student_test_id', $agent['id']);
    }

    /**
     * Test manifest generation without required configuration.
     *
     * @covers \local_copilot\manifest_generator::generate
     */
    public function test_generate_manifest_missing_config() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $generator = new manifest_generator(manifest_generator::ROLE_TYPE_TEACHER);
        
        $this->expectException(\coding_exception::class);
        $generator->generate();
    }

    /**
     * Test manifest validation.
     *
     * @covers \local_copilot\manifest_generator::validate_manifest
     */
    public function test_validate_manifest() {
        $this->resetAfterTest();

        $validmanifest = [
            '$schema' => 'https://example.com/schema',
            'manifestVersion' => '1.0',
            'name' => ['default' => 'Test App'],
            'id' => 'test-app-id',
            'copilotAgents' => [
                'agents' => [
                    [
                        'id' => 'agent-id',
                        'name' => 'Test Agent',
                        'description' => 'Test Description',
                    ]
                ]
            ]
        ];

        $generator = new manifest_generator(manifest_generator::ROLE_TYPE_TEACHER);
        $result = $generator->validate_manifest($validmanifest);
        $this->assertTrue($result);
    }

    /**
     * Test role constants.
     *
     * @covers \local_copilot\manifest_generator
     */
    public function test_role_constants() {
        $this->assertEquals('teacher', manifest_generator::ROLE_TYPE_TEACHER);
        $this->assertEquals('student', manifest_generator::ROLE_TYPE_STUDENT);
    }

    /**
     * Test icon handling in manifest.
     *
     * @covers \local_copilot\manifest_generator::generate
     */
    public function test_manifest_icons() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $role = manifest_generator::ROLE_TYPE_TEACHER;
        $this->set_required_config($role);

        $generator = new manifest_generator($role);
        $manifest = $generator->generate();

        $this->assertArrayHasKey('icons', $manifest);
        $this->assertIsArray($manifest['icons']);
        
        // Should have color and outline icons.
        $iconTypes = array_column($manifest['icons'], 'purpose');
        $this->assertContains('color', $iconTypes);
        $this->assertContains('outline', $iconTypes);
    }

    /**
     * Test capabilities in manifest.
     *
     * @covers \local_copilot\manifest_generator::generate
     */
    public function test_manifest_capabilities() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $role = manifest_generator::ROLE_TYPE_TEACHER;
        $this->set_required_config($role);
        
        // Set some capability configurations.
        set_config($role . '_agent_capability_web_search', 1, 'local_copilot');
        set_config($role . '_agent_capability_image_generator', 1, 'local_copilot');

        $generator = new manifest_generator($role);
        $manifest = $generator->generate();

        $agent = $manifest['copilotAgents']['agents'][0];
        $this->assertArrayHasKey('capabilities', $agent);
        
        $capabilities = $agent['capabilities'];
        $this->assertIsArray($capabilities);
        
        // Check for expected capabilities.
        $capabilityTypes = array_column($capabilities, 'name');
        $this->assertContains('WebSearch', $capabilityTypes);
        $this->assertContains('GraphicArt', $capabilityTypes);
    }

    /**
     * Helper method to set required configuration for testing.
     *
     * @param string $role
     */
    private function set_required_config(string $role): void {
        foreach (utils::APP_ROLE_CONFIGURATIONS as $config) {
            set_config($role . '_' . $config, $role . '_test_' . $config, 'local_copilot');
        }
        
        // Set specific values for known configs.
        set_config($role . '_agent_app_external_id', $role . '_test_id', 'local_copilot');
        set_config($role . '_agent_display_name', ucfirst($role) . ' Agent', 'local_copilot');
        set_config($role . '_agent_description', 'Test ' . $role . ' agent', 'local_copilot');
        set_config('app_version', '1.0.0', 'local_copilot');
    }
}