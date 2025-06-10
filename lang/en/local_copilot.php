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
 * English language strings for local_copilot.
 *
 * @package local_copilot
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license https://opensource.org/license/MIT MIT License
 * @copyright (C) 2024 onwards Microsoft, Inc. (http://microsoft.com/)
 */

defined('MOODLE_INTERNAL') || die();

// phpcs:disable moodle.Files.LangFilesOrdering.IncorrectOrder -- The strings are organised by features.
// phpcs:disable moodle.Files.LangFilesOrdering.UnexpectedComment -- The strings are organised by features.

// General.
$string['pluginname'] = 'Microsoft 365 Copilot';
$string['paginationlimit'] = 'Number of records to return per request';
$string['paginationlimit_desc'] = 'Number of web service records to return per request.';

// Privacy subsystem.
$string['privacy:metadata'] = 'This plugin does not store personal information.';

// Capabilities.
$string['copilot:download_agent'] = 'Configure and download declarative agents';

// Basic configurations.
$string['settings_feature_description'] = 'This plugin enables Moodle to integrate with Microsoft 365 Copilot by providing declarative agents for teachers and students.<br/>
This integration allows users to access and interact with Moodle data directly through Microsoft 365 Copilot, enhancing productivity and providing quick access to course information, assignments, and other educational resources.';
$string['settings_basic_settings'] = 'Basic settings';
$string['settings_basic_settings_desc'] = '';
$string['settings_check_settings'] = 'Check required settings';
$string['settings_check_settings_desc'] = 'This will make sure that:
<ul>
<li>Web Service is enabled.</li>
<li><a href="https://moodle.org/plugins/webservice_restful" target="_blank">RESTful protocol</a> plugin is installed.</li>
<li>RESTful protocol plugin is enabled.</li>
<li>RESTful protocol plugin is properly configured.</li>
<li>Microsoft 365 Copilot Web Services is enabled.</li>
<li>Authenticated user role has capability to create web service token.</li>
<li>Authenticated user role has capability to use RESTful protocol.</li>
<li>There are at least one <a href="{$a}" target="_blank">OAuth2 client</a> configured.</li>
</ul>';
$string['settings_check_settings_checking'] = 'Checking...';
$string['settings_notice_web_service_already_enabled'] = 'Web Service is already enabled.';
$string['settings_notice_web_service_enabled'] = 'Web Service has been enabled.';
$string['settings_notice_restful_webservice_already_enabled'] = 'RESTful protocol plugin is already enabled.';
$string['settings_notice_restful_webservice_enabled'] = 'RESTful protocol plugin has been enabled.';
$string['settings_notice_error_restful_webservice_not_enabled'] = 'Error occurred when trying to enable RESTful protocol plugin.';
$string['settings_notice_copilot_webservice_already_enabled'] = 'Microsoft 365 Copilot Web Services is already enabled.';
$string['settings_notice_copilot_webservice_enabled'] = 'Microsoft 365 Copilot Web Services has been enabled.';
$string['settings_notice_authenticated_user_already_has_create_token_capability'] = 'Authenticated user role already has capability to create web service token.';
$string['settings_notice_authenticated_user_assigned_create_token_capability'] = 'Authenticated user role has been assigned the capability to create web service token.';
$string['settings_notice_error_assigning_create_token_capability'] = 'Error happened when trying to grant authenticated user role the capability to create web service token.';
$string['settings_notice_error_capability_not_exist'] = 'Capability webservice/restful:use does not exist.';
$string['settings_notice_error_restful_webservice_not_installed'] = 'RESTful protocol plugin is not installed.';
$string['settings_notice_authenticated_user_already_has_use_restful_capability'] = 'Authenticated user role already has capability to use RESTful protocol.';
$string['settings_notice_authenticated_user_assigned_use_restful_capability'] = 'Authenticated user role has been assigned the capability to use RESTful protocol.';
$string['settings_notice_error_assigning_use_restful_capability'] = 'Error happened when trying to grant authenticated user role the capability to use RESTful protocol.';
$string['settings_notice_oauth_clients_exist'] = '<a href="{$a}" target="_blank">At least one OAuth clients exist</a>. Make sure it is for Microsoft 365 Copilot.';
$string['settings_notice_error_no_oauth_clients'] = 'No OAuth clients found. Please <a href="{$a}" target="_blank">add Microsoft 365 Copilot as an OAuth client.</a>';
$string['settings_notice_restful_webservice_accept_header_support_enabled'] = 'RESTful protocol plugin has been configured to support Accept header.';
$string['settings_notice_error_restful_webservice_accept_header_support_not_enabled'] = 'RESTful protocol plugin has not been configured to support Accept header. Please <a href="{$a}" target="_blank">enable Accept header support</a> in the RESTful protocol plugin settings.';
$string['settings_notice_restful_webservice_accept_header_support_already_enabled'] = 'RESTful protocol plugin already supports Accept header.';
$string['settings_notice_restful_webservice_default_accept_header_set'] = 'RESTful protocol plugin has been configured to use "json" as the default Accept header.';
$string['settings_notice_error_restful_webservice_default_accept_header_not_set'] = 'RESTful protocol plugin has not been configured to use "json" as the default Accept header. Please <a href="{$a}" target="_blank">set "json" as the default Accept header</a> in the RESTful protocol plugin settings.';
$string['settings_notice_restful_webservice_default_accept_header_already_set'] = 'RESTful protocol plugin already uses "json" as the default Accept header.';
$string['settings_oauth_client_ids'] = 'Copilot OAuth clients';
$string['settings_oauth_client_ids_desc'] = 'Select all the OAuth client IDs for Microsoft 365 Copilot as configured on the <a href="{$a}" target="_blank">OAuth provider settings</a> page.<br/>
If you have multiple OAuth clients, either for the same tenant or different tenants, select all of them.';
$string['settings_teacher_oauth_client_id'] = 'Teacher OAuth client ID';
$string['settings_teacher_oauth_client_id_desc'] = 'Select the OAuth client ID for the teacher app for Microsoft 365 Copilot as configured on the <a href="{$a}" target="_blank">OAuth provider settings</a> page.';
$string['settings_student_oauth_client_id'] = 'Student OAuth client ID';
$string['settings_student_oauth_client_id_desc'] = 'Select the OAuth client ID for the student app for Microsoft 365 Copilot as configured on the <a href="{$a}" target="_blank">OAuth provider settings</a> page.';

// Configure teacher and student agents.
$string['settings_configure_teacher_agent'] = 'Configure teacher agent app';
$string['settings_configure_student_agent'] = 'Configure student agent app';
$string['app_external_id'] = 'Agent app external ID';
$string['app_external_id_help'] = 'Keep default value of <b>{$a->id}</b> unless you have multiple {$a->role} apps in a single tenant.';
$string['app_short_name'] = 'Agent app short name';
$string['app_short_name_help'] = 'The short name of the app that is displayed to Microsoft 365 admins when managing apps.';
$string['app_full_name'] = 'Agent app full name';
$string['app_full_name_help'] = 'The full name of the app that is displayed to Microsoft 365 admins when managing apps.';
$string['app_short_description'] = 'Agent app short description';
$string['app_short_description_help'] = 'The short description of the app that is displayed to Microsoft 365 admins when managing apps.';
$string['app_full_description'] = 'Agent app full description';
$string['app_full_description_help'] = 'The full description of the app that is displayed to Microsoft 365 admins when managing apps.';
$string['app_version'] = 'Agent app version';
$string['app_version_help'] = ' The version of the app that is displayed to Microsoft 365 admins when managing apps.</br>
Version needs to be in the format of <b>major.minor.patch</b> (e.g. 1.0.0).</br>
Version can only be increased.</br>
The "patch" part is automatically increased when the app settings are updated.</br>
Teacher app and student app share the same version value.';
$string['accent_color'] = 'Agent app accent color';
$string['accent_color_help'] = 'The color that is used as the accent color for the app in Microsoft 365. Use a hexadecimal color code.';
$string['agent_display_name'] = 'Agent display name';
$string['agent_display_name_help'] = 'The name that is displayed to users when they access the agent from Microsoft 365 Copilot.';
$string['agent_description'] = 'Agent description';
$string['agent_description_help'] = 'The description that is displayed to users when they access the agent from Microsoft 365 Copilot.';
$string['agent_instructions'] = 'Agent instructions';
$string['agent_instructions_help'] = 'The instructions are crucial in helping Microsoft 365 Copilot understand the functionality of the agent. The default value should be kept unless you have special requirements.';
$string['color_icon'] = 'Agent color icon';
$string['color_icon_help'] = 'A 192px X 192px full color icon in .png format. This is the icon that is displayed to users when they access the agent from Microsoft 365 Copilot.';
$string['outline_icon'] = 'Agent outline icon';
$string['outline_icon_help'] = 'A 32px X 32px outline icon in .png format. This is the icon that is displayed to users when they access the agent from Microsoft 365 Copilot.';
$string['agent_oauth_client_registration_id'] = 'Teams developer portal OAuth client registration ID';
$string['agent_oauth_client_registration_id_steps'] = 'Teams developer portal OAuth client registration steps';
$string['agent_oauth_client_registration_id_help'] = 'The Moodle OAuth server needs to be registered with the <a href="https://dev.teams.microsoft.com/oauth-configuration" target="_blank">Teams developer portal</a> with following details:
<ul>
<li>Registration name: <b>{$a->site_name} local_oauth2 OAuth</b></li>
<li>Base URL: <b>{$a->wwwroot}</b></li>
<li>Restrict usage by org: <b>My organization only</b></li>
<li>Restrict usage by app: <b>Any Teams app</b></li>
<li>Client ID: <b>{$a->client_id}</b></li>
<li>Client secret: <b>{$a->client_secret}</b></li>
<li>Authorization endpoint: <b>{$a->authorization_endpoint}</b></li>
<li>Token endpoint: <b>{$a->token_endpoint}</b></li>
<li>Refresh endpoint: <b>{$a->refresh_endpoint}</b></li>
<li>Scope: <b>{$a->scope}</b></li>
<li>Enable Proof Key for Code Exchange (PKCE): <b>unchecked</b></li>
</ul>';
$string['agent_plugin_name'] = 'Declarative agent plugin name';
$string['agent_plugin_name_help'] = 'The name that is displayed to users when they access the agent from Microsoft 365 Copilot.';
$string['agent_plugin_description'] = 'Declarative agent plugin description';
$string['agent_plugin_description_help'] = 'The description that is displayed to users when they access the agent from Microsoft 365 Copilot.';
$string['settings_capabilities_and_knowledge_sources'] = 'Capability and knowledge sources';
$string['settings_capabilities_and_knowledge_sources_desc'] = '<ul>
<li>Some capabilities and knowledge sources require a M365 Copilot license or metered usage.</li>
<li>For more information, please check <a href="https://learn.microsoft.com/en-us/microsoft-365-copilot/extensibility/knowledge-sources" target="_blank">Add knowledge sources to your declarative agent | Microsoft Learn</a></li>
</ul>';
$string['enable_code_interpreter'] = 'Enable <a href="https://learn.microsoft.com/en-us/microsoft-365-copilot/extensibility/code-interpreter" target="_blank">code interpreter capability</a>';
$string['enable_image_generator'] = 'Enable <a href="https://learn.microsoft.com/en-us/microsoft-365-copilot/extensibility/image-generator" target=_blank">image generator capability</a>';
$string['enable_copilot_connectors'] = 'Enable <a href="https://learn.microsoft.com/en-us/microsoft-365-copilot/extensibility/knowledge-sources#copilot-connectors" target="_blank">copilot connectors capability</a>';
$string['copilot_connectors_connection_ids'] = 'Copilot connectors';
$string['copilot_connectors_connection_ids_help'] = 'Optional.<br/>
This setting contains a list of objects that identify the Copilot connectors available to the declarative agent.<br/>
If this setting is omitted, all Copilot connectors in the organization are available to the declarative agent.<br/>
Each item contains the following properties:
<ul>
<li><b>connection_id</b>: String, required. The unique identifier of the Copilot connector.</li>
<li><b>additional_search_terms</b>: String, optional. A Keyword Query Language (KQL) query to filter items based on fields in the connection\'s schema.</li>
<li><b>items_by_external_id</b>: Array of Item identify object, optional. Specifies specific items by ID in the Copilot connector that are available to the agent. Each item in the array contains the following properties:
<ul>
<li><b>item_id</b>: String, required. The unique identifier of the external item.</li>
</ul>
</li>
<li><b>items_by_path</b>: Array of Path object, optional. Filters the items available to the agent by item paths (the itemPath semantic label on items). Each item in the array contains the following properties:
<ul>
<li><b>path</b>: String, required. The path (itemPath semantic label value) of the external item.</li>
</ul>
</li>
<li><b>items_by_container_name</b>: Array of Container name object, optional. Filters the items available to the agent by container name (the containerName semantic label on items). Each item in the array contains the following properties:
<ul>
<li><b>container_name</b>: String, required. The name of the container (containerName semantic label value) of the external item.</li>
</ul>
</li>
<li><b>items_by_container_url</b>: Array of Container URL object, optional. Filters the items available to the agent by container URL (the containerUrl semantic label on items). Each item in the array contains the following properties:
<ul>
<li><b>container_url</b>: String required. The URL of the container (containerUrl semantic label value) of the external item.</li>
</ul>
</li>
</ul>
Each item should be provided in JSON format, one per line. For example:
<pre>
{"connection_id": "00000000-0000-0000-0000-000000000000", "additional_search_terms": "field1:value1 AND field2:value2"}
{"connection_id": "11111111-1111-1111-1111-111111111111", "items_by_external_id": [{"item_id": "22222222-2222-2222-2222-222222222222"}]}
{"connection_id": "33333333-3333-3333-3333-333333333333", "items_by_path": [{"path": "/path/to/item"}]}
{"connection_id": "44444444-4444-4444-4444-444444444444", "items_by_container_name": [{"container_name": "Container Name"}]}
{"connection_id": "55555555-5555-5555-5555-555555555555", "items_by_container_url": [{"container_url": "https://example.com/container"}]}
{"connection_id": "66666666-6666-6666-6666-666666666666", "items_by_external_id": [{"item_id": "77777777-7777-7777-7777-777777777777"}], "items_by_path": [{"path": "/another/path/to/item"}]}
{"connection_id": "88888888-8888-8888-8888-888888888888", "items_by_container_name": [{"container_name": "Another Container Name"}], "items_by_container_url": [{"container_url": "https://example.com/another/container"}]}
{"connection_id": "99999999-9999-9999-9999-999999999999"}
</pre>';
$string['enable_sharepoint_onedrive'] = 'Enable <a href="https://learn.microsoft.com/en-us/microsoft-365-copilot/extensibility/knowledge-sources#sharepoint-and-onedrive" target="_blank">SharePoint and OneDrive capability</a>';
$string['enable_sharepoint_onedrive_help'] = 'Access to SharePoint and OneDrive data can optionally be restricted by SharePoint IDs and/or URLs using the two respective settings below. If both settings are omitted, all OneDrive and Sharepoint sources in the organization are available to the declarative agent.';
$string['sharepoint_items_by_sharepoint_ids'] = 'SharePoint items by SharePoint IDs';
$string['sharepoint_items_by_sharepoint_ids_help'] = 'Optional.<br/>
This setting contains a list of objects that identify SharePoint or OneDrive sources using IDs.<br/>
Each item contains the following properties:
<ul>
<li><b>site_id</b>: String, optional. A unique GUID identifier for a SharePoint or OneDrive site.</li>
<li><b>web_id</b>: String, optional. A unique GUID identifier for a specific web within a SharePoint or OneDrive site.</li>
<li><b>list_id</b>: String, optional. A unique GUID identifier for a list within a SharePoint or OneDrive site.</li>
<li><b>unique_id</b>: String, optional. A unique GUID identifier used to represent a specific entity or resource.</li>
<li><b>search_associated_sites</b>: Boolean, optional. Indicates whether to enable searching associated sites. This value is only applicable when the site_id value references a SharePoint HubSite.</li>
<li><b>part_type</b>: String, optional. Indicates the type of part part_id refers to. This value is only applicable when the part_id value is present. Possible values are: OneNotePart.</li>
<li><b>part_id</b>: String, optional. A unique GUID identifier used to represent part of a SharePoint item such as a OneNote page.</li>
</ul>
Each item should be provided in JSON format, one per line. For example:
<pre>
{"site_id": "00000000-0000-0000-0000-000000000000", "web_id": "00000000-0000-0000-0000-000000000001", "list_id": "00000000-0000-0000-0000-000000000002", "unique_id": "00000000-0000-0000-0000-000000000003"}
{"site_id": "11111111-1111-1111-1111-111111111111"}
{"web_id": "22222222-2222-2222-2222-222222222222", "list_id": "33333333-3333-3333-3333-333333333333"}
{"unique_id": "44444444-4444-4444-4444-444444444444"}
</pre>';
$string['sharepoint_items_by_url'] = 'SharePoint items by URL';
$string['sharepoint_items_by_url_help'] = 'Optional.<br/>
This setting contains a list of absolute URLs to the SharePoint or OneDrive resource, one per line.';
$string['enable_web_search'] = 'Enable <a href="https://learn.microsoft.com/en-us/microsoft-365-copilot/extensibility/knowledge-sources#web-and-scoped-web-search" target="_blank">web and scoped web search capability</a>';
$string['scoped_web_search_sites'] = 'Scoped web search sites';
$string['scoped_web_search_sites_help'] = 'Optional.<br/>
Provide up to 4 site URLs, one per line, to limit the web search capability to these sites.<br/>
If not provided, the web search capability will use any web data.<br/>
The following limitations apply to the URLs provided:<ul>
<li>The URL MUST NOT contain more than two path segments, although search results include data from additional path segments.</li>
<li>The URL MUST NOT contain any query parameters.</li>
</ul>';
$string['error_invalid_accent_color'] = 'Invalid accent color';
$string['agent_config_saved'] = 'Agent configuration saved successfully.';
$string['download_manifest'] = 'Download manifest';
$string['configure_app_in_teams_dev_portal'] = '<b>After the app is uploaded to Microsoft 365 admin center, please remember to update the application ID in the OAuth client registration in the Teams Developer Portal.</b>';
$string['error_creating_manifest'] = 'Error occurred while creating manifest';
$string['error_invalid_color_icon_size'] = 'Invalid color icon size. Color icon must be 192px X 192px.';
$string['error_invalid_outline_icon_size'] = 'Invalid outline icon size. Outline icon must be 32px X 32px.';
$string['error_invalid_app_version'] = 'Invalid app version. Version needs to be in the format of major.minor.patch (e.g. 1.0.0).';
$string['error_decreased_app_version'] = 'Version can only be increased.';
$string['error_instructions_too_long'] = 'Agent instructions, including the instructions for all API functions, exceed the maximum length of 8,000 characters. Please shorten the instructions.';
$string['error_invalid_role'] = 'Invalid role';
$string['error_invalid_copilot_connector_id_property'] = 'Line {$a->line}: Invalid Copilot connector ID property {$a->name}.';
$string['error_invalid_copilot_connector_id_value'] = 'Line {$a->line}: Invalid Copilot connector ID value for field {$a->name}.';
$string['error_invalid_sharepoint_id_property'] = 'Line {$a->line}: Invalid SharePoint ID property {$a->name}.';
$string['error_invalid_sharepoint_id_value'] = 'Line {$a->line}: Invalid SharePoint ID value for field {$a->name}.';
$string['error_invalid_json_format'] = 'Line {$a->line}: Invalid JSON value.';
$string['error_invalid_sharepoint_item_url'] = 'Line {$a->line}: Invalid URL.';
$string['error_not_sharepoint_onedrive_url'] = 'Line {$a->line}: The URL is not a valid SharePoint or OneDrive URL.';
$string['error_too_many_scoped_web_search_sites'] = 'Too many scoped web search sites. Please provide up to 4 site URLs, one per line.';
$string['error_invalid_scoped_web_search_site'] = 'Invalid scoped web search site URL.';
$string['error_scoped_web_search_site_query_params'] = 'Invalid scoped web search site URL. The URL MUST NOT contain any query parameters.';
$string['error_scoped_web_search_site_path_segments'] = 'Invalid scoped web search site URL. The URL MUST NOT contain more than two path segments.';
