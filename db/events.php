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
 * Observers definition.
 *
 * @package local_copilot
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license https://opensource.org/license/MIT MIT License
 * @copyright (C) 2024 onwards Microsoft, Inc. (http://microsoft.com/)
 */

defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => '\local_oauth2\event\access_token_created',
        'callback' => '\local_copilot\observers::handle_access_token_created_or_updated',
        'priority' => 200,
        'internal' => false,
    ],
    [
        'eventname' => '\local_oauth2\event\access_token_updated',
        'callback' => '\local_copilot\observers::handle_access_token_created_or_updated',
        'priority' => 200,
        'internal' => false,
    ],
];
