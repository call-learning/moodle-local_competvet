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
use external_description;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use local_competvet\api_helpers;
use local_competvet\local\ordered_params_external_api;
use mod_competvet\local\api\observations;

/**
 * Edit a given eval observation.
 *
 * @package   local_competvet
 * @copyright 2023 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class edit_eval_observation extends ordered_params_external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([]);
    }

    /**
     * Return the list of criteria for this situation.
     *
     * @param int $observationid
     * @param array|null $context
     * @param array|null $comments
     * @param array|null $criteria
     * @return array
     */
    public static function execute(
        int $observationid,
        ?array $context = null,
        ?array $comments = [],
        ?array $criteria = []
    ): array {
        [
            'observationid' => $observationid,
            'context' => $context,
            'comments' => $comments,
            'criteria' => $criteria,
        ] =
            self::validate_parameters(self::execute_parameters(), [
                'observationid' => $observationid,
                'context' => $context,
                'comments' => $comments,
                'criteria' => $criteria,
            ]);
        self::validate_context(context_system::instance());
        if (!empty($context)) {
            $context = (object) $context;
            $context = $context->comment;
        } else {
            $context = null;
        }
        observations::edit_observation($observationid, $context, $comments, $criteria);
        return [];
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            [
                'observationid' => new external_value(PARAM_INT, 'Observation ID'),
                'context' =>
                    new external_single_structure(
                        api_helpers::get_context_structure(),
                        'Context',
                        VALUE_OPTIONAL,
                    ),
                'comments' => new external_multiple_structure(
                    new external_single_structure(
                        api_helpers::get_comment_structure(),
                    ),
                    'Comments',
                    VALUE_OPTIONAL,
                ),
                'criteria' => new external_multiple_structure(
                    new external_single_structure(
                        api_helpers::get_criteria_structure()
                    ),
                    'Criteria',
                    VALUE_OPTIONAL,
                ),
            ]
        );
    }
}
