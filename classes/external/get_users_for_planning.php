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
use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use mod_competvet\local\api\plannings;

/**
 * Get users involved in the current planning.
 *
 * @package   local_competvet
 * @copyright 2023 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_users_for_planning extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return
            new external_single_structure(
                [
                    'students' => new external_multiple_structure(
                        new external_single_structure([
                            'id' => new external_value(PARAM_INT, 'Student ID'),
                            'fullname' => new external_value(PARAM_RAW, 'Student Name'),
                            'userpictureurl' => new external_value(PARAM_URL, 'user picture (avatar)',
                                VALUE_OPTIONAL),
                        ])
                    ),
                    'observers' => new external_multiple_structure(
                        new external_single_structure([
                            'id' => new external_value(PARAM_INT, 'Group ID'),
                            'rolename' => new external_value(PARAM_TEXT, 'Group Name'),
                            'fullname' => new external_value(PARAM_RAW, 'Observer Name'),
                            'userpictureurl' => new external_value(PARAM_URL, 'user picture (avatar)',
                                VALUE_OPTIONAL),
                        ])
                    ),
                ]
            );
    }

    /**
     * Return the list of situations the user is registered in
     *
     * @param int $planningid
     * @return array
     */
    public static function execute(int $planningid = null): array {
        ['planningid' => $planningid] =
            self::validate_parameters(self::execute_parameters(), ['planningid' => $planningid]);
        self::validate_context(context_system::instance());
        return plannings::get_users_for_planning_id($planningid);
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            [
                'planningid' => new external_value(PARAM_INT, 'id of the planning'),
            ]
        );
    }
}
