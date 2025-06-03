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

namespace local_copilot;

use context_system;
use local_oauth2\event\access_token_created;
use local_oauth2\event\access_token_updated;
use stdClass;

/**
 * Class observers.
 */
class observers {
    /**
     * Handle access token created or updated event.
     *
     * @param access_token_created|access_token_updated $event
     */
    public static function handle_access_token_created_or_updated(access_token_created|access_token_updated $event) {
        global $DB;

        $data = $event->get_data();
        $userid = $data['userid'];
        $token = $data['other']['accesstoken'];
        $validuntil = $data['other']['expires'];
        $clientid = $data['other']['clientid'];

        // Only create web service token for Copilot clients.
        $copilotoauthclientids = utils::get_selected_oauth_client_options();
        if (!in_array($clientid, $copilotoauthclientids)) {
            return;
        }

        // Insert the token into external_tokens table in Moodle core.
        $copilotexternalserviceid = $DB->get_field('external_services', 'id',
            ['component' => 'local_copilot', 'shortname' => 'copilot_webservices'], MUST_EXIST);

        if ($externaltoken = $DB->get_record('external_tokens',
            ['userid' => $userid, 'externalserviceid' => $copilotexternalserviceid])) {
            // Update an existing token.
            $externaltoken->token = $token;
            $externaltoken->validuntil = $validuntil;
            $externaltoken->timecreated = time();
            $externaltoken->privatetoken = random_string(64);

            $DB->update_record('external_tokens', $externaltoken);
        } else {
            // Create a new token.
            $externaltoken = new stdClass();
            $externaltoken->userid = $userid;
            $externaltoken->externalserviceid = $copilotexternalserviceid;
            $externaltoken->token = $token;
            $externaltoken->tokentype = EXTERNAL_TOKEN_PERMANENT;
            $externaltoken->contextid = context_system::instance()->id;
            $externaltoken->creatorid = $userid;
            $externaltoken->validuntil = $validuntil;
            $externaltoken->timecreated = time();
            $externaltoken->privatetoken = random_string(64);
            $externaltoken->iprestriction = '';
            $externaltoken->name = get_string('tokennameprefix', 'webservice', random_string(5));

            $externaltoken->id = $DB->insert_record('external_tokens', $externaltoken);
        }
    }
}
