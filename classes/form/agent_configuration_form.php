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
 * Configure agent form.
 *
 * @package local_copilot
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license https://opensource.org/license/MIT MIT License
 * @copyright (C) 2024 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot\form;

use context_user;
use html_writer;
use local_copilot\manifest_generator;
use local_copilot\utils;
use moodle_url;
use moodleform;

/**
 * Configure agent form.
 */
class agent_configuration_form extends moodleform {
    /**
     * @var string $role The role type.
     */
    private $role;

    /**
     * Default external ID for teacher app.
     */
    const TEACHER_APP_DEFAULT_EXTERNAL_ID = '3663d3f6-c250-4610-912f-b0b51ef7e0e2';

    /**
     * Default external ID for student app.
     */
    const STUDENT_APP_DEFAULT_EXTERNAL_ID = 'a66b7cf7-7870-4ba5-8749-ab0f1ae3cc0d';

    /**
     * Constructor.
     *
     * @param string $action
     * @param array $customdata
     */
    public function __construct($action = null, $customdata = null) {
        $this->role = $customdata['role'];
        parent::__construct($action, $customdata);
    }

    /**
     * Form definition.
     */
    protected function definition() {
        global $CFG, $DB;

        $mform =& $this->_form;

        // Role.
        $mform->addElement('hidden', 'role', $this->role);
        $mform->setType('role', PARAM_ALPHA);

        // Agent details header.
        $mform->addElement('header', $this->role . '_agent_details',
            get_string('settings_configure_' . $this->role . '_agent', 'local_copilot'));
        $mform->setExpanded($this->role . '_agent_details');

        // Agent app external ID.
        $mform->addElement('text', $this->role . '_agent_app_external_id', get_string('app_external_id', 'local_copilot'),
            ['maxlength' => 36, 'size' => 36]);
        $mform->setType($this->role . '_agent_app_external_id', PARAM_TEXT);
        $defaultexternalid = $this->role == manifest_generator::ROLE_TYPE_TEACHER ?
            static::TEACHER_APP_DEFAULT_EXTERNAL_ID : static::STUDENT_APP_DEFAULT_EXTERNAL_ID;
        $mform->setDefault($this->role . '_agent_app_external_id', $defaultexternalid);
        $mform->addElement('static', $this->role . '_agent_app_external_id_help', '',
            get_string('app_external_id_help', 'local_copilot', ['role' => $this->role, 'id' => $defaultexternalid]));
        $mform->addRule($this->role . '_agent_app_external_id', null, 'required', null, 'client');

        // Agent app short name.
        $mform->addElement('text', $this->role . '_agent_app_short_name', get_string('app_short_name', 'local_copilot'),
            ['maxlength' => 30, 'size' => 30]);
        $mform->setType($this->role . '_agent_app_short_name', PARAM_TEXT);
        $mform->setDefault($this->role . '_agent_app_short_name', 'Moodle ' . ucfirst($this->role));
        $mform->addElement('static', $this->role . '_agent_app_short_name_help', '',
            get_string('app_short_name_help', 'local_copilot'));
        $mform->addRule($this->role . '_agent_app_short_name', null, 'required', null, 'client');

        // Agent app full name.
        $mform->addElement('text', $this->role . '_agent_app_full_name', get_string('app_full_name', 'local_copilot'),
            ['maxlength' => 100, 'size' => 80]);
        $mform->setType($this->role . '_agent_app_full_name', PARAM_TEXT);
        $mform->setDefault($this->role . '_agent_app_full_name', 'Moodle ' . ucfirst($this->role) .
            ' app for Microsoft 365 Copilot');
        $mform->addElement('static', $this->role . '_agent_app_full_name_help', '',
            get_string('app_full_name_help', 'local_copilot'));
        $mform->addRule($this->role . '_agent_app_full_name', null, 'required', null, 'client');

        // App version.
        $mform->addElement('text', 'app_version', get_string('app_version', 'local_copilot'),
            ['maxlength' => 10, 'size' => 10]);
        $mform->setType('app_version', PARAM_TEXT);
        $mform->setDefault('app_version', '1.0.0');
        $mform->addElement('static', 'app_version_help', '', get_string('app_version_help', 'local_copilot'));
        $mform->addRule('app_version', null, 'required', null, 'client');

        // Agent app short description.
        $mform->addElement('text', $this->role . '_agent_app_short_description',
            get_string('app_short_description', 'local_copilot'), ['maxlength' => 80, 'size' => 80]);
        $mform->setType($this->role . '_agent_app_short_description', PARAM_TEXT);
        $mform->setDefault($this->role . '_agent_app_short_description', 'A declarative agent for Moodle ' . $this->role .
            's');
        $mform->addElement('static', $this->role . '_agent_app_short_description_help', '',
            get_string('app_short_description_help', 'local_copilot'));
        $mform->addRule($this->role . '_agent_app_short_description', null, 'required', null, 'client');

        // Agent app full description.
        $mform->addElement('textarea', $this->role . '_agent_app_full_description',
            get_string('app_full_description', 'local_copilot'), ['rows' => 5, 'cols' => 80, 'maxlength' => 400]);
        $mform->setType($this->role . '_agent_app_full_description', PARAM_TEXT);
        switch ($this->role) {
            case manifest_generator::ROLE_TYPE_TEACHER:
                $mform->setDefault($this->role . '_agent_app_full_description',
                    'This agent provides a set of functions to help teachers interact with Moodle data in Microsoft 365 Copilot.' .
                    ' It provides functions to list courses, find course content, and get assignment details.');
                break;
            case manifest_generator::ROLE_TYPE_STUDENT:
                $mform->setDefault($this->role . '_agent_app_full_description',
                    'This agent provides a set of functions to help students interact with Moodle data in Microsoft 365 Copilot. ' .
                    'It provides functions to list courses, find course content, find activities, and get assignment details.');
                break;
        }
        $mform->addElement('static', $this->role . '_agent_app_full_description_help', '',
            get_string('app_full_description_help', 'local_copilot'));
        $mform->addRule($this->role . '_agent_app_full_description', null, 'required', null, 'client');

        // Accent color.
        $mform->addElement('text', $this->role . '_agent_accent_color', get_string('accent_color', 'local_copilot'),
            ['maxlength' => 7, 'size' => 7]);
        $mform->setType($this->role . '_agent_accent_color', PARAM_TEXT);
        $mform->setDefault($this->role . '_agent_accent_color', '#FFFFFF');
        $mform->addElement('static', $this->role . '_agent_accent_color_help', '',
            get_string('accent_color_help', 'local_copilot'));
        $mform->addRule($this->role . '_agent_accent_color', null, 'required', null, 'client');

        // Agent display name.
        $mform->addElement('text', $this->role . '_agent_display_name', get_string('agent_display_name', 'local_copilot'),
            ['maxlength' => 100, 'size' => 80]);
        $mform->setType($this->role . '_agent_display_name', PARAM_TEXT);
        $mform->setDefault($this->role . '_agent_display_name', 'Moodle ' . ucfirst($this->role));
        $mform->addElement('static', $this->role . '_agent_display_name_help', '',
            get_string('agent_display_name_help', 'local_copilot'));
        $mform->addRule($this->role . '_agent_display_name', null, 'required', null, 'client');

        // Agent description.
        $mform->addElement('textarea', $this->role . '_agent_description', get_string('agent_description', 'local_copilot'),
            ['rows' => 5, 'cols' => 80, 'maxlength' => 1000]);
        $mform->setType($this->role . '_agent_description', PARAM_TEXT);
        $mform->setDefault($this->role . '_agent_description', 'An agent that can answer questions to ' . $this->role .
            's using Moodle data.');
        $mform->addElement('static', $this->role . '_agent_description_help', '',
            get_string('agent_description_help', 'local_copilot'));
        $mform->addRule($this->role . '_agent_description', null, 'required', null, 'client');

        // Agent instructions.
        $mform->addElement('textarea', $this->role . '_agent_instructions', get_string('agent_instructions', 'local_copilot'),
            ['rows' => 16, 'cols' => 80, 'maxlength' => 8000]);
        $mform->setType($this->role . '_agent_instructions', PARAM_TEXT);
        $instructionfile = $CFG->dirroot . '/local/copilot/manifest_resource/instructions/' . $this->role . '.txt';
        $instructions = file_get_contents($instructionfile);
        $mform->setDefault($this->role . '_agent_instructions', $instructions);
        $mform->addElement('static', $this->role . '_agent_instructions_help', '',
            get_string('agent_instructions_help', 'local_copilot'));
        $mform->addRule($this->role . '_agent_instructions', null, 'required', null, 'client');

        // Color icon.
        $mform->addElement('filemanager', $this->role . '_agent_color_icon', get_string('color_icon', 'local_copilot'), null,
            ['subdirs' => 0, 'accepted_types' => ['.png'], 'maxbytes' => utils::MAX_ICON_SIZE,
                'areamaxbytes' => utils::MAX_ICON_SIZE, 'maxfiles' => 1]);
        $mform->addElement('static', $this->role . '_agent_color_icon_help', '', get_string('color_icon_help', 'local_copilot'),
            '');
        $mform->addRule($this->role . '_agent_color_icon', null, 'required', null, 'client');

        // Outline icon.
        $mform->addElement('filemanager', $this->role . '_agent_outline_icon', get_string('outline_icon', 'local_copilot'), null,
            ['subdirs' => 0, 'accepted_types' => ['.png'], 'maxbytes' => utils::MAX_ICON_SIZE,
                'areamaxbytes' => utils::MAX_ICON_SIZE, 'maxfiles' => 1]);
        $mform->addElement('static', $this->role . '_agent_outline_icon_help', '', get_string('outline_icon_help', 'local_copilot'),
            '');
        $mform->addRule($this->role . '_agent_outline_icon', null, 'required', null, 'client');

        // Teams developer portal OAuth client registration ID.
        $site = get_site();
        $clientid = get_config('local_copilot', $this->role . '_oauth_client_id');
        $oauthclient = $DB->get_record('local_oauth2_client', ['id' => $clientid]);
        $authorizationendpoint = new moodle_url('/local/oauth2/login.php');
        $tokenendpoint = new moodle_url('/local/oauth2/token.php');
        $refreshendpoint = new moodle_url('/local/oauth2/refresh_token.php');
        $stringparams = [
            'site_name' => $site->fullname,
            'wwwroot' => $CFG->wwwroot,
            'client_id' => $oauthclient->client_id,
            'client_secret' => $oauthclient->client_secret,
            'authorization_endpoint' => $authorizationendpoint->out(),
            'token_endpoint' => $tokenendpoint->out(),
            'refresh_endpoint' => $refreshendpoint->out(),
            'scope' => $this->role . '.read, ' . $this->role . '.write',
        ];
        $mform->addElement('static', $this->role . '_oauth_client_registration_id_steps',
            get_string('agent_oauth_client_registration_id_steps', 'local_copilot'),
            get_string('agent_oauth_client_registration_id_help', 'local_copilot', $stringparams));
        $mform->addElement('text', $this->role . '_oauth_client_registration_id',
            get_string('agent_oauth_client_registration_id', 'local_copilot'), ['maxlength' => 100, 'size' => 80]);
        $mform->setType($this->role . '_oauth_client_registration_id', PARAM_TEXT);
        $mform->addRule($this->role . '_oauth_client_registration_id', null, 'required', null, 'client');

        // Agent plugin name.
        $mform->addElement('text', $this->role . '_agent_plugin_name', get_string('agent_plugin_name', 'local_copilot'),
            ['maxlength' => 20, 'size' => 20]);
        $mform->setType($this->role . '_agent_plugin_name', PARAM_TEXT);
        $mform->setDefault($this->role . '_agent_plugin_name', 'Moodle ' . ucfirst($this->role) . ' API');
        $mform->addElement('static', $this->role . '_agent_plugin_name_help', '',
            get_string('agent_plugin_name_help', 'local_copilot'));
        $mform->addRule($this->role . '_agent_plugin_name', null, 'required', null, 'client');

        // Agent plugin description.
        $mform->addElement('textarea', $this->role . '_agent_plugin_description',
            get_string('agent_plugin_description', 'local_copilot'), ['rows' => 5, 'cols' => 80, 'maxlength' => 2048]);
        $mform->setType($this->role . '_agent_plugin_description', PARAM_TEXT);
        $mform->setDefault($this->role . '_agent_plugin_description',
            'This API plugin provides access to Moodle data for Microsoft 365 Copilot. ' .
            'It contains functions specifically for ' . $this->role . 's.');
        $mform->addElement('static', $this->role . '_agent_plugin_description_help', '',
            get_string('agent_plugin_description_help', 'local_copilot'));
        $mform->addRule($this->role . '_agent_plugin_description', null, 'required', null, 'client');

        // Capabilities and knowledge sources settings.
        $mform->addElement('header', 'capabilities_and_knowledge_sources_settings',
            get_string('settings_capabilities_and_knowledge_sources', 'local_copilot'));
        $mform->setExpanded('capabilities_and_knowledge_sources_settings');

        // Capabilities and knowledge sources description.
        $mform->addElement('static', 'capabilities_and_knowledge_sources_description', '',
            get_string('settings_capabilities_and_knowledge_sources_desc', 'local_copilot'));

        // Code interpreter.
        $mform->addElement('advcheckbox', $this->role . '_agent_capability_code_interpreter',
            get_string('enable_code_interpreter', 'local_copilot'), null, ['group' => 1, 'disabled' => true], [0, 1]);
        $mform->setDefault($this->role . '_agent_capability_code_interpreter', 1);

        // Image generator.
        $mform->addElement('advcheckbox', $this->role . '_agent_capability_image_generator',
            get_string('enable_image_generator', 'local_copilot'), null, ['group' => 1], [0, 1]);
        $mform->setDefault($this->role . '_agent_capability_image_generator', 1);

        // Copilot connectors.
        $mform->addElement('advcheckbox', $this->role . '_agent_capability_copilot_connectors',
            get_string('enable_copilot_connectors', 'local_copilot'), null, ['group' => 1], [0, 1]);
        $mform->setDefault($this->role . '_agent_capability_copilot_connectors', 0);

        // Copilot connectors connection IDs.
        // TODO v1.4 version of agent supports more parameters in connection object. They need to be added to the configuration.
        // https://learn.microsoft.com/en-us/microsoft-365-copilot/extensibility/declarative-agent-manifest-1.4#connection-object.
        $mform->addElement('textarea', $this->role . '_agent_copilot_connectors_connection_ids',
            get_string('copilot_connectors_connection_ids', 'local_copilot'), ['rows' => 8, 'cols' => 80, 'maxlength' => 8192]);
        $mform->setType($this->role . '_agent_copilot_connectors_connection_ids', PARAM_TEXT);
        $mform->hideIf($this->role . '_agent_copilot_connectors_connection_ids',
            $this->role . '_agent_capability_copilot_connectors', 'unchecked');
        $mform->addElement('static', $this->role . '_agent_copilot_connectors_connection_ids_help', '',
            get_string('copilot_connectors_connection_ids_help', 'local_copilot'));
        $mform->hideIf($this->role . '_agent_copilot_connectors_connection_ids_help',
            $this->role . '_agent_capability_copilot_connectors', 'unchecked');

        // SharePoint and OneDrive.
        $mform->addElement('advcheckbox', $this->role . '_agent_capability_sharepoint_onedrive',
            get_string('enable_sharepoint_onedrive', 'local_copilot'), null, ['group' => 1], [0, 1]);
        $mform->setDefault($this->role . '_agent_capability_sharepoint_onedrive', 0);

        // Items by SharePoint IDs.
        $mform->addElement('textarea', $this->role . '_agent_sharepoint_items_by_sharepoint_ids',
            get_string('sharepoint_items_by_sharepoint_ids', 'local_copilot'), ['rows' => 8, 'cols' => 80, 'maxlength' => 8192]);
        $mform->setType($this->role . '_agent_sharepoint_items_by_sharepoint_ids', PARAM_TEXT);
        $mform->hideIf($this->role . '_agent_sharepoint_items_by_sharepoint_ids',
            $this->role . '_agent_capability_sharepoint_onedrive', 'unchecked');
        $mform->addElement('static', $this->role . '_agent_sharepoint_items_by_sharepoint_ids_help', '',
            get_string('sharepoint_items_by_sharepoint_ids_help', 'local_copilot'));
        $mform->hideIf($this->role . '_agent_sharepoint_items_by_sharepoint_ids_help',
            $this->role . '_agent_capability_sharepoint_onedrive', 'unchecked');

        // Items by URL.
        $mform->addElement('textarea', $this->role . '_agent_sharepoint_items_by_url',
            get_string('sharepoint_items_by_url', 'local_copilot'), ['rows' => 8, 'cols' => 80, 'maxlength' => 8192]);
        $mform->setType($this->role . '_agent_sharepoint_items_by_url', PARAM_TEXT);
        $mform->hideIf($this->role . '_agent_sharepoint_items_by_url',
            $this->role . '_agent_capability_sharepoint_onedrive', 'unchecked');
        $mform->addElement('static', $this->role . '_agent_sharepoint_items_by_url_help', '',
            get_string('sharepoint_items_by_url_help', 'local_copilot'));
        $mform->hideIf($this->role . '_agent_sharepoint_items_by_url_help',
            $this->role . '_agent_capability_sharepoint_onedrive', 'unchecked');

        // Web search.
        $mform->addElement('advcheckbox', $this->role . '_agent_capability_web_search',
            get_string('enable_web_search', 'local_copilot'), null, ['group' => 1], [0, 1]);
        $mform->setDefault($this->role . '_agent_capability_web_search', 0);

        // Scoped web search.
        $mform->addElement('textarea', $this->role . '_agent_scoped_web_search_sites',
            get_string('scoped_web_search_sites', 'local_copilot'), ['rows' => 4, 'cols' => 80, 'maxlength' => 2048]);
        $mform->setType($this->role . '_agent_scoped_web_search_sites', PARAM_TEXT);
        $mform->hideIf($this->role . '_agent_scoped_web_search_sites', $this->role . '_agent_capability_web_search',
            'unchecked');
        $mform->addElement('static', $this->role . '_agent_scoped_web_search_sites_help', '',
            get_string('scoped_web_search_sites_help', 'local_copilot'));
        $mform->hideIf($this->role . '_agent_scoped_web_search_sites_help',
            $this->role . '_agent_capability_web_search', 'unchecked');

        // Buttons.
        $this->add_action_buttons();

        // Link to download manifest file.
        if (utils::is_agent_configured($this->role)) {
            $downloadurl = new moodle_url('/local/copilot/configure_agent.php', ['role' => $this->role, 'action' => 'download']);
            $mform->addElement('static', $this->role . '_download_manifest_link', '',
                html_writer::link($downloadurl, get_string('download_manifest', 'local_copilot'), ['class' => 'btn btn-primary']));
        }

        // Reminder to configure the app in Teams developer portal.
        $mform->addElement('static', $this->role . '_configure_app_link', '',
            get_string('configure_app_in_teams_dev_portal', 'local_copilot'));
    }

    /**
     * Validate the form data.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files): array {
        global $USER;

        $errors = parent::validation($data, $files);

        // Validate the accent color.
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $data[$this->role . '_agent_accent_color'])) {
            $errors[$this->role . '_agent_accent_color'] = get_string('error_invalid_accent_color', 'local_copilot');
        }

        // Validate color icon size.
        $fs = get_file_storage();
        $usercontext = context_user::instance($USER->id);
        $coloriconfiles = $fs->get_area_files($usercontext->id, 'user', 'draft', $data[$this->role . '_agent_color_icon']);
        foreach ($coloriconfiles as $coloriconfile) {
            if ($coloriconfile->get_filename() == '.') {
                continue;
            }

            $imageinfo = getimagesizefromstring($coloriconfile->get_content());
            if ($imageinfo[0] != 192 || $imageinfo[1] != 192) {
                $errors[$this->role . '_agent_color_icon'] = get_string('error_invalid_color_icon_size', 'local_copilot');
            }
        }

        // Validate outline icon size.
        $outlineiconfiles = $fs->get_area_files($usercontext->id, 'user', 'draft', $data[$this->role . '_agent_outline_icon']);
        foreach ($outlineiconfiles as $outlineiconfile) {
            if ($outlineiconfile->get_filename() == '.') {
                continue;
            }

            $imageinfo = getimagesizefromstring($outlineiconfile->get_content());
            if ($imageinfo[0] != 32 || $imageinfo[1] != 32) {
                $errors[$this->role . '_agent_outline_icon'] = get_string('error_invalid_outline_icon_size', 'local_copilot');
            }
        }

        // Validate app version.
        if (!preg_match('/^\d+\.\d+\.\d+$/', $data['app_version'])) {
            $errors['app_version'] = get_string('error_invalid_app_version', 'local_copilot');
        } else {
            $currentversion = get_config('local_copilot', 'app_version');
            if ($currentversion && version_compare($data['app_version'], $currentversion, '<')) {
                $errors['app_version'] = get_string('error_decreased_app_version', 'local_copilot');
            }
        }

        // Validate app instructions length.
        $instructions = $data[$this->role . '_agent_instructions'];
        $manifestgenerator = new manifest_generator($this->role);
        $instructions .= $manifestgenerator->get_instructions(true, false);
        if (strlen($instructions) > manifest_generator::INSTRUCTIONS_LENGTH_LIMIT) {
            $errors[$this->role . '_agent_instructions'] = get_string('error_instructions_too_long', 'local_copilot');
        }

        // Validate OneDrive and SharePoint items by IDs.
        if ($data[$this->role . '_agent_capability_sharepoint_onedrive'] &&
            !empty($data[$this->role . '_agent_sharepoint_items_by_sharepoint_ids'])) {
            $items = explode("\n", $data[$this->role . '_agent_sharepoint_items_by_sharepoint_ids']);
            $sharepointiderrors = [];
            foreach ($items as $line => $item) {
                $item = trim($item);
                if (!$item) {
                    continue; // Skip empty lines.
                }
                if ($itemcontent = json_decode($item, true)) {
                    if (!is_array($itemcontent)) {
                        $sharepointiderrors[] = get_string('error_invalid_json_format', 'local_copilot', ['line' => $line + 1]);
                        continue;
                    }
                    foreach ($itemcontent as $key => $value) {
                        if (!in_array($key, utils::SHAREPOINT_ID_NAMES)) {
                            $sharepointiderrors[] = get_string('error_invalid_sharepoint_id_name', 'local_copilot',
                                ['name' => $key, 'line' => $line + 1]);
                        }

                        switch ($key) {
                            case 'search_associated_sites':
                                if (!is_bool($value)) {
                                    $sharepointiderrors[] = get_string('error_invalid_sharepoint_id_value', 'local_copilot',
                                        ['name' => $key, 'line' => $line + 1]);
                                }
                                break;
                            case 'part_type':
                                if ($value !== 'OneNotePart') {
                                    $sharepointiderrors[] = get_string('error_invalid_sharepoint_id_value', 'local_copilot',
                                        ['name' => $key, 'line' => $line + 1]);
                                }
                                break;
                            default:
                                // For other keys, we expect a GUID value.
                                if (!utils::is_guid($value)) {
                                    $sharepointiderrors[] = get_string('error_invalid_sharepoint_id_value', 'local_copilot',
                                        ['name' => $key, 'line' => $line + 1]);
                                }
                        }
                        if (!utils::is_guid($value)) {
                            $sharepointiderrors[] = get_string('error_invalid_sharepoint_id_value', 'local_copilot',
                                ['name' => $key, 'line' => $line + 1]);
                        }
                    }
                }
            }

            if ($sharepointiderrors) {
                $errors[$this->role . '_agent_sharepoint_items_by_sharepoint_ids'] = implode('<br>', $sharepointiderrors);
            }
        }

        // Validate items by URL.
        if ($data[$this->role . '_agent_capability_sharepoint_onedrive'] &&
            !empty($data[$this->role . '_agent_sharepoint_items_by_url'])) {
            $items = explode("\n", $data[$this->role . '_agent_sharepoint_items_by_url']);
            $sharepointurlerrors = [];
            foreach ($items as $line => $item) {
                $item = trim($item);
                if (!$item) {
                    continue; // Skip empty lines.
                }
                if (!filter_var($item, FILTER_VALIDATE_URL)) {
                    $sharepointurlerrors[] = get_string('error_invalid_sharepoint_item_url', 'local_copilot',
                        ['line' => $line + 1]);
                } else {
                    $validationresult = utils::is_sharepoint_onedrive_url($item);
                    if (!$validationresult['is_valid']) {
                        $sharepointurlerrors[] = get_string('error_not_sharepoint_onedrive_url', 'local_copilot',
                            ['url' => $item, 'line' => $line + 1]);
                    }
                }
            }

            if ($sharepointurlerrors) {
                $errors[$this->role . '_agent_sharepoint_items_by_url'] = implode('<br>', $sharepointurlerrors);
            }
        }

        // Validate scoped web search sites.
        if ($data[$this->role . '_agent_capability_web_search'] && !empty($data[$this->role . '_agent_scoped_web_search_sites'])) {
            $sites = explode("\n", $data[$this->role . '_agent_scoped_web_search_sites']);
            if (count($sites) > 4) {
                $errors[$this->role . '_agent_scoped_web_search_sites'] = get_string('error_too_many_scoped_web_search_sites',
                    'local_copilot');
            } else {
                foreach ($sites as $site) {
                    $site = trim($site);
                    if (!empty($site) && !filter_var($site, FILTER_VALIDATE_URL)) {
                        $errors[$this->role . '_agent_scoped_web_search_sites'] = get_string('error_invalid_scoped_web_search_site',
                            'local_copilot');
                        break;
                    } else {
                        $parsedurl = parse_url($site);
                        // Ensure the URL doesn't have query parameters.
                        if (isset($parsedurl['query']) && !empty($parsedurl['query'])) {
                            $errors[$this->role . '_agent_scoped_web_search_sites'] =
                                get_string('error_scoped_web_search_site_query_params', 'local_copilot');
                            break;
                        } else {
                            // Ensure the URL doesn't have more than two path segments.
                            if (isset($parsedurl['path']) && !empty($parsedurl['path'])) {
                                $pathsegments = explode('/', trim($parsedurl['path'], '/'));
                                if (count($pathsegments) > 2) {
                                    $errors[$this->role . '_agent_scoped_web_search_sites'] =
                                        get_string('error_scoped_web_search_site_path_segments', 'local_copilot');
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Get the form data.
     * This function contains custom logic to ensure that certain fields are formatted correctly.
     *
     * @return object|null
     */
    public function get_data() {
        $data = parent::get_data();
        if ($data) {
            // Ensure the accent color is always in lowercase.
            $data->{$this->role . '_agent_accent_color'} = strtolower($data->{$this->role . '_agent_accent_color'});

            // Ensure agent_copilot_connectors_connection_ids is only set if copilot connectors capability is enabled.
            if (empty($data->{$this->role . '_agent_capability_copilot_connectors'})) {
                $data->{$this->role . '_agent_copilot_connectors_connection_ids'} = null;
            } else {
                // Clean up the copilot connectors connection IDs.
                $connectionids = explode("\n", $data->{$this->role . '_agent_copilot_connectors_connection_ids'});
                $cleanedids = [];
                foreach ($connectionids as $id) {
                    $id = trim($id);
                    if ($id) {
                        $cleanedids[] = $id;
                    }
                }
                $data->{$this->role . '_agent_copilot_connectors_connection_ids'} = implode("\n", $cleanedids);
            }

            // Ensure agent_sharepoint_items_by_sharepoint_ids is only set if SharePoint/OneDrive capability is enabled.
            if (empty($data->{$this->role . '_agent_capability_sharepoint_onedrive'})) {
                $data->{$this->role . '_agent_sharepoint_items_by_sharepoint_ids'} = null;
            } else {
                // Clean up the SharePoint items by IDs.
                $items = explode("\n", $data->{$this->role . '_agent_sharepoint_items_by_sharepoint_ids'});
                $cleaneditems = [];
                foreach ($items as $item) {
                    $item = trim($item);
                    if ($item) {
                        $cleaneditems[] = $item;
                    }
                }
                $data->{$this->role . '_agent_sharepoint_items_by_sharepoint_ids'} = implode("\n", $cleaneditems);
            }

            // Ensure agent_sharepoint_items_by_url is only set if SharePoint/OneDrive capability is enabled.
            if (empty($data->{$this->role . '_agent_capability_sharepoint_onedrive'})) {
                $data->{$this->role . '_agent_sharepoint_items_by_url'} = null;
            } else {
                // Clean up the SharePoint items by URL.
                $items = explode("\n", $data->{$this->role . '_agent_sharepoint_items_by_url'});
                $cleaneditems = [];
                foreach ($items as $item) {
                    $item = trim($item);
                    if ($item) {
                        $cleaneditems[] = $item;
                    }
                }
                $data->{$this->role . '_agent_sharepoint_items_by_url'} = implode("\n", $cleaneditems);
            }

            // Ensure agent_scoped_web_search_sites is only set if web search capability is enabled.
            if (empty($data->{$this->role . '_agent_capability_web_search'})) {
                $data->{$this->role . '_agent_scoped_web_search_sites'} = null;
            } else {
                // Clean up the scoped web search sites.
                $sites = explode("\n", $data->{$this->role . '_agent_scoped_web_search_sites'});
                $cleanedsites = [];
                foreach ($sites as $site) {
                    $site = trim($site);
                    if ($site) {
                        $cleanedsites[] = $site;
                    }
                }
                $data->{$this->role . '_agent_scoped_web_search_sites'} = implode("\n", $cleanedsites);
            }
        }

        return $data;
    }
}
