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
use mod_competvet\local\api\plannings;

/**
 * Get users involved in the current planning and information about the status
 * of their evaluation.
 *
 * @package   local_competvet
 * @copyright 2023 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_users_infos_for_planning extends external_api {
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
                            'userinfo' => new external_single_structure(
                                api_helpers::get_user_info_structure()
                            ),
                            'planninginfo' => new external_multiple_structure(
                                new external_single_structure(
                                    [
                                        'type' => new external_value(
                                            PARAM_TEXT,
                                            'Type of evaluation (eval, autoeval, certif, list)'
                                        ),
                                        'nbdone' => new external_value(PARAM_INT, 'Nb of observation done'),
                                        'nbrequired' => new external_value(PARAM_INT, 'Nb of observation required'),
                                    ]
                                )
                            ),
                        ])
                    ),
                    'observers' => new external_multiple_structure(
                        new external_single_structure([
                            'userinfo' => new external_single_structure(
                                api_helpers::get_user_info_structure()
                            ),
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
    public static function execute(int $planningid): array {
        ['planningid' => $planningid] =
            self::validate_parameters(self::execute_parameters(), ['planningid' => $planningid]);
        self::validate_context(context_system::instance());
        return plannings::get_users_infos_for_planning_id($planningid);
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
