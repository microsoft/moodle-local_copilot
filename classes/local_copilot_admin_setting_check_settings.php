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
 * Admin setting to detect and set required settings in Moodle.
 *
 * @package local_copilot
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2024 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot;

use admin_setting;
use html_writer;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/adminlib.php');

/**
 * Admin setting to detect and set required settings in Moodle.
 */
class local_copilot_admin_setting_check_settings extends admin_setting {
    /**
     * Constructor.
     *
     * @param string $name
     * @param string $visiblename
     * @param string $description
     * @param string $defaultsetting
     */
    public function __construct($name, $visiblename, $description, $defaultsetting) {
        $this->nosave = true;
        parent::__construct($name, $visiblename, $description, $defaultsetting);
    }

    /**
     * Return the setting
     *
     * @return true returns config if successful else null
     */
    public function get_setting() {
        // This doesn't have any settings, so just return true.
        return true;
    }

    /**
     * Write the setting.
     *
     * @param mixed $data
     */
    public function write_setting($data) {
        return '';
    }

    /**
     * Output the HTML for the setting.
     *
     * @param mixed $data
     * @param string $query
     * @return string
     */
    public function output_html($data, $query = '') {
        global $OUTPUT, $PAGE;

        $html = '';

        // Check settings buttons.
        $html .= html_writer::tag('button', get_string('settings_check_settings', 'local_copilot'),
            ['class' => 'btn btn-primary local_copilot_check_settings', 'id' => 'check_settings']);

        // Check settings results.
        $html .= html_writer::tag('div', '', ['id' => 'check-settings-results']);

        // Ajax request to check the settings.
        $ajaxurl = new moodle_url('/local/copilot/ajax.php');
        $PAGE->requires->js_call_amd('local_copilot/check_settings', 'init', [
            [
                'url' => $ajaxurl->out(),
                'iconsuccess' => $OUTPUT->pix_icon('t/check', 'success', 'moodle'),
                'iconinfo' => $OUTPUT->pix_icon('i/info', 'information', 'moodle'),
                'iconerror' => $OUTPUT->pix_icon('t/delete', 'error', 'moodle'),
                'strcheck' => addslashes(get_string('settings_check_settings', 'local_copilot')),
                'strchecking' => addslashes(get_string('settings_check_settings_checking', 'local_copilot')),
                'elementid' => 'admin-' . $this->name,
            ],
        ]);

        return format_admin_setting($this, $this->visiblename, $html, $this->description, true, '', null, $query);
    }
}
