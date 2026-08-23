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

use context_system;
use core_external\restricted_context_exception;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use mod_competvet\local\api\cases;
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
     * @param int $planningid
     * @param int $studentid
     * @param array $fields
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
        foreach ($fields as $field) {
            if (!empty($field['id'])) {
                // This is the new way with the new version 2.5.8 of the app to target the correct field version.
                $casefield = \mod_competvet\local\persistent\case_field::get_record(['id' => $field['id']]);
            } else {
                $casefield = \mod_competvet\local\persistent\case_field::get_by_idnumber($field['idnumber']);
            }
            if (!$casefield) {
                continue;
            }
            $fieldassociative[$casefield->get('id')] = $field['value'] ?? '';
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
                        // We will use id from now on so we can target the correct field version (from 2.5.8).
                        'id' => new external_value(PARAM_INT, 'The field id', VALUE_OPTIONAL),
                        'value' => new external_value(PARAM_TEXT, 'The field value', VALUE_OPTIONAL),
                    ])
                ),
            ]
        );
    }
}
