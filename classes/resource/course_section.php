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
 * Resource type "section" for web service return data in local_copilot.
 *
 * @package local_copilot
 * @author Dorel Manolescu <dorel.manolescu@enovation.ie>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2014 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot\resource;

defined('MOODLE_INTERNAL') || die();

use core_availability\info_section;
use external_value;
use moodle_url;
use stdClass;
use core_availability\api;
use core_availability\instance;
use core_availability\restriction;
use core_availability\condition_set;
use grade_item;

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->dirroot . '/course/format/lib.php');

/**
 * Section resource type.
 */
class course_section implements resource_type {
    /**
     * Get the resource return structure.
     *
     * @return array
     */
    public static function get_return_structure(): array {
        return [
            'section_name' => new external_value(PARAM_TEXT, 'Name of the section.'),
            'section_id' => new external_value(PARAM_INT, 'ID of the section.'),
            'section_link' => new external_value(PARAM_URL, ' Link to the section.'),
            'section_summary' => new external_value(PARAM_TEXT, 'Summary of the section, in HTML format.'),
            'section_sequence' => new external_value(PARAM_INT, 'Sequence of the section in the course, starting from 0.'),
            'visibility' => new external_value(PARAM_BOOL, 'Whether the section is visible to students.'),
            'availability' => new external_value(PARAM_RAW, 'Section availability information.'),
        ];
    }

    /**
     * Extract section data.
     *
     * @param stdClass $section
     * @param int|null $userid
     * @return array
     */
    public static function extract_section_data(stdClass $section, $userid = null): array {
        global $DB;

        // Get the section link.
        $sectionlink = new moodle_url('/course/section.php', ['id' => $section->id]);

        // Get the course format instance.
        $courseformat = course_get_format($section->course);

        $availabilityinformation = '-';
        if ($userid) {
            if ($section->availability) {
                if (is_string($section->availability)) {
                    $availability = json_decode($section->availability);
                }

                if (is_object($availability) && !empty((array) $availability) && isset($availability->op)
                    && isset($availability->c) && !empty($availability->c)) {
                    $modinfo = get_fast_modinfo($section->course);
                    $info = new info_section($modinfo->get_section_info($section->section));
                    $availabilityinformation = $info->get_full_information($modinfo);

                    // Replace placeholders with actual values.
                    if (is_object($availabilityinformation) &&
                        get_class($availabilityinformation) === 'core_availability_multiple_messages') {
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
            'section_name' => $courseformat->get_section_name($section),
            'section_id' => $section->id,
            'section_link' => $sectionlink->out(false),
            'section_summary' => strip_tags($section->summary),
            'section_sequence' => $section->section,
            'visibility' => $section->visible,
            'availability' => $availabilityinformation,
        ];
    }
}
