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
use mod_competvet\local\api\plannings;
use mod_competvet\local\api\situations;

/**
 * Get information for the given planning
 *
 * @package   local_competvet
 * @copyright 2023 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_planning_info extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure(
            [
                'id' => new external_value(PARAM_INT, 'Plan ID'),
                'startdate' => new external_value(PARAM_INT, 'Plan start date'),
                'enddate' => new external_value(PARAM_INT, 'Plan end date'),
                'groupname' => new external_value(PARAM_TEXT, 'Group name'),
                'groupid' => new external_value(PARAM_INT, 'Group id (Internal ID)'),
                'session' => new external_value(PARAM_ALPHANUMEXT, 'Session name (unused now but might be later)'),
                'situationid' => new external_value(PARAM_INT, 'Situation ID'),
            ]
        );
    }

    /**
     * Return the list of situations the user is registered in
     *
     * @param int $planningid
     * @return array
     */
    public static function execute(int $planningid): array {
        ['planningid' => $planningid] =
            self::validate_parameters(self::execute_parameters(), ['planningid' => $planningid]);
        self::validate_context(context_system::instance());
        return plannings::get_planning_info($planningid);
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
