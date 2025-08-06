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
 * Web service function to create a forum activity for teacher.
 *
 * @package local_copilot
 * @author Dorel Manolescu <dorel.manolescu@enovation.ie>
 * @author Lai Wei <lai.wei@enovation.ie>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
require_once($CFG->dirroot.'/mod/forum/lib.php');

/**
 * Web service class definition.
 */
class create_forum_for_teacher extends external_api {
    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'course_id' => new external_value(PARAM_INT, 'Moodle course id', VALUE_REQUIRED),
            'forum_name' => new external_value(PARAM_TEXT, 'Title/name of the forum activity', VALUE_REQUIRED),
            'section_id' => new external_value(PARAM_INT, 'Section/topic id', VALUE_REQUIRED),
            'forum_description' => new external_value(PARAM_RAW, 'Description of the activity', VALUE_DEFAULT, ''),
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
            'error' => new external_value(PARAM_TEXT, 'Error message if the activity creation failed'),
        ]);
    }

    /**
     * Create course activity.
     *
     * @param int $forumcourseid
     * @param string $forumname
     * @param int $sectionid
     * @param string|null $forumdescription
     * @return array|null
     * @uses die
     */
    public static function execute(int $forumcourseid, string $forumname, int $sectionid,
        ?string $forumdescription = null): ?array {
        global $DB;

        // Validate parameters.
        $params = self::validate_parameters(self::execute_parameters(), [
            'course_id' => $forumcourseid, 'forum_name' => $forumname, 'section_id' => $sectionid,
            'forum_description' => $forumdescription]);
        $courseid = $params['course_id'];
        $title = $params['forum_name'];
        $section = $params['section_id'];
        $description = $params['forum_description'];

        $course = $DB->get_record('course', ['id' => $courseid]);
        if (!$course) {
            header('HTTP/1.0 404 course not found');
            die();
        }

        // Perform security checks.
        $coursecontext = context_course::instance($courseid);
        self::validate_context($coursecontext);
        if (!has_capability('mod/forum:addinstance', $coursecontext)) {
            header('HTTP/1.0 403 user does not have forum add instance capability');
            die();
        }

        // From: mod/forum/tests/generator/lib.php.
        $moduledata = new stdClass();
        $moduledata->course = $courseid;
        $moduledata->name = trim($title);
        $moduledata->section = $section;
        $moduledata->visible = 1;
        $moduledata->type = 'general';
        $moduledata->assessed = 0;
        $moduledata->scale = 0;
        $moduledata->forcesubscribe = FORUM_CHOOSESUBSCRIBE;
        $moduledata->grade_forum = 0;
        $moduledata->modulename = 'forum';
        $moduledata->introeditor = ['text' => '', 'format' => ''];

        try {
            $modid = create_module($moduledata);
            if (!$modid) {
                header('HTTP/1.0 500 Internal Server Error');
                die('Failed to create activity');
            }
            if ($description) {
                $forum = $DB->get_record('forum', ['id' => $modid->instance], '*');
                $forum->intro = $description;
                $forum->introformat = 1;
                $forum->timemodified = time() + 1;
                $DB->update_record('forum', $forum);
            }

            $returnvalue = ['success' => true, 'id' => $modid->instance, 'error' => ''];
        } catch (Exception $e) {
            header('HTTP/1.0 500 Internal Server Error');
            die('Failed to create activity: ' . $e->getMessage());
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
