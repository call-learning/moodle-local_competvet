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
namespace local_competvet\external;
defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/externallib.php');

use context_system;
use context_user;
use core_user;
use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use stdClass;
use user_picture;

/**
 * Get user profile
 *
 * @package   local_competvet
 * @copyright 2023 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_profile extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure(
            [
                'userid' => new external_value(PARAM_INT, 'ID type of user'),
                'fullname' => new external_value(PARAM_TEXT, 'User fullname'),
                'firstname' => new external_value(PARAM_TEXT, 'User firstname (can be ignored)'),
                'lastname' => new external_value(PARAM_TEXT, 'User lastname (can be ignored)'),
                'username' => new external_value(PARAM_RAW_TRIMMED, 'User internal username'),
                'userpictureurl' => new external_value(PARAM_URL, 'User picture (avatar) URL', VALUE_OPTIONAL),
            ]
        );
    }

    /**
     * Return the current information for the user
     *
     * @param int $userid
     * @return stdClass
     */
    public static function execute(int $userid): stdClass {
        global $USER, $PAGE;
        self::validate_parameters(self::execute_parameters(), ['userid' => $userid]);
        self::validate_context(context_system::instance());
        $user = null;
        if ($userid) {
            $user = core_user::get_user($userid);
        }
        if (!$user) {
            throw new \moodle_exception('invaliduserid', 'core_user', '', $userid);
        }
        $context = context_user::instance($user->id);
        $canseeadvanced = true;
        if ($user->id != $USER->id && !has_capability('moodle/user:viewdetails', $context)) {
            $canseeadvanced = false;
        }
        $userpicture = new user_picture($user);
        $userpicture->includetoken = true;
        $userpicture->size = 1; // Size f1.
        return (object) [
            'userid' => intval($user->id),
            'fullname' => fullname($user),
            'firstname' => $canseeadvanced ? $user->firstname : '',
            'lastname' => $canseeadvanced ? $user->lastname : '',
            'username' => $canseeadvanced ? $user->username : 'anonymous',
            'userpictureurl' => $userpicture->get_url($PAGE)->out(false), // TODO check if we should not return the default
            // picture in case the calling user is either not in the same context or not allowed to see the user.
        ];
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            [
                'userid' => new external_value(PARAM_INT, 'ID of the user', VALUE_DEFAULT, 0),
            ]
        );
    }
}

