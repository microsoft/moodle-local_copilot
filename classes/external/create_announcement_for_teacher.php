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
 * Web service function to create an announcement in the news forum for teacher.
 *
 * @package local_copilot
 * @author Dorel Manolescu <dorel.manolescu@enovation.ie>
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license https://opensource.org/license/MIT MIT License
 * @copyright (C) 2014 onwards Microsoft, Inc. (http://microsoft.com/)
 */

namespace local_copilot\external;

use cache_helper;
use context_course;
use Exception;
use external_api;
use external_function_parameters;
use external_single_structure;
use core_external\external_value;
use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/mod/forum/lib.php');

/**
 * Web service class definition.
 */
class create_announcement_for_teacher extends external_api {
    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'course_id' => new external_value(PARAM_INT, 'Moodle course id', VALUE_REQUIRED),
            'announcement_subject' => new external_value(PARAM_TEXT, 'announcement subject', VALUE_REQUIRED),
            'announcement_message' => new external_value(PARAM_RAW, 'announcement message', VALUE_REQUIRED),
            'announcement_pinned' => new external_value(PARAM_BOOL, 'Pinned', VALUE_DEFAULT, false),
            'announcement_timestart' => new external_value(PARAM_TEXT, 'Display start in MM/DD/YYYY format', VALUE_DEFAULT, ''),
            'announcement_timeend' => new external_value(PARAM_TEXT, 'Display end in MM/DD/YYYY format', VALUE_DEFAULT, ''),
        ]);
    }

    /**
     * Method return definitions.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'True if the operation was successful'),
            'id' => new external_value(PARAM_INT, 'ID of the activity created'),
            'error' => new external_value(PARAM_TEXT, 'Error message if the announcement creation failed'),
        ]);
    }

    /**
     * Create course activity.
     *
     * @param int $announcementcourseid
     * @param string $announcementsubject
     * @param string $announcementmessage
     * @param int|null $announcementpinned
     * @param string|null $announcementtimestart
     * @param string|null $announcementtimeend
     * @return array|null
     * @throws \coding_exception
     */
    public static function execute(int $announcementcourseid, string $announcementsubject, string $announcementmessage,
                                   ?int $announcementpinned = null, ?string $announcementtimestart = null,
                                   ?string $announcementtimeend = null): ?array {
        global $DB;

        // Convert date strings to Unix timestamps.
        $announcementtimestart = $announcementtimestart ? strtotime($announcementtimestart) : 0;
        $announcementtimeend = $announcementtimeend ? strtotime($announcementtimeend) : 0;

        if ($announcementtimestart === false || $announcementtimeend === false) {
            return ['success' => false, 'id' => 0, 'error' => 'Invalid date format. Use MM/DD/YYYY.'];
        }

        $params = self::validate_parameters(self::execute_parameters(), [
            'course_id' => $announcementcourseid, 'announcement_subject' => $announcementsubject,
            'announcement_message' => $announcementmessage, 'announcement_pinned' => $announcementpinned,
            'announcement_timestart' => $announcementtimestart, 'announcement_timeend' => $announcementtimeend]);

        $courseid = $params['course_id'];
        $subject = $params['announcement_subject'];
        $message = $params['announcement_message'];
        $pinned = $params['announcement_pinned'];
        $pinned = $pinned ? 1 : 0;
        $displaystart = $params['announcement_timestart'];
        $displayend = $params['announcement_timeend'];

        $course = $DB->get_record('course', ['id' => $courseid]);
        if (!$course) {
            header('HTTP/1.0 404 course not found');
            die;
        }

        $coursecontext = context_course::instance($courseid);
        // Check if user has course update capability.
        if (!has_capability('moodle/course:update', $coursecontext)) {
            header('HTTP/1.0 403 user does not have course update capability');
            die;
        }

        if (!has_capability('mod/forum:addinstance', $coursecontext)) {
            header('HTTP/1.0 403 user does not have forum add instance capability');
            die;
        }

        // Check if there is an announcement forum in the course.
        $announcementforum = $DB->get_record('forum', ['course' => $courseid, 'type' => 'news']);
        if (!$announcementforum) {
            header('HTTP/1.0 404 announcement forum not found');
            die;
        }

        // Create the discussion in the announcement forum.
        $discussion = new stdClass();
        $discussion->course = $courseid;
        $discussion->forum = $announcementforum->id;
        $discussion->name = $subject;
        $discussion->message = $message;
        $discussion->messageformat = FORMAT_HTML;
        $discussion->pinned = $pinned;
        $discussion->timestart = $displaystart;
        $discussion->timeend = $displayend;
        $discussion->groupid = 0;
        $discussion->messagetrust = 0;
        $discussion->mailnow = 0;
        try {
            $discussionid = forum_add_discussion($discussion);
            $returnvalue = ['success' => true, 'id' => $discussionid, 'error' => ''];
        } catch (Exception $e) {
            header('HTTP/1.0 500 Internal Server Error');
            die('Failed to create discussion in announcement forum: ' . $e->getMessage());
            /*
             * There's a discussion on whether the API returns 500 or 'success' => false with more details.
             * For the moment, let's return 500. If we decide to do the opposite, the following line can be uncommented.
             * Note there will be more changes required in the agents to process the response.
             * $returnvalue = ['success' => false, 'id' => 0, 'error' => $e->getMessage()];
             */
        }

        rebuild_course_cache($courseid);
        cache_helper::purge_by_event('changesincourse');

        return $returnvalue;
    }
}
