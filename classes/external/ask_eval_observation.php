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

use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use mod_competvet\competvet;
use mod_competvet\local\api\todos;
use mod_competvet\local\persistent\planning;
use mod_competvet\local\persistent\situation;

/**
 * Ask for a given eval observation.
 *
 * @package   local_competvet
 * @copyright 2023 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ask_eval_observation extends external_api {
    /**
     * Returns description of method return value
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return \mod_competvet\external\ask_eval_observation::execute_returns();
    }

    /**
     * Execute and return observation list
     *
     * @param int $observationid - Observation instance id
     * @return array|array[]
     * @throws \invalid_parameter_exception
     */
    public static function execute(string $context, int $planningid, int $observerid, int $studentid): array {
        return \mod_competvet\external\ask_eval_observation::execute($context, $planningid, $observerid, $studentid);
    }


    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return \mod_competvet\external\ask_eval_observation::execute_parameters();
    }
}
