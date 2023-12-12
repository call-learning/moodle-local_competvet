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
use mod_competvet\local\api\observations;

/**
 * Get users involved in the current planning.
 *
 * @package   local_competvet
 * @copyright 2023 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_user_evaluations extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_multiple_structure
     */
    public static function execute_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'eval' => new external_multiple_structure(
                        new external_single_structure([
                            'id' => new external_value(PARAM_INT, 'Student ID'),
                            'studentid' => new external_value(PARAM_INT, 'Student ID'),
                            'observerid' => new external_value(PARAM_INT, 'Observer ID'),
                            'status' => new external_value(PARAM_INT, 'Status ID'),
                            'time' => new external_value(PARAM_INT, 'Time of the evaluation'),
                            'category' => new external_value(PARAM_INT, 'Category of the evaluation (autoeval = 1, eval = 2)'),
                            'categorytext' => new external_value(PARAM_TEXT, 'Category textual information'),
                        ]), '', VALUE_OPTIONAL
                    ),
                    'certif' => new external_multiple_structure(
                        new external_single_structure([
                            'id' => new external_value(PARAM_INT, 'Student ID'),
                            'studentid' => new external_value(PARAM_INT, 'Student ID'),
                            'observerid' => new external_value(PARAM_INT, 'Observer ID'),
                            'status' => new external_value(PARAM_INT, 'Status ID'),
                            'time' => new external_value(PARAM_INT, 'Time of the evaluation'),
                            'category' => new external_value(PARAM_INT, 'Category of the evaluation (autoeval = 1, eval = 2)'),
                        ],), '', VALUE_OPTIONAL
                    ),
                    'list' => new external_multiple_structure(
                        new external_single_structure([
                            'id' => new external_value(PARAM_INT, 'Student ID'),
                            'studentid' => new external_value(PARAM_INT, 'Student ID'),
                            'observerid' => new external_value(PARAM_INT, 'Observer ID'),
                            'status' => new external_value(PARAM_INT, 'Status ID'),
                            'time' => new external_value(PARAM_INT, 'Time of the evaluation'),
                            'autoeval' => new external_value(PARAM_BOOL, 'Is it an auto-evaluation'),
                        ],), '',VALUE_OPTIONAL
                    ),
                ]
            )
        );
    }

    /**
     * Return the list of situations the user is registered in
     *
     * @param int $planningid
     * @param int $userid
     * @return array
     */
    public static function execute(int $planningid, int $userid): array {
        ['planningid' => $planningid, 'userid' => $userid] =
            self::validate_parameters(self::execute_parameters(), ['planningid' => $planningid, 'userid' => $userid]);
        self::validate_context(context_system::instance());
        return observations::get_user_evaluation($planningid, $userid);

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
                'userid' => new external_value(PARAM_INT, 'user id for the planning to check'),
            ]
        );
    }

}