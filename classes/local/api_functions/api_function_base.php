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
 * API function base class.
 *
 * @package local_copilot
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license https://opensource.org/license/MIT MIT License
 * @copyright (C) 2024 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot\local\api_functions;

/**
 * API function base class.
 */
class api_function_base {
    /**
     * @var string $path
     */
    protected string $path;
    /**
     * @var string $method
     */
    protected string $method;
    /**
     * @var string $summary
     */
    protected string $summary;
    /**
     * @var string $description
     */
    protected string $description;
    /**
     * @var string $operationid
     */
    protected string $operationid;
    /**
     * @var string $scopesuffix
     */
    protected string $scopesuffix;
    /**
     * @var array $requestbody
     */
    protected array $requestbody;
    /**
     * @var array $parameters
     */
    protected array $parameters;
    /**
     * @var array $responses
     */
    protected array $responses;
    /**
     * @var array $responsesemantics
     */
    protected array $responsesemantics = [];
    /**
     * @var array $confirmation
     */
    protected array $confirmation = [];
    /**
     * @var string $instructions
     */
    protected string $instructions;
    /**
     * @var int $sortorder
     */
    protected int $sortorder = 0;
    /**
     * @var bool $supportpagination
     */
    protected bool $supportpagination = false;
    /**
     * @var bool $enabled
     */
    protected bool $enabled = true;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->requestbody = [
            'content' => [
                'application/json' => [
                    'schema' => [
                        'type' => 'object',
                    ],
                ],
            ],
        ];
    }

    /**
     * Check if the API function is applicable to the given role type.
     *
     * @param string $roletype
     * @return bool
     */
    public static function check_applicable_role_type(string $roletype): bool {
        return false;
    }

    /**
     * Return the OpenAPI specification content for the API function.
     *
     * @param string $role
     * @return array
     */
    public function get_open_api_specification_path_content(string $role): array {
        return [
            $this->path => [
                $this->method => [
                    'summary' => $this->summary,
                    'description' => $this->description,
                    'operationId' => $this->operationid,
                    'requestBody' => $this->requestbody,
                    'security' => [
                        [
                            'OAuth2' => [
                                $role . '.' .$this->scopesuffix,
                            ],
                        ],
                    ],
                    'parameters' => $this->parameters,
                    'responses' => $this->responses,
                ],
            ],
        ];
    }

    /**
     * Return the API plugin function content to be inserted into the API plugin manifest.
     *
     * @return array
     */
    public function get_api_plugin_function_content(): array {
        $capabilities = [];
        if ($this->responsesemantics) {
            $capabilities['response_semantics'] = $this->responsesemantics;
        }
        if ($this->confirmation) {
            $capabilities['confirmation'] = $this->confirmation;
        }
        return [
            'name' => $this->operationid,
            'description' => $this->description,
            'capabilities' => $capabilities,
        ];
    }

    /**
     * Return the operation ID.
     *
     * @return string
     */
    public function get_operationid(): string {
        return $this->operationid;
    }

    /**
     * Return the instruction.
     *
     * @return string
     */
    public function get_instructions(): string {
        return $this->instructions;
    }

    /**
     * Return the sort order.
     *
     * @return int
     */
    public function get_sortorder(): int {
        return $this->sortorder;
    }

    /**
     * Return whether the API function is enabled.
     *
     * @return bool
     */
    public function is_enabled(): bool {
        return $this->enabled;
    }

    /**
     * Return whether the API function supports pagination.
     *
     * @return bool
     */
    public function support_pagination(): bool {
        return $this->supportpagination;
    }
}
