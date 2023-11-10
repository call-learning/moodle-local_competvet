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
use core_user;
use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use mod_competvet\local\api\user_role;

/**
 * Get application mode (student or observer)
 *
 * This returns a simple type or potentially an exception when there is a mismatched type
 * across all situations (an observer cannot be a student for example).
 *
 * @package   local_competvet
 * @copyright 2023 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_application_mode extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure(
            [
                'type' => new external_value(
                    PARAM_ALPHA,
                    'The way the mobile application mode (taken the highest role in this situation)
                    (student, observer, unknown). Unknown is returned either when no role is assigned OR
                    the user has student role and also observer role (which is not allowed currently). In
                    this case it is up to the Mobile application to decide what to do (display error message or run as
                    a student)'
                ),
            ]
        );
    }

    /**
     * Return the current role for the user across all situations
     *
     * @param int $userid
     * @return \stdClass
     */
    public static function execute(int $userid): \stdClass {
        self::validate_parameters(self::execute_parameters(), ['userid' => $userid]);
        self::validate_context(context_system::instance());
        $user = null;
        if ($userid) {
            $user = core_user::get_user($userid);
        }
        if (!$user) {
            throw new \moodle_exception('invaliduserid', 'core_user', '', $userid);
        }

        $mode = '';
        try {
            $role = user_role::get_top_for_all_situations($userid);
            switch($role) {
                case 'student':
                    $mode = 'student';
                    break;
                case 'unknown':
                    $mode = 'unknown';
                    break;
                default:
                    $mode = 'observer';
                    break;
            }
        } catch (\moodle_exception $e) {
            // We do not throw an exception here because we want to return the mode anyway.
            // This is because the user can be a student and an observer at the same time.
            $mode = 'unknown';
        }
        return (object) ['type' => $mode];
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            [
                'userid' => new external_value(PARAM_INT, 'id of the user', VALUE_REQUIRED),
            ]
        );
    }
}
