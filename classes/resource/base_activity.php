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
 * Resource type "activity" for web service return data in local_copilot.
 *
 * @package local_copilot
 * @author Dorel Manolescu <dorel.manolescu@enovation.ie>
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license https://opensource.org/license/MIT MIT License
 * @copyright (C) 2014 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot\resource;

defined('MOODLE_INTERNAL') || die();

use cm_info;
use core_availability\info_module;
use external_value;
use moodle_url;
use stdClass;
use grade_item;

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->libdir . '/gradelib.php');
/**
 * Section resource type.
 */
class base_activity implements resource_type {
    /**
     * Get the resource return structure.
     *
     * @return array
     */
    public static function get_return_structure(): array {
        return [
            'activity_name' => new external_value(PARAM_TEXT, 'Name of the activity.'),
            'activity_id' => new external_value(PARAM_INT, 'ID of the activity.'),
            'activity_link' => new external_value(PARAM_URL, 'Link to the activity.'),
            'activity_type' => new external_value(PARAM_TEXT,
                'Activity type, also referred as module name. For example, forum, assignment, quiz, etc.'),
            'activity_description' => new external_value(PARAM_RAW, 'Description of the activity in HTML format.'),
            'completion_enabled' => new external_value(PARAM_BOOL, 'Whether completion tracking is enabled in the activity.'),
            'visibility' => new external_value(PARAM_BOOL, 'Whether the activity is visible to students.'),
            'instructions' => new external_value(PARAM_RAW, 'Activity instructions.'),
            'availability' => new external_value(PARAM_RAW, 'Activity availability information.'),
        ];
    }

    /**
     * Extract activity data.
     *
     * @param stdClass $coursemodule
     * @param int $userid
     * @return array|null
     */
    public static function extract_activity_data(stdClass $coursemodule, int $userid): ?array {
        global $DB;

        $sactivitylink = new moodle_url('/mod/' . $coursemodule->modname . '/view.php', ['id' => $coursemodule->id]);
        $activityinstance = $DB->get_record($coursemodule->modname, ['id' => $coursemodule->instance], '*', MUST_EXIST);
        $instructions = '-';
        if ($coursemodule->modname == 'assign') {
            $instructions = $activityinstance->activity;
        }
        $availabilityinformation = '-';
        if ($userid) {
            // Ensure availability is an object.
            if ($coursemodule->availability) {
                if (is_string($coursemodule->availability)) {
                    $availability = json_decode($coursemodule->availability);
                }
                if (is_object($coursemodule->availability)) {
                    $cminfo = cm_info::create($coursemodule);
                    $info = new info_module($cminfo);
                    $modinfo = get_fast_modinfo($coursemodule->course);
                    $availabilityinformation = $info->get_full_information($modinfo);

                    // Replace placeholders with actual values.
                    if (is_object($availabilityinformation) && get_class($availabilityinformation) ===
                        'core_availability_multiple_messages') {
                        $processeditems = array_map(function($item) use ($modinfo, $DB) {
                            $item = preg_replace_callback('/<AVAILABILITY_CMNAME_(\d+)\/>/', function($matches) use ($modinfo) {
                                $cm = $modinfo->cms[$matches[1]];

                                return $cm->name;
                            }, $item);

                            $item = preg_replace_callback('/<AVAILABILITY_CALLBACK type="grade">(\d+)<\/AVAILABILITY_CALLBACK>/',
                                function($matches) use ($modinfo) {
                                    $gradeitem = grade_item::fetch(['id' => $matches[1]]);

                                    return $gradeitem->itemname;
                                }, $item);

                            $item = preg_replace_callback('/<AVAILABILITY_DATE>(\d+)<\/AVAILABILITY_DATE>/', function($matches) {
                                return userdate($matches[1]);
                            }, $item);

                            $item = preg_replace_callback('/<AVAILABILITY_USER_(\d+)\/>/', function($matches) use ($DB) {
                                $user = $DB->get_record('user', ['id' => $matches[1]], 'firstname, lastname');

                                return fullname($user);
                            }, $item);

                            $item = preg_replace_callback('/<AVAILABILITY_GROUP_(\d+)\/>/', function($matches) use ($DB) {
                                $group = $DB->get_record('groups', ['id' => $matches[1]], 'name');

                                return $group->name;
                            }, $item);

                            return "<p>$item</p>";
                        }, $availabilityinformation->items);

                        $operator = $availabilityinformation->andoperator ? ' AND ' : ' OR ';
                        $availabilityinformation = "Not available unless: " . implode($operator, $processeditems);
                    } else {
                        $availabilityinformation = preg_replace_callback('/<AVAILABILITY_CMNAME_(\d+)\/>/',
                            function($matches) use ($modinfo) {
                                $cm = $modinfo->cms[$matches[1]];

                                return $cm->name;
                            }, $availabilityinformation);

                        $availabilityinformation =
                            preg_replace_callback('/<AVAILABILITY_CALLBACK type="grade">(\d+)<\/AVAILABILITY_CALLBACK>/',
                                function($matches) use ($modinfo) {
                                    $gradeitem = grade_item::fetch(['id' => $matches[1]]);

                                    return $gradeitem->itemname;
                                }, $availabilityinformation);

                        $availabilityinformation = preg_replace_callback('/<AVAILABILITY_DATE>(\d+)<\/AVAILABILITY_DATE>/',
                            function($matches) {
                                return userdate($matches[1]);
                            }, $availabilityinformation);

                        $availabilityinformation = preg_replace_callback('/<AVAILABILITY_USER_(\d+)\/>/',
                            function($matches) use ($DB) {
                                $user = $DB->get_record('user', ['id' => $matches[1]], 'firstname, lastname');

                                return fullname($user);
                            }, $availabilityinformation);

                        $availabilityinformation = preg_replace_callback('/<AVAILABILITY_GROUP_(\d+)\/>/',
                            function($matches) use ($DB) {
                                $group = $DB->get_record('groups', ['id' => $matches[1]], 'name');

                                return $group->name;
                            }, $availabilityinformation);

                        if (strpos($availabilityinformation, 'Not available unless:') === false) {
                            $availabilityinformation = "Not available unless: <p>$availabilityinformation</p>";
                        } else {
                            $availabilityinformation = "<p>$availabilityinformation</p>";
                        }
                    }
                }
            }
        }

        return [
            'activity_name' => $activityinstance->name,
            'activity_id' => $coursemodule->instance,
            'activity_link' => $sactivitylink->out(false),
            'activity_type' => get_string('modulename', $coursemodule->modname),
            'activity_description' => format_text($activityinstance->intro, $activityinstance->introformat),
            'completion_enabled' => (bool) $coursemodule->completion,
            'visibility' => $coursemodule->visible,
            'instructions' => $instructions,
            'availability' => $availabilityinformation,
        ];
    }
}
