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
use local_competvet\api_helpers;
use mod_competvet\utils;
use stdClass;
use user_picture;

/**
 * Get user information
 *
 * @package   local_competvet
 * @copyright 2023 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_info extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure(
           api_helpers::get_user_info_structure()
        );
    }

    /**
     * Return the current information for the user
     *
     * @param int $userid
     * @return stdClass
     */
    public static function execute(int $userid): stdClass {
        global $PAGE;
        self::validate_parameters(self::execute_parameters(), ['userid' => $userid]);
        self::validate_context(context_system::instance());
        $user = null;
        if ($userid) {
            $user = core_user::get_user($userid);
        }
        if (!$user) {
            throw new \moodle_exception('invaliduserid', 'core_user', '', $userid);
        }
        return (object) utils::get_user_info($userid);
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

