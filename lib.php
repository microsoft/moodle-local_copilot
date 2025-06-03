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
 * Plugin library.
 *
 * @package local_copilot
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license https://opensource.org/license/MIT MIT License
 * @copyright (C) 2024 onwards Microsoft, Inc. (http://microsoft.com/)
 */

use local_copilot\manifest_generator;

/**
 * Serve files in local_copilot plugin.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return false
 */
function local_copilot_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    require_login(null, false);

    $fs = get_file_storage();

    $filename = array_pop($args);
    $roles = [manifest_generator::ROLE_TYPE_TEACHER, manifest_generator::ROLE_TYPE_STUDENT];
    $icons = ['color', 'outline'];
    $fileareanames = [];
    foreach ($roles as $role) {
        foreach ($icons as $icon) {
            $fileareanames[] = 'manifest_setting_' . $role . '_' . $icon;
        }
    }
    if (in_array($filearea, $fileareanames) && (in_array($filename, 'color.png', 'outline.png')) &&
        str_ends_with($filearea, substr($filename, 0, strpos($filename, '.png'))) !== false) {
        $fullpath = "/{$context->id}/local_copilot/{$filearea}/0/{$filename}";
        if (!$file = $fs->get_file_by_hash(sha1($fullpath))) {
            return false;
        }

        send_stored_file($file, 0, 0, $forcedownload, $options);
    }

    return false;
}
