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
 * Plugin configurations.
 *
 * @package local_copilot
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2024 onwards Microsoft, Inc. (http://microsoft.com/)
 */

use local_copilot\adminsetting\check_settings;
use local_copilot\utils;

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    // Add a section for the plugin configurations in the "Local plugins" section.
    $ADMIN->add('localplugins', new admin_category('local_copilot', get_string('pluginname', 'local_copilot')));

    // Add plugin configuration page.
    $settings = new admin_settingpage('local_copilot_basic_setting', get_string('settings_basic_settings', 'local_copilot'));
    $ADMIN->add('local_copilot', $settings);

    // Feature description.
    $settings->add(new admin_setting_heading('local_copilot/description', '',
        get_string('settings_feature_description', 'local_copilot')));

    // Basic settings section.
    $settings->add(new admin_setting_heading('local_copilot/basic_settings', get_string('settings_basic_settings', 'local_copilot'),
        get_string('settings_basic_settings_desc', 'local_copilot')));

    $settings->add(new admin_setting_configtext('local_copilot/paginationlimit',
        get_string('paginationlimit', 'local_copilot'),
        get_string('paginationlimit_desc', 'local_copilot'), 10, PARAM_INT));

    // Button to check settings.
    $oauthclientconfigurationurl = new moodle_url('/local/oauth2/manage_oauth_clients.php');
    $settings->add(new check_settings('local_copilot/check_settings',
        get_string('settings_check_settings', 'local_copilot'),
        get_string('settings_check_settings_desc', 'local_copilot', $oauthclientconfigurationurl->out()), null));

    if (utils::is_basic_configuration_complete()) {
        // Copilot OAuth client IDs.
        $oauthclientoptions = utils::get_oauth_client_options();
        if (count($oauthclientoptions) > 1) {
            $oauthclientoptionscopy = $oauthclientoptions;
            unset($oauthclientoptionscopy[0]);
            $settings->add(new admin_setting_configmultiselect('local_copilot/oauth_client_ids',
                get_string('settings_oauth_client_ids', 'local_copilot'),
                get_string('settings_oauth_client_ids_desc', 'local_copilot', $oauthclientconfigurationurl->out()),
                null,
                $oauthclientoptionscopy));
        }

        // Teacher OAuth client ID.
        $settings->add(new admin_setting_configselect('local_copilot/teacher_oauth_client_id',
            get_string('settings_teacher_oauth_client_id', 'local_copilot'),
            get_string('settings_teacher_oauth_client_id_desc', 'local_copilot', $oauthclientconfigurationurl->out()),
            null,
            $oauthclientoptions));

        // Student OAuth client ID.
        $settings->add(new admin_setting_configselect('local_copilot/student_oauth_client_id',
            get_string('settings_student_oauth_client_id', 'local_copilot'),
            get_string('settings_student_oauth_client_id_desc', 'local_copilot', $oauthclientconfigurationurl->out()),
            null,
            $oauthclientoptions));

        if (get_config('local_copilot', 'teacher_oauth_client_id') && get_config('local_copilot', 'access_token_timeout')) {
            // Teacher agent page.
            $ADMIN->add('local_copilot', new admin_externalpage('local_copilot/configure_teacher_agent',
                get_string('settings_configure_teacher_agent', 'local_copilot'),
                new moodle_url('/local/copilot/configure_agent.php', ['role' => 'teacher'])));
        }
        if (get_config('local_copilot', 'student_oauth_client_id') && get_config('local_copilot', 'access_token_timeout')) {
            // Student agent page.
            $ADMIN->add('local_copilot', new admin_externalpage('local_copilot/configure_student_agent',
                get_string('settings_configure_student_agent', 'local_copilot'),
                new moodle_url('/local/copilot/configure_agent.php', ['role' => 'student'])));
        }
    }
}
