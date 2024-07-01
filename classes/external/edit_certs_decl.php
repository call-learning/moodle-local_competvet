<?php
// This file is part of Moodle - http://moodle.org/
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
use external_warnings;
use mod_competvet\local\api\certifications;

/**
 * Edit certif item.
 *
 * @package   local_competvet
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class edit_certs_decl extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure(
            ['warnings' => new external_warnings()]
        );
    }

    /**
     * Edit certif item
     *
     * @param int $id
     * @param int $level
     * @param string $comment
     * @param int $status
     * @param array|null $supervisors
     * @return array
     */
    public static function execute(int $id, int $level, string $comment, int $status, ?array $supervisors): array {
        [
            'criterionid' => $criterionid,
            'level' => $level,
            'comment' => $comment,
            'status' => $status,
            'supervisors' => $supervisors,
        ] = self::validate_parameters(
            self::execute_parameters(),
            ['id' => $id, 'level' => $level, 'comment' => $comment, 'status' => $status, 'supervisors' => $supervisors]
        );

        // Logic to edit the cert item using the certifications API.
        $result = certifications::update_cert_declaration($id, $level, $comment, FORMAT_PLAIN, $status);
        certifications::declaration_supervisors_update($id, array_map(fn($supervisor) => $supervisor['id'], $supervisors));
        $warnings = [];
        if (!$result) {
            $warnings[] = ['item' => $id, 'message' => 'Item not edited'];
        }
        return ['warnings' => $warnings];
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            [
                'id' => new external_value(PARAM_INT, 'Item ID'),
                'level' => new external_value(PARAM_INT, 'Level'),
                'comment' => new external_value(PARAM_TEXT, 'Comment'),
                'status' => new external_value(PARAM_TEXT, 'Status'),
                'supervisors' => new \external_multiple_structure(
                    new \external_single_structure(
                        [
                            'id' => new \external_value(PARAM_INT, 'supervisor id'),
                        ]
                    ),
                    'The supervisors',
                    VALUE_OPTIONAL
                ),
            ]
        );
    }
}
