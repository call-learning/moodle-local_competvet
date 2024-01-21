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
use local_competvet\api_helpers;
use mod_competvet\local\api\observations;

/**
 * Get observation info for the eval component and a student id.
 *
 * @package   local_competvet
 * @copyright 2023 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_eval_observation_info extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure(
            [
                'id' => new external_value(PARAM_INT, 'Observation ID'),
                'category' => new external_value(PARAM_INT, 'Observation category (AUTOEVAL or EVAL'),
                'context' =>
                    new external_single_structure(
                        api_helpers::get_context_structure()
                    ),
                'comments' => new external_multiple_structure(
                    new external_single_structure(
                        api_helpers::get_comment_structure()
                    )
                ),
                'criteria' => new external_multiple_structure(
                    new external_single_structure(
                        api_helpers::get_criteria_structure()
                    )
                ),
                'canedit' => new external_value(PARAM_BOOL, 'Can edit'),
                'candelete' => new external_value(PARAM_BOOL, 'Can delete'),
            ]
        );
    }

    /**
     * Return the list of criteria for this situation.
     *
     * @param int $observationid
     * @return array
     */
    public static function execute(int $observationid): array {
        ['observationid' => $observationid] =
            self::validate_parameters(self::execute_parameters(), ['observationid' => $observationid]);
        self::validate_context(context_system::instance());
        return observations::get_observation_information($observationid);
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            [
                'observationid' => new external_value(PARAM_INT, 'id of the observation'),
            ]
        );
    }
}
