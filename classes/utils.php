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
 * @license https://opensource.org/license/MIT MIT License
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
        'agent_capability_image_generator',
        'agent_capability_copilot_connectors',
        'agent_capability_sharepoint_onedrive',
        'agent_capability_web_search',
    ];

    /**
     * @var array App role optional configurations.
     */
    const APP_ROLE_OPTIONAL_CONFIGURATIONS = [
        'agent_copilot_connectors_connection_ids',
        'agent_sharepoint_items_by_sharepoint_ids',
        'agent_sharepoint_items_by_url',
        'agent_scoped_web_search_sites',
    ];

    /**
     * @var array SharePoint ID names.
     */
    const SHAREPOINT_ID_NAMES = [
        'site_id',
        'web_id',
        'list_id',
        'unique_id',
        'search_associated_sites',
        'part_type',
        'part_id',
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
                $configvalue = get_config('local_copilot', $role . '_' . $configuration);
                if ($configvalue === false) {
                    $configvalue = null;
                }
                $formdata[$role . '_' . $configuration] = $configvalue;
            }

            foreach (static::APP_ROLE_OPTIONAL_CONFIGURATIONS as $configuration) {
                if ($configvalue = get_config('local_copilot', $role . '_' . $configuration)) {
                    $formdata[$role . '_' . $configuration] = $configvalue;
                } else {
                    $formdata[$role . '_' . $configuration] = null;
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
    public static function is_agent_configured(string $role): bool {
        foreach (static::APP_ROLE_CONFIGURATIONS as $configuration) {
            if (is_null((get_config('local_copilot', $role . '_' . $configuration)))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if a value is a valid GUID.
     *
     * @param string $value The value to check.
     * @param bool $requirehyphens Whether to require hyphens in the GUID format.
     * @return bool True if the value is a valid GUID, false otherwise.
     */
    public static function is_guid(string $value, bool $requirehyphens = false): bool {
        if (!is_string($value)) {
            return false;
        }

        if ($requirehyphens) {
            // Strict format: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx.
            $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';
        } else {
            // Allow both with and without hyphens.
            $normalized = str_replace('-', '', $value);
            return strlen($normalized) === 32 && ctype_xdigit($normalized);
        }

        return preg_match($pattern, $value) === 1;
    }

    /**
     * Check if URL is a SharePoint or OneDrive item URL
     *
     * @param string $url The URL to validate
     * @return array Result with 'is_valid', and 'type'
     */
    public static function is_sharepoint_onedrive_url(string $url): array  {
        if (!is_string($url) || empty($url)) {
            return ['is_valid' => false, 'type' => null];
        }

        $parsed = parse_url($url);
        if (!$parsed || !isset($parsed['host'])) {
            return ['is_valid' => false, 'type' => null];
        }

        $host = strtolower($parsed['host']);
        $path = $parsed['path'] ?? '';

        // OneDrive patterns.
        if (static::is_onedrive_url($host, $path)) {
            return [
                'is_valid' => true,
                'type' => 'onedrive',
            ];
        }

        // SharePoint patterns.
        if (static::is_sharepoint_url($host, $path)) {
            return [
                'is_valid' => true,
                'type' => 'sharepoint',
            ];
        }

        return ['is_valid' => false, 'type' => null];
    }

    /**
     * Check if URL is OneDrive.
     *
     * @param string $host
     * @param string $path
     * @return bool
     */
    public static function is_onedrive_url(string $host, string $path): bool {
        // OneDrive personal patterns
        $onedrivepatterns = [
            '/^.*\.onedrive\.live\.com$/',
            '/^onedrive\.live\.com$/',
            '/^.*-my\.sharepoint\.com$/'  // OneDrive for Business.
        ];

        foreach ($onedrivepatterns as $pattern) {
            if (preg_match($pattern, $host)) {
                return true;
            }
        }

        // Check for OneDrive path patterns
        if (preg_match('/\/personal\/.*\/_layouts\/15\/onedrive\.aspx/', $path)) {
            return true;
        }

        return false;
    }

    /**
     * Check if URL is SharePoint.
     *
     * @param string $host
     * @param string $path
     * @return bool
     */
    public static function is_sharepoint_url(string $host, string $path): bool {
        // SharePoint patterns.
        $sharepointpatterns = [
            '/^.*\.sharepoint\.com$/',
            '/^.*\.sharepoint-df\.com$/',  // Dedicated environments.
            '/^sharepoint\..*\.com$/'
        ];

        foreach ($sharepointpatterns as $pattern) {
            if (preg_match($pattern, $host)) {
                return true;
            }
        }

        // Check for SharePoint path patterns.
        $sharepointpaths = [
            '/_layouts/',
            '/sites/',
            '/teams/',
            '/Shared%20Documents/',
            '/Forms/',
            '/Lists/'
        ];

        foreach ($sharepointpaths as $sppath) {
            if (strpos($path, $sppath) !== false) {
                return true;
            }
        }

        return false;
    }
}
