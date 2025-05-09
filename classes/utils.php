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
 * Utility functions.
 *
 * @package local_copilot
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2024 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot;

use context_system;
use core_component;
use webservice;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/classes/component.php');
require_once($CFG->dirroot . '/webservice/lib.php');

/**
 * Utility functions.
 */
class utils {
    /**
     * @var array App role configurations.
     */
    const APP_ROLE_CONFIGURATIONS = [
        'agent_app_external_id',
        'agent_app_short_name',
        'agent_app_full_name',
        'agent_app_short_description',
        'agent_app_full_description',
        'agent_accent_color',
        'agent_display_name',
        'agent_description',
        'agent_instructions',
        'oauth_client_registration_id',
        'agent_plugin_name',
        'agent_plugin_description',
    ];

    /**
     * @var int Max icon size.
     */
    const MAX_ICON_SIZE = 1048576;

    /**
     * Check if basic configuration is complete.
     *
     * @return bool
     */
    public static function is_basic_configuration_complete(): bool {
        global $CFG;

        // Check if web services are enabled.
        if (empty($CFG->enablewebservices)) {
            return false;
        }

        // Check if RESTful protocol plugin is enabled.
        $availablewebservices = core_component::get_plugin_list('webservice');
        if (!array_key_exists('restful', $availablewebservices)) {
            return false;
        }

        // Check if RESTful protocol plugin is enabled.
        $activewebservices = empty($CFG->webserviceprotocols) ? [] : explode(',', $CFG->webserviceprotocols);
        if (!in_array('restful', $activewebservices)) {
            return false;
        }

        // Check if Microsoft 365 Copilot web services are enabled.
        $webservicemanager = new webservice();
        $copilotwebservice = $webservicemanager->get_external_service_by_shortname('copilot_webservices');
        if (!$copilotwebservice || !$copilotwebservice->enabled) {
            return false;
        }

        // Check if authenticated user role has capability to create web service token.
        $systemcontext = context_system::instance();
        $roleswithcapability = get_roles_with_capability('moodle/webservice:createtoken', CAP_ALLOW, $systemcontext);
        if (!array_key_exists($CFG->defaultuserroleid, $roleswithcapability)) {
            return false;
        }

        // Check if authenticated user role has capability to use RESTful protocol.
        $roleswithcapability = get_roles_with_capability('webservice/restful:use', CAP_ALLOW, $systemcontext);
        if (!array_key_exists($CFG->defaultuserroleid, $roleswithcapability)) {
            return false;
        }

        // All checks passed.
        return true;
    }

    /**
     * Get OAuth client options.
     *
     * @return array
     */
    public static function get_oauth_client_options(): array {
        global $DB;

        $oauthclientrecords = $DB->get_records('local_oauth2_client');
        foreach ($oauthclientrecords as $oauthclientrecord) {
            $options[$oauthclientrecord->id] = $oauthclientrecord->client_id;
        }

        return $options;
    }

    /**
     * Get selected OAuth client options.
     *
     * @return array
     */
    public static function get_selected_oauth_client_options(): array {
        global $DB;

        $options = [];
        $copilotoauthclients = get_config('local_copilot', 'oauth_client_ids');
        $teacheroauthclientid = get_config('local_copilot', 'teacher_oauth_client_id');
        $studentoauthclientid = get_config('local_copilot', 'student_oauth_client_id');

        if (!$copilotoauthclients) {
            $copilotoauthclients = [];
            if ($teacheroauthclientid) {
                $copilotoauthclients[] = $teacheroauthclientid;
            }
            if ($studentoauthclientid) {
                $copilotoauthclients[] = $studentoauthclientid;
            }
            if ($copilotoauthclients) {
                set_config('copilotoauthclients', implode(',', $copilotoauthclients));
            }
        } else {
            $copilotoauthclients = explode(',', $copilotoauthclients);
        }

        if ($copilotoauthclients) {
            foreach ($copilotoauthclients as $oauthclientid) {
                $oauthclientrecord = $DB->get_record('local_oauth2_client', ['id' => $oauthclientid]);
                if ($oauthclientrecord) {
                    $options[$oauthclientid] = $oauthclientrecord->client_id;
                }
            }
        }

        return $options;
    }

    /**
     * Get agent configuration form data.
     *
     * @param string $role The role.
     * @return array
     */
    public static function get_agent_configuration_form_data(string $role): array {
        global $CFG;

        $formdata = [];

        if (in_array($role, [manifest_generator::ROLE_TYPE_TEACHER, manifest_generator::ROLE_TYPE_STUDENT])) {
            // Icons.
            $icons = ['color', 'outline'];
            $context = context_system::instance();
            $maxfilesize = static::MAX_ICON_SIZE;
            $fs = get_file_storage();

            foreach ($icons as $icon) {
                $fieldname = $role . '_agent_' . $icon . '_icon';
                $draftitemid = file_get_submitted_draft_itemid($fieldname);
                $file = $fs->get_file($context->id, 'local_copilot', 'manifest_setting_' . $role . '_' . $icon, 0, '/',
                    $icon . '.png');

                if (!$file) {
                    $defaultimagepath = $CFG->dirroot . '/local/copilot/manifest_resource/icons/' . $icon . '.png';
                    $fs = get_file_storage();
                    $filerecord = [
                        'contextid' => $context->id,
                        'component' => 'local_copilot',
                        'filearea' => 'manifest_setting_' . $role . '_' . $icon,
                        'itemid' => 0,
                        'filepath' => '/',
                        'filename' => $icon . '.png',
                    ];
                    if (!$fs->file_exists($context->id, 'local_copilot', 'manifest_setting_' . $role . '_' . $icon, 0, '/',
                        $icon . '.png')) {
                        $fs->create_file_from_pathname($filerecord, $defaultimagepath);
                    }
                }

                file_prepare_draft_area($draftitemid, $context->id, 'local_copilot', 'manifest_setting_' . $role . '_' . $icon,
                    0, ['subdirs' => 0, 'accepted_types' => ['.png'], 'maxbytes' => $maxfilesize, 'areamaxbytes' => $maxfilesize,
                        'maxfiles' => 1]);

                $formdata[$fieldname] = $draftitemid;
            }

            // All other settings.
            foreach (static::APP_ROLE_CONFIGURATIONS as $configuration) {
                if ($configvalue = get_config('local_copilot', $role . '_' . $configuration)) {
                    $formdata[$role . '_' . $configuration] = $configvalue;
                }
            }

            // App version.
            if ($appversion = get_config('local_copilot', 'app_version')) {
                $formdata['app_version'] = $appversion;
            }
        }

        return $formdata;
    }

    /**
     * Check if all agent configurations for a role are complete.
     *
     * @param string $role
     * @return bool
     */
    public static function is_agent_configured(string $role) {
        foreach (static::APP_ROLE_CONFIGURATIONS as $configuration) {
            if (empty(get_config('local_copilot', $role . '_' . $configuration))) {
                return false;
            }
        }

        return true;
    }
}
