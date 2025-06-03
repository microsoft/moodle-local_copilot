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
 * Ajax page.
 *
 * @package local_copilot
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license https://opensource.org/license/MIT MIT License
 * @copyright (C) 2024 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot\page;

use context;
use context_system;
use core\event\webservice_external_service_enabled;
use core\session\manager;
use core_component;
use core_plugin_manager;
use html_writer;
use moodle_url;
use stdClass;
use webservice;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/lib/classes/component.php');
require_once($CFG->dirroot . '/webservice/lib.php');

/**
 * Ajax page.
 */
class ajax {
    /**
     * @var string The page's URL (relative to Moodle root).
     */
    private $url = '';

    /**
     * @var string The page's title.
     */
    private $title = '';

    /**
     * @var context The page's context.
     */
    private $context = null;

    /**
     * Constructor.
     *
     * @param string $url
     * @param string $title
     * @param context|null $context
     * @return void
     */
    public function __construct(string $url, string $title, context|null $context) {
        global $PAGE;

        if (empty($context)) {
            $context = context_system::instance();
        }
        $this->set_context($context);
        $this->set_title($title);
        $this->set_url($url);
        $PAGE->set_pagelayout('standard');
        $this->add_navbar();
    }

    /**
     * Add base navbar for this page.
     *
     * @return void
     */
    private function add_navbar(): void {
        global $PAGE;

        $PAGE->navbar->add($this->title, $this->url);
    }

    /**
     * Display the page header.
     *
     * @return bool
     */
    public function header(): bool {
        global $OUTPUT;

        echo $OUTPUT->header();

        return true;
    }

    /**
     * Set the title of the page.
     *
     * @param string $title
     * @return void
     */
    public function set_title(string $title): void {
        global $PAGE;

        $this->title = $title;
        $PAGE->set_title($this->title);
        $PAGE->set_heading($this->title);
    }

    /**
     * Set the URL of the page.
     *
     * @param mixed $url
     * @return void
     */
    public function set_url(mixed $url): void {
        global $PAGE;

        $this->url = (string)$url;
        $PAGE->set_url($this->url);
    }

    /**
     * Set the context of the page.
     *
     * @param context $context
     * @return void
     */
    public function set_context(context $context): void {
        global $PAGE;

        $this->context = $context;
        $PAGE->set_context($this->context);
    }

    /**
     * Default action.
     *
     * @return true
     */
    public function mod_default() {
        return true;
    }

    /**
     * Standard page header.
     *
     * @return void
     */
    private function standard_header(): void {
        global $OUTPUT;
        echo $OUTPUT->header();
        echo html_writer::tag('h2', $this->title);
    }

    /**
     * Run the page.
     *
     * @param string $mode
     * @return void
     */
    public function run($mode): void {
        try {
            $this->header();
            $methodname = (!empty($mode)) ? 'mode_' . $mode : 'mode_default';
            if (!method_exists($this, $methodname)) {
                $methodname = 'mode_default';
            }
            $this->$methodname();
        } catch (moodle_exception $e) {
            echo $this->error_response($e->getMessage());
        }
    }

    /**
     * Build an error ajax response.
     *
     * @param string $errormessage
     * @param string $errorcode
     * @return false|string
     */
    protected function error_response($errormessage, $errorcode = '') {
        $result = new stdClass();
        $result->success = false;
        $result->errorcode = $errorcode;
        $result->errormessage = $errormessage;
        return json_encode($result);
    }

    /**
     * Build an ajax response.
     *
     * @param mixed $data
     * @param bool $success
     * @return string
     */
    protected function ajax_response($data, $success = true): string {
        $result = new stdClass();
        $result->success = $success;
        $result->data = $data;
        return json_encode($result);
    }

    /**
     * Check local_copilot related settings.
     *
     * @return void
     */
    public function mode_check_settings(): void {
        global $CFG, $DB;

        $data = new stdClass();
        $data->success = [];
        $data->errormessages = [];
        $data->info = [];
        $success = true;

        // Check if Moodle web service is enabled.
        $formdata = new stdClass();
        $formdata->s__enablewebservices = 1;
        $count = admin_write_settings($formdata);
        if ($count === 0) {
            $data->info[] = get_string('settings_notice_web_service_already_enabled', 'local_copilot');
        } else {
            $data->success[] = get_string('settings_notice_web_service_enabled', 'local_copilot');
        }

        // Check if RESTful web service protocol is installed.
        $availablewebservices = core_component::get_plugin_list('webservice');
        if (!array_key_exists('restful', $availablewebservices)) {
            $data->errormessages[] = get_string('settings_error_restful_webservice_not_installed', 'local_copilot');
            $success = false;
        } else {
            // Check if RESTful web service protocol is enabled.
            $activewebservices = empty($CFG->webserviceprotocols) ? [] : explode(',', $CFG->webserviceprotocols);
            foreach ($activewebservices as $key => $activewebservice) {
                if (empty($availablewebservices[$activewebservice])) {
                    unset($activewebservices[$key]);
                }
            }
            if (!in_array('restful', $activewebservices)) {
                $activewebservices[] = 'restful';
                $activewebservices = array_unique($activewebservices);
                if (set_config('webserviceprotocols', implode(',', $activewebservices))) {
                    $data->success[] = get_string('settings_notice_restful_webservice_enabled', 'local_copilot');
                } else {
                    $data->errormessages[] = get_string('settings_notice_error_restful_webservice_not_enabled', 'local_copilot');
                    $success = false;
                }
            } else {
                $data->info[] = get_string('settings_notice_restful_webservice_already_enabled', 'local_copilot');
            }

            // TODO verify RESTful web service settings.
        }

        // Enable Microsoft 365 Copilot Web Services if necessary.
        $webservicemanager = new webservice();
        $copilotwebservice = $webservicemanager->get_external_service_by_shortname('copilot_webservices');
        if (!$copilotwebservice->enabled) {
            $copilotwebservice->enabled = 1;
            $webservicemanager->update_external_service($copilotwebservice);

            $params = ['objectid' => $copilotwebservice->id];
            $event = webservice_external_service_enabled::create($params);
            $event->trigger();
            $data->success[] = get_string('settings_notice_copilot_webservice_enabled', 'local_copilot');
        } else {
            $data->info[] = get_string('settings_notice_copilot_webservice_already_enabled', 'local_copilot');
        }

        // Verify authenticated user role has capability to create web service token.
        $sysmtecontext = context_system::instance();
        $roleswithcapability = get_roles_with_capability('moodle/webservice:createtoken', CAP_ALLOW, $sysmtecontext);
        if (array_key_exists($CFG->defaultuserroleid, $roleswithcapability)) {
            $data->info[] = get_string('settings_notice_authenticated_user_already_has_create_token_capability', 'local_copilot');
        } else {
            if (assign_capability('moodle/webservice:createtoken', CAP_ALLOW, $CFG->defaultuserroleid, $sysmtecontext->id)) {
                $data->success[] = get_string('settings_notice_authenticated_user_assigned_create_token_capability',
                    'local_copilot');
            } else {
                $data->errormessages[] = get_string('settings_notice_error_assigning_create_token_capability', 'local_copilot');
                $success = false;
            }
        }

        // Verify authenticated user role has capability to use RESTful protocol.
        $roleswithcapability = get_roles_with_capability('webservice/restful:use', CAP_ALLOW, $sysmtecontext);
        if (array_key_exists($CFG->defaultuserroleid, $roleswithcapability)) {
            $data->info[] = get_string('settings_notice_authenticated_user_already_has_use_restful_capability', 'local_copilot');
        } else {
            // Check if capability exists.
            $capability = 'webservice/restful:use';
            $capabilityinfo = get_capability_info($capability);
            if ($capabilityinfo) {
                if (assign_capability($capability, CAP_ALLOW, $CFG->defaultuserroleid, $sysmtecontext->id)) {
                    $data->success[] = get_string('settings_notice_authenticated_user_assigned_use_restful_capability',
                        'local_copilot');
                } else {
                    $data->errormessages[] = get_string('settings_notice_error_assigning_use_restful_capability', 'local_copilot');
                    $success = false;
                }
            } else {
                $data->errormessages[] = get_string('settings_notice_error_capability_not_exist', 'local_copilot', $capability);
                $success = false;
            }
        }

        // Confirm that there are OAuth clients configured.
        $oauthclientsconfigurationurl = new moodle_url('/local/oauth2/manage_oauth_clients.php');
        if ($DB->count_records('local_oauth2_client') === 0) {
            $data->errormessages[] = get_string('settings_notice_error_no_oauth_clients', 'local_copilot',
                $oauthclientsconfigurationurl->out());
            $success = false;
        } else {
            $data->info[] = get_string('settings_notice_oauth_clients_exist', 'local_copilot',
                $oauthclientsconfigurationurl->out());
        }

        // Remove stale sessions.
        manager::gc();
        core_plugin_manager::reset_caches();

        echo $this->ajax_response($data, $success);
    }
}
