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
 * @license https://opensource.org/license/MIT MIT License
 * @copyright (C) 2024 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot\local\adminsetting;

use admin_setting;
use html_writer;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/adminlib.php');

/**
 * Admin setting to detect and set required settings in Moodle.
 */
class check_settings extends admin_setting {
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
        global $OUTPUT;

        $html = '';

        // Check settings buttons.
        $html .= html_writer::tag('button', get_string('settings_check_settings', 'local_copilot'),
            ['class' => 'btn btn-primary local_copilot_check_settings', 'id' => 'check_settings']);

        // Check settings results.
        $html .= html_writer::tag('div', '', ['id' => 'check-settings-results']);

        // Add the JavaScript file.
        $scripturl = new moodle_url('/local/copilot/classes/local/adminsetting/check_settings.js');
        $html .= html_writer::tag('script', '', ['src' => $scripturl]);

        // Ajax request to check the settings.
        $ajaxurl = new moodle_url('/local/copilot/ajax.php');
        $html .= '
<script>
    $(function() {
        var opts = {
            url: "' . $ajaxurl->out() . '",
            iconsuccess: "' . addslashes($OUTPUT->pix_icon('t/check', 'success', 'moodle')) . '",
            iconinfo: "' . addslashes($OUTPUT->pix_icon('i/info', 'information', 'moodle')) . '",
            iconerror: "' . addslashes($OUTPUT->pix_icon('t/delete', 'error', 'moodle')) . '",
            strcheck: "' . addslashes(get_string('settings_check_settings', 'local_copilot')) . '",
            strchecking: "' . addslashes(get_string('settings_check_settings_checking', 'local_copilot')) . '",
        };
        $("#admin-' . $this->name . '").check_settings(opts);
    });
</script>';

        return format_admin_setting($this, $this->visiblename, $html, $this->description, true, '', null, $query);
    }
}
