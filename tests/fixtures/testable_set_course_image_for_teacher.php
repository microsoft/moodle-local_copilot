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
 * Testable version of the set_course_image_for_teacher external function.
 *
 * @package local_copilot
 * @category test
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2026 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot;

use local_copilot\external\set_course_image_for_teacher;

/**
 * Testable version of set_course_image_for_teacher.
 *
 * Downloads through an injected curl client, and throws an exception instead of sending an HTTP error status and
 * terminating the process.
 */
class testable_set_course_image_for_teacher extends set_course_image_for_teacher {
    /** @var \curl|null curl client to use for the download. */
    public static $curl = null;

    /**
     * Return the injected curl client.
     *
     * @return \curl
     */
    protected static function create_curl(): \curl {
        return self::$curl;
    }

    /**
     * Throw an exception carrying the HTTP error status, instead of terminating the process.
     *
     * @param string $status HTTP status code and reason phrase
     * @return never
     * @throws \RuntimeException always
     */
    protected static function send_error_response(string $status): never {
        throw new \RuntimeException($status);
    }
}
