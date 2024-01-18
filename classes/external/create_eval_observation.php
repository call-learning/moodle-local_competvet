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
class create_eval_observation extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure(
            [
                'observationid' => new external_value(PARAM_INT, 'id of the observation'),
            ]
        );
    }

    /**
     * Return the list of criteria for this situation.
     *
     * @param int $category
     * @param int $studentid
     * @param int $planningid
     * @param int|null $observerid
     * @return array
     */
    public static function execute(int $category, int $studentid, int $planningid, ?int $observerid = 0, array $criteria = []): array {
        [
            'category' => $category,
            'studentid' => $studentid,
            'planningid' => $planningid,
            'observerid' => $observerid,
            'criteria' => $criteria,
        ] =
            self::validate_parameters(self::execute_parameters(), [
                'category' => $category,
                'studentid' => $studentid,
                'planningid' => $planningid,
                'observerid' => $observerid,
                'criteria' => $criteria,
            ]);
        self::validate_context(context_system::instance());
        $observationid = observations::create_observation($category, $studentid, $planningid, $observerid, $criteria);
        return ['observationid' => $observationid];
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            [
                'category' => new external_value(PARAM_INT, 'Observation category (AUTOEVAL or EVAL'),
                'studentid' => new external_value(PARAM_INT, 'id of the student'),
                'observerid' => new external_value(PARAM_INT, 'id of the student'),
                'planningid' => new external_value(PARAM_INT, 'id of the student'),
                'context' =>
                    new external_single_structure(
                        api_helpers::get_context_structure(),
                        'Context of the observation',
                        VALUE_OPTIONAL
                    ),
                'criteria' => new \external_multiple_structure(
                    new external_single_structure(
                        [
                            'id' => new external_value(PARAM_INT, 'id of the criterion'),
                            'grade' => new external_value(PARAM_INT, 'grade of the criterion', VALUE_OPTIONAL),
                            'comment' => new external_value(PARAM_TEXT, 'comment of the criterion', VALUE_OPTIONAL),
                            'isactive' => new external_value(PARAM_BOOL, 'is the criterion active', VALUE_DEFAULT, 1),
                        ]
                    ),
                    'Criteria of the observation',
                    VALUE_OPTIONAL,
                ),
            ]
        );
    }
}
