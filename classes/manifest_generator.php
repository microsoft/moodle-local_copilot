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
 * Microsoft 365 Teams app manifest generator base class.
 *
 * @package local_copilot
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license https://opensource.org/license/MIT MIT License
 * @copyright (C) 2024 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot;

use context_system;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use zip_archive;

/**
 * Class manifest_generator.
 */
class manifest_generator {
    /**
     * @var string Role type for teacher.
     */
    const ROLE_TYPE_TEACHER = 'teacher';
    /**
     * @var string Role type for student.
     */
    const ROLE_TYPE_STUDENT = 'student';

    /**
     * @var string Manifest version.
     */
    const APP_MANIFEST_VERSION = '1.19';
    /**
     * @var string Manifest schema.
     */
    const APP_MANIFEST_SCHEMA = 'https://developer.microsoft.com/json-schemas/teams/v1.19/MicrosoftTeams.schema.json';
    /**
     * @var string Developer information.
     */
    const DEVELOPER = [
        'name' => 'Enovation Solutions',
        'websiteUrl' => 'https://enovation.ie',
        'privacyUrl' => 'https://enovation.ie/privacy-policy/',
        'termsOfUseUrl' => 'https://enovation.ie/terms-and-conditions/',
        'mpnId' => '1718735',
    ];
    /**
     * @var string Agent manifest schema.
     */
    const AGENT_MANIFEST_SCHEMA = 'https://developer.microsoft.com/json-schemas/copilot/declarative-agent/v1.4/schema.json';
    /**
     * @var string Agent manifest version.
     */
    const AGENT_MANIFEST_VERSION = 'v1.4';
    /**
     * @var string Plugin manifest schema.
     */
    const PLUGIN_MANIFEST_SCHEMA = 'https://developer.microsoft.com/json-schemas/copilot/plugin/v2.2/schema.json';
    /**
     * @var string Plugin manifest version.
     */
    const PLUGIN_MANIFEST_VERSION = 'v2.2';
    /**
     * @var string OpenAPI version.
     */
    const OPENAPI_VERSION = '3.0.0';
    /**
     * @var int Instruction text length limit.
     */
    const INSTRUCTIONS_LENGTH_LIMIT = 8000;

    /**
     * @var string Role type.
     */
    private string $role;

    /**
     * @var array Functions.
     */
    private array $functions;

    /**
     * Constructor.
     *
     * @param string $role Role type.
     */
    public function __construct(string $role) {
        $this->role = $role;
        $this->load_functions();
    }

    /**
     * Load API functions that are relevant to the role type.
     */
    private function load_functions(): void {
        global $CFG;

        $functions = [];

        $apifunctionsdir = $CFG->dirroot . '/local/copilot/classes/api_functions';

        $directoryiterator = new RecursiveDirectoryIterator($apifunctionsdir);
        $iterator = new RecursiveIteratorIterator($directoryiterator);
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $classname = 'local_copilot\\api_functions\\' . $file->getBasename('.php');
                $reflection = new ReflectionClass($classname);
                if ($reflection->getMethod('check_applicable_role_type')->invoke(null, $this->role)) {
                    $function = $reflection->newInstance();
                    if ($function->is_enabled()) {
                        $functions[] = $function;
                    }
                }
            }
        }

        // Sort functions by sort order.
        usort($functions, function ($a, $b) {
            return $a->get_sortorder() <=> $b->get_sortorder();
        });

        $this->functions = $functions;
    }

    /**
     * Generate manifest for the app.
     *
     * @return array [error, manifest zip file path]
     */
    public function generate_manifest(): array {
        global $CFG;

        // Step 1: create temporary manifest folder.
        $pathtomanifestfolder = $CFG->dataroot . '/temp/copilot/' . $this->role . '/manifest/' . time() . '_' . random_string(6);
        if (file_exists($pathtomanifestfolder)) {
            $this->rm_dir($pathtomanifestfolder);
        }
        mkdir($pathtomanifestfolder, 0777, true);

        // Step 2: create app manifest.
        $appmanifestcontent = $this->get_app_manifest_content();
        $appmanifestfile = $pathtomanifestfolder . '/manifest.json';
        file_put_contents($appmanifestfile, $appmanifestcontent);

        // Step 3: create agent manifest.
        $agentmanifestcontent = $this->get_agent_manifest_content();
        $agentmanifestfile = $pathtomanifestfolder . '/moodle' . ucfirst($this->role) . 'Agent.json';
        file_put_contents($agentmanifestfile, $agentmanifestcontent);

        // Step 4: create plugin manifest.
        $pluginmanifestcontent = $this->get_plugin_manifest_content();
        $pluginmanifestfile = $pathtomanifestfolder . '/moodle' . ucfirst($this->role) . 'Plugin.json';
        file_put_contents($pluginmanifestfile, $pluginmanifestcontent);

        // Step 5: create OpenAPI spec.
        $openapispeccontent = $this->get_openapi_spec_content();
        $openapispecfile = $pathtomanifestfolder . '/moodle' . ucfirst($this->role) . 'API.json';
        file_put_contents($openapispecfile, $openapispeccontent);

        // Step 6: prepare icons.
        $fs = get_file_storage();
        $context = context_system::instance();
        $filearea = 'manifest_setting_' . $this->role . '_color';
        $files = $fs->get_area_files($context->id, 'local_copilot', $filearea, 0);
        foreach ($files as $file) {
            if ($file->get_filename() === 'color.png') {
                $file->copy_content_to($pathtomanifestfolder . '/color.png');
            }
        }

        $filearea = 'manifest_setting_' . $this->role . '_outline';
        $files = $fs->get_area_files($context->id, 'local_copilot', $filearea, 0);
        foreach ($files as $file) {
            if ($file->get_filename() === 'outline.png') {
                $file->copy_content_to($pathtomanifestfolder . '/outline.png');
            }
        }

        // Step 7: create zip file.
        $ziparchive = new zip_archive();
        $zipfilename = $pathtomanifestfolder . '/manifest.zip';
        $ziparchive->open($zipfilename);
        $filenames = [
            'manifest.json',
            'moodle' . ucfirst($this->role) . 'Agent.json',
            'moodle' . ucfirst($this->role) . 'Plugin.json',
            'moodle' . ucfirst($this->role) . 'API.json',
            'color.png',
            'outline.png',
        ];
        foreach ($filenames as $filename) {
            $ziparchive->add_file_from_pathname($filename, $pathtomanifestfolder . '/' . $filename);
        }
        $ziparchive->close();

        return $zipfilename ? [null, $zipfilename] : ['error_creating_manifest', null];
    }

    /**
     * Remove directory and all its contents.
     *
     * @param string $path Path to the directory.
     */
    private function rm_dir(string $path) {
        $it = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($path);
    }

    /**
     * Get app manifest content.
     *
     * @return string
     */
    private function get_app_manifest_content(): string {
        $appmanifest = [
            '$schema' => static::APP_MANIFEST_SCHEMA,
            'manifestVersion' => static::APP_MANIFEST_VERSION,
            'version' => static::get_app_version(),
            'id' => get_config('local_copilot', $this->role . '_agent_app_external_id'),
            'developer' => static::DEVELOPER,
            'icons' => [
                'color' => 'color.png',
                'outline' => 'outline.png',
            ],
            'name' => [
                'short' => get_config('local_copilot', $this->role . '_agent_app_short_name'),
                'full' => get_config('local_copilot', $this->role . '_agent_app_full_name'),
            ],
            'description' => [
                'short' => get_config('local_copilot', $this->role . '_agent_app_short_description'),
                'full' => get_config('local_copilot', $this->role . '_agent_app_full_description'),
            ],
            'accentColor' => get_config('local_copilot', $this->role . '_agent_accent_color'),
            'composeExtensions' => [],
            'permissions' => [
                'identity',
                'messageTeamMembers',
            ],
            'copilotAgents' => [
                'declarativeAgents' => [
                    [
                        'id' => 'moodle' . ucfirst($this->role) . 'Agent',
                        'file' => 'moodle' . ucfirst($this->role) . 'Agent.json',
                    ],
                ],
            ],
            'validDomains' => [],
        ];

        return json_encode($appmanifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get agent manifest content.
     *
     * @return string
     */
    private function get_agent_manifest_content(): string {
        // Prepare capabilities.
        // Code Interpreter is always enabled.
        $capabilities = [
            ['name' => 'CodeInterpreter'],
        ];

        // Image generator capability.
        if (get_config('local_copilot', $this->role . '_agent_capability_image_generator')) {
            $capabilities[] = ['name' => 'GraphicArt'];
        }

        // Copilot connectors capability.
        if (get_config('local_copilot', $this->role . '_agent_capability_copilot_connectors')) {
            $graphconnectorcapability = ['name' => 'GraphConnectors'];
            $connectionids = get_config('local_copilot', $this->role . '_agent_copilot_connectors_connection_ids');
            $connectionids = explode("\n", $connectionids);
            if ($connectionids) {
                $cleanedconnectionids = [];
                foreach ($connectionids as $connectionid) {
                    $cleanedconnectionids[] = ['connection_id' => trim($connectionid)];
                }
                if ($cleanedconnectionids) {
                    $graphconnectorcapability['connections'] = $cleanedconnectionids;
                }
            }
            $capabilities[] = $graphconnectorcapability;
        }

        // OneDrive and SharePoint capability.
        if (get_config('local_copilot', $this->role . '_agent_capability_sharepoint_onedrive')) {
            $sharepointcapability = ['name' => 'OneDriveAndSharePoint'];

            // Items by SharePoint IDs.
            $itemsbysharepointids = get_config('local_copilot', $this->role . '_agent_sharepoint_items_by_sharepoint_ids');
            $itemsbysharepointids = explode("\n", $itemsbysharepointids);
            if ($itemsbysharepointids) {
                $cleaneditemsbysharepointids = [];
                foreach ($itemsbysharepointids as $itembysharepointid) {
                    $cleaneditemsbysharepointids[] = json_decode($itembysharepointid, true);
                }
                if ($cleaneditemsbysharepointids) {
                    $sharepointcapability['items_by_sharepoint_ids'] = $cleaneditemsbysharepointids;
                }
            }

            // Items by URL.
            $itemsbyurl = get_config('local_copilot', $this->role . '_agent_sharepoint_items_by_url');
            $itemsbyurl = explode("\n", $itemsbyurl);
            if ($itemsbyurl) {
                $cleaneditemsbyurl = [];
                foreach ($itemsbyurl as $itembyurl) {
                    $cleaneditemsbyurl[] = ['url' => trim($itembyurl)];
                }
                if ($cleaneditemsbyurl) {
                    $sharepointcapability['items_by_url'] = $cleaneditemsbyurl;
                }
            }

            $capabilities[] = $sharepointcapability;
        }

        // Web search capability.
        if (get_config('local_copilot', $this->role . '_agent_capability_web_search')) {
            $websearchcapability = ['name' => 'WebSearch'];
            $websearchsites = get_config('local_copilot', $this->role . '_agent_scoped_web_search_sites');
            $websearchsites = explode("\n", $websearchsites);
            if ($websearchsites) {
                $cleanedwebsearchsites = [];
                foreach ($websearchsites as $websearchsite) {
                    $cleanedwebsearchsites[] = ['url' => $websearchsite];
                }
                if ($cleanedwebsearchsites) {
                    $websearchcapability['sites'] = $cleanedwebsearchsites;
                }
            }
            $capabilities[] = $websearchcapability;
        }

        $agentmanifest = [
            '$schema' => static::AGENT_MANIFEST_SCHEMA,
            'version' => static::AGENT_MANIFEST_VERSION,
            'name' => get_config('local_copilot', $this->role . '_agent_display_name'),
            'description' => get_config('local_copilot', $this->role . '_agent_description'),
            'instructions' => $this->get_instructions(),
            'conversation_starters' => $this->get_conversation_starters_content(),
            'actions' => [
                [
                    'id' => 'moodle-' . $this->role . '-plugin',
                    'file' => 'moodle' . ucfirst($this->role) . 'Plugin.json',
                ],
            ],
            'capabilities' => $capabilities,
        ];

        return json_encode($agentmanifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Construct the instruction content for the agent by combining the agent instructions with the plugin functions instructions.
     *
     * @param bool $functioninstructionsonly Whether to return only function instructions.
     * @param bool $applylimit Whether to apply limit to the instructions.
     * @return string
     */
    public function get_instructions(bool $functioninstructionsonly = false, bool $applylimit = true): string {
        if ($functioninstructionsonly) {
            $instructions = '';
        } else {
            $instructions = get_config('local_copilot', $this->role . '_agent_instructions');
            if (!$instructions) {
                $instructions = '';
            }
        }

        $instructions .= PHP_EOL . 'You have the following skills:';
        $paginatedfunctions = [];

        foreach ($this->functions as $function) {
            $instructions .= PHP_EOL . $function->get_instructions();
            if ($function->support_pagination()) {
                $paginatedfunctions[] = $function;
            }
        }

        if ($paginatedfunctions) {
            $instructions .= PHP_EOL . PHP_EOL .
                'These actions support pagination: ';
            foreach ($paginatedfunctions as $function) {
                $instructions .= $function->get_operationid() . ', ';
            }
            $instructions = rtrim($instructions, ', ') . '.' . PHP_EOL .
                'When using these actions, use parameters "limit" (default to 10) and "offset" (default to 0) to control the ' .
                'number of records returned and the starting point of the records.' . PHP_EOL .
                'If the "has_more" attribute of the last item has value of "true", inform the user that there are more records ' .
                'available and automatically set "offset" value in subsequent calls.';
        }

        if ($applylimit) {
            $instructions = substr($instructions, 0, static::INSTRUCTIONS_LENGTH_LIMIT);
        }

        return $instructions;
    }

    /**
     * Get conversation starters content.
     *
     * @return array
     */
    private function get_conversation_starters_content(): array {
        $conversationstarters = [];

        switch ($this->role) {
            case static::ROLE_TYPE_TEACHER:
                $conversationstarters = [
                    [
                        'title' => 'List Courses',
                        'text' => 'Find Moodle courses that I\'m teaching.',
                    ],
                    [
                        'title' => 'Course Content',
                        'text' => 'Show me the content for ',
                    ],
                    [
                        'title' => 'List Assignments',
                        'text' => 'Find assignments for ',
                    ],
                    [
                        'title' => 'Create Assignment',
                        'text' => 'Create a new assignment ',
                    ],
                    [
                        'title' => 'Assignment Status',
                        'text' => 'Show me how many students have submitted the final assignment for ',
                    ],
                    [
                        'title' => 'Create Announcement',
                        'text' => 'Create an announcement for ',
                    ],
                ];
                break;
            case static::ROLE_TYPE_STUDENT:
                $conversationstarters = [
                    [
                        'title' => 'List Courses',
                        'text' => 'Find Moodle courses that I\'m enrolled in.',
                    ],
                    [
                        'title' => 'Course Content',
                        'text' => 'List activities I have in course ',
                    ],
                    [
                        'title' => 'Course Enrollment',
                        'text' => 'What courses can I enroll myself in?',
                    ],
                    [
                        'title' => 'Find Activities',
                        'text' => 'Are there any activities I need to do?',
                    ],
                    [
                        'title' => 'Grade Information',
                        'text' => 'Show me grade details in for ',
                    ],
                    [
                        'title' => 'Overdue Assignments',
                        'text' => 'Find all overdue assignments.',
                    ],
                ];
                break;
        }

        return $conversationstarters;
    }

    /**
     * Get plugin functions.
     *
     * @return string
     */
    private function get_plugin_manifest_content(): string {
        $pluginmanifest = [
            '$schema' => static::PLUGIN_MANIFEST_SCHEMA,
            'schema_version' => static::PLUGIN_MANIFEST_VERSION,
            'name_for_human' => get_config('local_copilot', $this->role . '_agent_plugin_name'),
            'description_for_human' => get_config('local_copilot', $this->role . '_agent_plugin_description'),
            'namespace' => 'moodle' . ucfirst($this->role) . 'API',
            'functions' => $this->get_plugin_functions_content(),
            'runtimes' => [
                [
                    'type' => 'OpenApi',
                    'auth' => [
                        'type' => 'OAuthPluginVault',
                        'reference_id' => get_config('local_copilot', $this->role . '_oauth_client_registration_id'),
                    ],
                    'spec' => [
                        'url' => 'moodle' . ucfirst($this->role) . 'API.json',
                    ],
                    'run_for_functions' => $this->get_plugin_run_for_functions(),
                ],
            ],
        ];

        return json_encode($pluginmanifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get plugin functions content.
     *
     * @return array
     */
    private function get_plugin_functions_content(): array {
        $functionscontent = [];

        foreach ($this->functions as $function) {
            $functionscontent[] = $function->get_api_plugin_function_content();
        }

        return $functionscontent;
    }

    /**
     * Get plugin run for functions content.
     *
     * @return array
     */
    private function get_plugin_run_for_functions(): array {
        $operationids = [];

        foreach ($this->functions as $function) {
            $operationids[] = $function->get_operationid();
        }

        return $operationids;
    }

    /**
     * Get OpenAPI spec content.
     *
     * @return string
     */
    private function get_openapi_spec_content(): string {
        global $CFG;

        $openapispec = [
            'openapi' => static::OPENAPI_VERSION,
            'info' => [
                'title' => 'Microsoft 365 Copilot Web Services for Moodle ' . ucfirst($this->role) . 's',
                'description' => 'Moodle API functions to allow Microsoft 365 Copilot to access Moodle data for ' . $this->role .
                    's.',
                'version' => static::get_app_version(), // This is set to be the same as the app version.
            ],
            'servers' => [
                [
                    'url' => $CFG->wwwroot . '/webservice/restful/server.php',
                    'description' => 'Moodle RESTful web service endpoint',
                ],
            ],
            'paths' => $this->get_openapi_paths_content(),
            'components' => [
                'securitySchemes' => [
                    'OAuth2' => [
                        'type' => 'oauth2',
                        'flows' => [
                            'authorizationCode' => [
                                'authorizationUrl' => $CFG->wwwroot . '/local/oauth2/login.php',
                                'tokenUrl' => $CFG->wwwroot . '/local/oauth2/token.php',
                                'refreshUrl' => $CFG->wwwroot . '/local/oauth2/refresh_token.php',
                                'scopes' => [
                                    $this->role . '.read' => 'Read Moodle data using ' . $this->role . ' role',
                                    $this->role . '.write' => 'Write Moodle data using ' . $this->role . ' role',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'security' => [
                [
                    'OAuth2' => [
                        $this->role . '.read',
                        $this->role . '.write',
                    ],
                ],
            ],
        ];

        return json_encode($openapispec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get OpenAPI paths content.
     *
     * @return array
     */
    private function get_openapi_paths_content(): array {
        $pathscontent = [];

        foreach ($this->functions as $function) {
            $pathscontent = array_merge($pathscontent, $function->get_open_api_specification_path_content($this->role));
        }

        return $pathscontent;
    }

    /**
     * Get next app version.
     *
     * @param string $appversion Current app version.
     * @return string
     */
    public static function get_next_app_version(string $appversion): string {
        $versionparts = explode('.', $appversion);
        $versionparts[count($versionparts) - 1] = (int)$versionparts[count($versionparts) - 1] + 1;
        return implode('.', $versionparts);
    }

    /**
     * Get app version.
     *
     * @return string
     */
    public static function get_app_version(): string {
        return get_config('local_copilot', 'app_version');
    }
}
