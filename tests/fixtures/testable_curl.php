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
 * Curl test double returning a canned response body and headers.
 *
 * @package local_copilot
 * @category test
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2026 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/filelib.php');

/**
 * Curl test double returning a canned response, without making any network request.
 *
 * Unlike curl::mock_response(), this allows the response headers (e.g. Content-Type) to be controlled.
 */
class testable_curl extends \curl {
    /** @var string response body to return. */
    protected $body;

    /** @var int HTTP status code to report. */
    protected $httpcode;

    /** @var array response headers to report, keyed by header name. */
    protected $headers;

    /**
     * Constructor.
     *
     * @param string $body response body to return
     * @param int $httpcode HTTP status code to report
     * @param array $headers response headers to report, keyed by header name
     */
    public function __construct(string $body, int $httpcode = 200, array $headers = []) {
        parent::__construct();
        $this->body = $body;
        $this->httpcode = $httpcode;
        $this->headers = $headers;
    }

    /**
     * Return the canned response body instead of making an HTTP GET request.
     *
     * @param string $url
     * @param array $params
     * @param array $options
     * @return string
     */
    public function get($url, $params = [], $options = []) {
        return $this->body;
    }

    /**
     * Return the canned request info, including the content type reported by the response headers.
     *
     * @return array
     */
    public function get_info() {
        $info = ['http_code' => $this->httpcode];
        foreach ($this->headers as $name => $value) {
            if (strtolower($name) === 'content-type') {
                $info['content_type'] = $value;
            }
        }
        return $info;
    }

    /**
     * Return the canned response headers.
     *
     * @return array
     */
    public function getResponse() { // phpcs:ignore moodle.NamingConventions.ValidFunctionName.LowercaseMethod
        return $this->headers;
    }

    /**
     * Report that no curl error occurred.
     *
     * @return int
     */
    public function get_errno() {
        return 0;
    }
}
