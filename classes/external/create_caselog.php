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
use mod_competvet\local\api\cases;
use mod_competvet\local\api\observations;
use mod_competvet\local\persistent\case_field;

/**
 * Get observation info for the eval component and a student id.
 *
 * @package   local_competvet
 * @copyright 2023 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class create_caselog extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure(
            [
                'caselogid' => new external_value(PARAM_INT, 'id of the caselog'),
            ]
        );
    }

    /**
     * Return the list of criteria for this situation.
     *
     * @param int $category
     * @param int $planningid
     * @param int $studentid
     * @param int|null $observerid
     * @param array|null $context
     * @param array|null $comments
     * @param array|null $criteria
     * @return array
     */
    public static function execute(
        int $planningid,
        int $studentid,
        array $fields = []
    ): array {
        [
            'planningid' => $planningid,
            'studentid' => $studentid,
            'fields' => $fields,
        ] =
            self::validate_parameters(self::execute_parameters(), [
                'planningid' => $planningid,
                'studentid' => $studentid,
                'fields' => $fields,
            ]);
        self::validate_context(context_system::instance());
        if (empty($studentid)) {
            global $USER;
            $observerid = $USER->id;
        }
        // Transform field in field id => value.

        $fieldassociative = [];
        foreach($fields as $field) {
            $casefield = case_field::get_record(['idnumber' => $field['idnumber']]);
            if (!$casefield) {
                continue;
            }
            $fieldassociative[$casefield->get('id')] = $field['value'];
        }
        $caselogid =
            cases::create_case(
                $planningid,
                $studentid,
                $fieldassociative
            );
        return ['caselogid' => $caselogid];
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            [
                'planningid' => new external_value(PARAM_INT, 'Planning id'),
                'studentid' => new external_value(PARAM_INT, 'id of the student'),
                'fields' => new external_multiple_structure(
                    new external_single_structure([
                        'idnumber' => new external_value(PARAM_TEXT, 'The field shortname'),
                        'value' => new external_value(PARAM_TEXT, 'The field value'),
                    ])
                ),
            ]
        );
    }
}
