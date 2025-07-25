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
 * This page processes ajax requests.
 *
 * @package local_copilot
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license https://opensource.org/licenses/MIT MIT License
 * @copyright (C) 2024 onwards Microsoft, Inc. (http://microsoft.com/)
 */

use local_copilot\local\page\ajax;

define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../config.php');

require_login();
$mode = required_param('mode', PARAM_TEXT);
require_capability('moodle/site:config', context_system::instance());

$url = '/local/copilot/ajax.php';
$page = new ajax($url, '');
$page->run($mode);
