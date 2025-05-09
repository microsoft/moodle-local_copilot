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
 * Plugin upgrade script.
 *
 * @package local_copilot
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2024 onwards Microsoft, Inc. (http://microsoft.com/)
 */

/**
 * Upgrade the copilot plugin.
 *
 * @param int $oldversion The version we are upgrading from.
 * @return bool
 */
function xmldb_local_copilot_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2024042123) {
        // Define table local_copilot_oauth_clients to be created.
        $table = new xmldb_table('local_copilot_oauth_clients');

        // Adding fields to table local_copilot_oauth_clients.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('client_id', XMLDB_TYPE_CHAR, '80', null, XMLDB_NOTNULL, null, null);
        $table->add_field('client_secret', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('redirect_uri', XMLDB_TYPE_CHAR, '1333', null, null, null, null);
        $table->add_field('scope', XMLDB_TYPE_CHAR, '1333', null, null, null, null);

        // Adding keys to table local_copilot_oauth_clients.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for local_copilot_oauth_clients.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Copilot savepoint reached.
        upgrade_plugin_savepoint(true, 2024042123, 'local', 'copilot');
    }

    if ($oldversion < 2024042124) {
        // Define table local_copilot_oauth_user_auth_scopes to be created.
        $table = new xmldb_table('local_copilot_oauth_user_auth_scopes');

        // Adding fields to table local_copilot_oauth_user_auth_scopes.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('client_id', XMLDB_TYPE_CHAR, '80', null, XMLDB_NOTNULL, null, null);
        $table->add_field('user_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('scope', XMLDB_TYPE_CHAR, '1333', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_copilot_oauth_user_auth_scopes.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for local_copilot_oauth_user_auth_scopes.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Copilot savepoint reached.
        upgrade_plugin_savepoint(true, 2024042124, 'local', 'copilot');
    }

    if ($oldversion < 2024042125) {
        // Define table local_copilot_oauth_access_token to be created.
        $table = new xmldb_table('local_copilot_oauth_access_token');

        // Adding fields to table local_copilot_oauth_access_token.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('access_token', XMLDB_TYPE_CHAR, '40', null, XMLDB_NOTNULL, null, null);
        $table->add_field('client_id', XMLDB_TYPE_CHAR, '80', null, XMLDB_NOTNULL, null, null);
        $table->add_field('user_id', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('expires', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('scope', XMLDB_TYPE_CHAR, '1333', null, null, null, null);

        // Adding keys to table local_copilot_oauth_access_token.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('access_token', XMLDB_KEY_UNIQUE, ['access_token']);

        // Conditionally launch create table for local_copilot_oauth_access_token.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_copilot_oauth_authorization_code to be created.
        $table = new xmldb_table('local_copilot_oauth_authorization_code');

        // Adding fields to table local_copilot_oauth_authorization_code.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('authorization_code', XMLDB_TYPE_CHAR, '40', null, XMLDB_NOTNULL, null, null);
        $table->add_field('client_id', XMLDB_TYPE_CHAR, '80', null, XMLDB_NOTNULL, null, null);
        $table->add_field('user_id', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('redirect_uri', XMLDB_TYPE_CHAR, '1333', null, null, null, null);
        $table->add_field('expires', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('scope', XMLDB_TYPE_CHAR, '1333', null, null, null, null);
        $table->add_field('id_token', XMLDB_TYPE_CHAR, '1000', null, null, null, null);

        // Adding keys to table local_copilot_oauth_authorization_code.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('authorization_code', XMLDB_KEY_UNIQUE, ['authorization_code']);

        // Conditionally launch create table for local_copilot_oauth_authorization_code.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_copilot_oauth_refresh_tokens to be created.
        $table = new xmldb_table('local_copilot_oauth_refresh_tokens');

        // Adding fields to table local_copilot_oauth_refresh_tokens.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('refresh_token', XMLDB_TYPE_CHAR, '40', null, XMLDB_NOTNULL, null, null);
        $table->add_field('client_id', XMLDB_TYPE_CHAR, '80', null, XMLDB_NOTNULL, null, null);
        $table->add_field('user_id', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('expires', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('scope', XMLDB_TYPE_CHAR, '1333', null, null, null, null);

        // Adding keys to table local_copilot_oauth_refresh_tokens.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('refresh_token', XMLDB_KEY_UNIQUE, ['refresh_token']);

        // Conditionally launch create table for local_copilot_oauth_refresh_tokens.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_copilot_oauth_scopes to be created.
        $table = new xmldb_table('local_copilot_oauth_scopes');

        // Adding fields to table local_copilot_oauth_scopes.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('scope', XMLDB_TYPE_CHAR, '80', null, XMLDB_NOTNULL, null, null);
        $table->add_field('is_default', XMLDB_TYPE_INTEGER, '1', null, null, null, null);

        // Adding keys to table local_copilot_oauth_scopes.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('scope', XMLDB_KEY_UNIQUE, ['scope']);

        // Conditionally launch create table for local_copilot_oauth_scopes.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_copilot_oauth_jwt to be created.
        $table = new xmldb_table('local_copilot_oauth_jwt');

        // Adding fields to table local_copilot_oauth_jwt.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('client_id', XMLDB_TYPE_CHAR, '80', null, XMLDB_NOTNULL, null, null);
        $table->add_field('subject', XMLDB_TYPE_CHAR, '80', null, null, null, null);
        $table->add_field('public_key', XMLDB_TYPE_CHAR, '1333', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_copilot_oauth_jwt.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for local_copilot_oauth_jwt.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_copilot_oauth_public_keys to be created.
        $table = new xmldb_table('local_copilot_oauth_public_keys');

        // Adding fields to table local_copilot_oauth_public_keys.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('client_id', XMLDB_TYPE_CHAR, '80', null, XMLDB_NOTNULL, null, null);
        $table->add_field('public_key', XMLDB_TYPE_CHAR, '1333', null, XMLDB_NOTNULL, null, null);
        $table->add_field('private_key', XMLDB_TYPE_CHAR, '1333', null, XMLDB_NOTNULL, null, null);
        $table->add_field('encryption_algorithm', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_copilot_oauth_public_keys.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for local_copilot_oauth_public_keys.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Copilot savepoint reached.
        upgrade_plugin_savepoint(true, 2024042125, 'local', 'copilot');
    }

    if ($oldversion < 2024042128) {
        // Define table local_copilot_oauth_access_token to be renamed to local_copilot_oauth_access_tokens.
        $table = new xmldb_table('local_copilot_oauth_access_token');
        $newtable = new xmldb_table('local_copilot_oauth_access_tokens');

        // Launch rename table for local_copilot_oauth_access_token.
        if ($dbman->table_exists($table)) {
            if (!$dbman->table_exists($newtable)) {
                $dbman->rename_table($table, 'local_copilot_oauth_access_tokens');
            } else {
                $dbman->drop_table($table);
            }
        }

        // Define table local_copilot_oauth_authorization_code to be renamed to local_copilot_oauth_authorization_codes.
        $table = new xmldb_table('local_copilot_oauth_authorization_code');
        $newtable = new xmldb_table('local_copilot_oauth_authorization_codes');

        // Launch rename table for local_copilot_oauth_authorization_code.
        if ($dbman->table_exists($table)) {
            if (!$dbman->table_exists($newtable)) {
                $dbman->rename_table($table, 'local_copilot_oauth_authorization_codes');
            } else {
                $dbman->drop_table($table);
            }
        }

        // Copilot savepoint reached.
        upgrade_plugin_savepoint(true, 2024042128, 'local', 'copilot');
    }

    if ($oldversion < 2024042147) {
        // Drop all OAuth tables.
        $oauthtablenames = [
            'local_copilot_oauth_clients',
            'local_copilot_oauth_user_auth_scopes',
            'local_copilot_oauth_access_tokens',
            'local_copilot_oauth_authorization_codes',
            'local_copilot_oauth_refresh_tokens',
            'local_copilot_oauth_scopes',
            'local_copilot_oauth_jwt',
            'local_copilot_oauth_public_keys',
        ];

        foreach ($oauthtablenames as $oauthtablename) {
            // Define table local_copilot_oauth_clients to be dropped.
            $table = new xmldb_table($oauthtablename);

            // Conditionally launch drop table for local_copilot_oauth_clients.
            if ($dbman->table_exists($table)) {
                $dbman->drop_table($table);
            }
        }

        // Copilot savepoint reached.
        upgrade_plugin_savepoint(true, 2024042147, 'local', 'copilot');
    }

    return true;
}
