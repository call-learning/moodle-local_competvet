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
use mod_competvet\local\api\certifications;

/**
 * Create certif validation item.
 *
 * @package   local_competvet
 * @copyright  2024 CALL Learning <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class create_certs_valid extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure(
            [
                'id' => new external_value(PARAM_INT, 'Newly created certification validation ID'),
            ]
        );
    }

    /**
     * Create certif item
     *
     * @param int $criterionid
     * @param int $studentid
     * @param int $planningid
     * @param int $level
     * @param string $comment
     * @param int $status
     * @return array
     */
    public static function execute(int $declid, int $supervisorid, string $comment, int $status): array {
        [
            'declid' => $declid,
            'supervisorid' => $supervisorid,
            'comment' => $comment,
            'status' => $status
        ] = self::validate_parameters(
            self::execute_parameters(),
            [
                'declid' => $declid,
                'supervisorid' => $supervisorid,
                'comment' => $comment,
                'status' => $status,
            ]
        );

        // Logic to create the cert item using the certifications API.
        $validationid =
            certifications::validate_cert_declaration($declid, $supervisorid, $status, $comment, intval(FORMAT_PLAIN));

        return ['id' => $validationid];
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            [
                'declid' => new external_value(PARAM_INT, 'Criterion ID'),
                'supervisorid' => new external_value(PARAM_INT, 'Observer/Supervisor ID'),
                'comment' => new external_value(PARAM_TEXT, 'Comment'),
                'status' => new external_value(PARAM_INT, 'Status'),
            ]
        );
    }
}
