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
 * Tests for privacy provider.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_copilot;

use core_privacy\local\metadata\collection;
use core_privacy\local\metadata\null_provider;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use core_privacy\tests\provider_testcase;
use local_copilot\privacy\provider;

/**
 * Tests for privacy provider.
 *
 * @package local_copilot
 * @category test
 * @copyright 2024 Microsoft
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class provider_test extends provider_testcase {
    /**
     * Test get_metadata returns correct metadata.
     *
     * @covers \local_copilot\privacy\provider::get_metadata
     */
    public function test_get_metadata(): void {
        $collection = new collection('local_copilot');
        $newcollection = provider::get_metadata($collection);

        $this->assertInstanceOf(collection::class, $newcollection);
        $this->assertCount(0, $newcollection->get_collection()); // Plugin doesn't store user data.
    }

    /**
     * Test get_contexts_for_userid returns empty contextlist.
     *
     * @covers \local_copilot\privacy\provider::get_contexts_for_userid
     */
    public function test_get_contexts_for_userid(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $contextlist = provider::get_contexts_for_userid($user->id);

        $this->assertInstanceOf(contextlist::class, $contextlist);
        $this->assertEmpty($contextlist->get_contexts());
    }

    /**
     * Test export_user_data does not export any data.
     *
     * @covers \local_copilot\privacy\provider::export_user_data
     */
    public function test_export_user_data(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $contextlist = new approved_contextlist($user, 'local_copilot', []);

        provider::export_user_data($contextlist);

        // Since the plugin doesn't store user data, nothing should be exported.
        $writer = writer::with_context(\context_system::instance());
        $this->assertFalse($writer->has_any_data());
    }

    /**
     * Test delete_data_for_all_users_in_context.
     *
     * @covers \local_copilot\privacy\provider::delete_data_for_all_users_in_context
     */
    public function test_delete_data_for_all_users_in_context(): void {
        $this->resetAfterTest();

        // This should not throw any exceptions since no user data is stored.
        provider::delete_data_for_all_users_in_context(\context_system::instance());

        // Test passes if no exception is thrown.
        $this->assertTrue(true);
    }

    /**
     * Test delete_data_for_user.
     *
     * @covers \local_copilot\privacy\provider::delete_data_for_user
     */
    public function test_delete_data_for_user(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $contextlist = new approved_contextlist($user, 'local_copilot', []);

        // This should not throw any exceptions since no user data is stored.
        provider::delete_data_for_user($contextlist);

        // Test passes if no exception is thrown.
        $this->assertTrue(true);
    }

    /**
     * Test get_users_in_context returns empty userlist.
     *
     * @covers \local_copilot\privacy\provider::get_users_in_context
     */
    public function test_get_users_in_context(): void {
        $this->resetAfterTest();

        $userlist = new userlist(\context_system::instance(), 'local_copilot');
        provider::get_users_in_context($userlist);

        $this->assertEmpty($userlist->get_userids());
    }

    /**
     * Test delete_data_for_users.
     *
     * @covers \local_copilot\privacy\provider::delete_data_for_users
     */
    public function test_delete_data_for_users(): void {
        $this->resetAfterTest();

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        $approveduserlist = new approved_userlist(
            \context_system::instance(),
            'local_copilot',
            [$user1->id, $user2->id]
        );

        // This should not throw any exceptions since no user data is stored.
        provider::delete_data_for_users($approveduserlist);

        // Test passes if no exception is thrown.
        $this->assertTrue(true);
    }

    /**
     * Test that plugin is correctly identified as not storing user data.
     *
     * @covers \local_copilot\privacy\provider
     */
    public function test_plugin_stores_no_user_data(): void {
        // Test that the provider implements the null_provider interface
        // indicating it doesn't store user data.
        $this->assertInstanceOf(
            null_provider::class,
            new provider()
        );
    }
}
