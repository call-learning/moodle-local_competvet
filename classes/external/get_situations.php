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
use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use local_competvet\api_helpers;
use mod_competvet\local\api\situations;

/**
 * Get current user's situation list.
 *
 * @package   local_competvet
 * @copyright 2023 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_situations extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_multiple_structure
     */
    public static function execute_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'id' => new external_value(PARAM_INT, 'Situation ID (key used to retrieve further information)'),
                    'shortname' => new external_value(PARAM_TEXT, 'Situation short name or ID
                        (unique key for this situation when taken with session name)'),
                    'name' => new external_value(PARAM_TEXT, 'Situation descriptive name'),
                    'intro' => new external_value(PARAM_RAW, 'Situation full description'),
                    'evalnum' => new external_value(PARAM_INT, 'Required evaluation count'),
                    'autoevalnum' => new external_value(PARAM_INT, 'Required auto-evaluation count'),
                    'roles' => new external_value(PARAM_RAW, 'User roles in this situation
                            (student, observer, assessor) in a JSON array, with the highest role first'),
                    'tags' => new external_value(PARAM_RAW, 'User roles in this situation
                            (student, observer, assessor) in a JSON array, with the highest role first'),
                    'plannings' => new external_multiple_structure(
                        new external_single_structure(
                            api_helpers::get_planning_info_structure()
                        )
                    ),
                ]
            )
        );
    }

    /**
     * Return the list of situations the user is registered in
     *
     * @param int|null $userid
     * @param bool|null $nofutureplanning
     * @return array
     */
    public static function execute(?int $userid = null, ?bool $nofutureplanning = false): array {
        global $USER;
        ['userid' => $userid, 'nofutureplanning' => $nofutureplanning] =
            self::validate_parameters(self::execute_parameters(), ['userid' => $userid, 'nofutureplanning' => $nofutureplanning]);
        self::validate_context(context_system::instance());
        if (!$userid) {
            $userid = $USER->id;
        }
        self::validate_context(context_user::instance($userid));
        return situations::get_all_situations_with_planning_for($userid, $nofutureplanning);
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            [
                'userid' => new external_value(PARAM_INT, 'id of the user (optional parameter)', VALUE_DEFAULT, 0),
                'nofutureplanning' => new external_value(PARAM_BOOL,
                    'If true, do not show the future planning for this user/situations', VALUE_DEFAULT, false),
            ]
        );
    }
}
