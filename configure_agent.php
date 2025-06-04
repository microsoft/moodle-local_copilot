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
 * This page allows authorised users to configure and download agent apps.
 *
 * @package local_copilot
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license https://opensource.org/license/MIT MIT License
 * @copyright (C) 2024 onwards Microsoft, Inc. (http://microsoft.com/)
 */

use local_copilot\form\agent_configuration_form;
use local_copilot\manifest_generator;
use local_copilot\utils;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();
require_capability('local/copilot:download_agent', context_system::instance());

$role = required_param('role', PARAM_ALPHANUMEXT);
if (!in_array($role, [manifest_generator::ROLE_TYPE_TEACHER, manifest_generator::ROLE_TYPE_STUDENT])) {
    throw new moodle_exception('error_invalid_role', 'local_copilot');
}

admin_externalpage_setup('local_copilot/configure_' . $role . '_agent');

$action = optional_param('action', '', PARAM_ALPHA);

if ($action == 'download') {
    $manifestgenerator = new manifest_generator($role);
    [$errorcode, $manifestfilepath] = $manifestgenerator->generate_manifest();

    if ($manifestfilepath) {
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename=' . $role . '-agent-' . get_config('local_copilot', 'app_version') .
            '.zip');
        header('Content-Length: ' . filesize($manifestfilepath));
        header('Pragma: no-cache');
        header('Expires: 0');
        readfile($manifestfilepath);
    } else {
        throw new moodle_exception($errorcode, 'local_copilot');
    }
}

$form = new agent_configuration_form(null, ['role' => $role]);
$formdata = utils::get_agent_configuration_form_data($role);
$form->set_data($formdata);

if ($form->is_cancelled()) {
    redirect(new moodle_url('/local/copilot/configure_agent.php', ['role' => $role]));
} else if ($data = $form->get_data()) {
    $settingsupdated = false;

    // Save the data.
    foreach (utils::APP_ROLE_CONFIGURATIONS as $configname) {
        $fullconfigname = $role . '_' . $configname;
        if ($data->$fullconfigname != get_config('local_copilot', $fullconfigname)) {
            $settingsupdated = true;
        }
        set_config($fullconfigname, $data->$fullconfigname, 'local_copilot');
    }

    foreach (utils::APP_ROLE_OPTIONAL_CONFIGURATIONS as $configname) {
        $fullconfigname = $role . '_' . $configname;
        $newconfigdata = empty($data->$fullconfigname) ? null : $data->$fullconfigname;
        $existingconfigdata = get_config('local_copilot', $fullconfigname);
        if (empty($existingconfigdata)) {
            $existingconfigdata = null;
        }

        if ($newconfigdata !== $existingconfigdata) {
            $settingsupdated = true;
        }
        set_config($fullconfigname, $newconfigdata, 'local_copilot');
    }

    // Save icons.
    $icons = ['color', 'outline'];
    $context = context_system::instance();
    $fs = get_file_storage();
    foreach ($icons as $icon) {
        $fieldname = $role . '_agent_' . $icon . '_icon';

        $files = $fs->get_area_files($context->id, 'local_copilot', 'manifest_setting_' . $role . '_' . $icon);
        $originalfilecontenthash = false;
        foreach ($files as $file) {
            if ($file->get_filename() == '.') {
                continue;
            }
            $originalfilecontenthash = $file->get_contenthash();
        }

        file_save_draft_area_files($data->$fieldname, $context->id, 'local_copilot', 'manifest_setting_' . $role . '_' . $icon, 0,
            ['subdirs' => 0, 'accepted_types' => ['.png'], 'maxbytes' => utils::MAX_ICON_SIZE,
                'areamaxbytes' => utils::MAX_ICON_SIZE, 'maxfiles' => 1]);

        $files = $fs->get_area_files($context->id, 'local_copilot', 'manifest_setting_' . $role . '_' . $icon);

        $originalfile = false;
        $newfile = false;
        foreach ($files as $file) {
            if ($file->get_filename() == '.') {
                continue;
            }

            if ($file->get_filename() != $icon . '.png') {
                $newfile = $file;
            } else {
                $originalfile = $file;
            }
        }

        if ($newfile) {
            // Another file with different name was uploaded.
            // We need to delete the original file, and rename the new file.
            if ($originalfile) {
                $originalfile->delete();
            }
            $newfilename = $icon . '.png';
            $newfile->rename($newfile->get_filepath(), $newfilename);

            if ($newfile->get_contenthash() != $originalfilecontenthash) {
                $settingsupdated = true;
            }
        }
    }

    // Save app version.
    $originalappversion = get_config('local_copilot', 'app_version');
    if ($data->app_version != $originalappversion) {
        set_config('app_version', $data->app_version, 'local_copilot');
    } else if ($settingsupdated) {
        // If the app version is not changed, but the settings are updated, we need to update the app version.
        set_config('app_version', manifest_generator::get_next_app_version($originalappversion), 'local_copilot');
    }

    redirect(new moodle_url('/local/copilot/configure_agent.php', ['role' => $role]),
        get_string('agent_config_saved', 'local_copilot'));
}

echo $OUTPUT->header();

$form->display();

echo $OUTPUT->footer();
