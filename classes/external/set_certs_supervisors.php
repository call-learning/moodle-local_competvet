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
use external_multiple_structure;
use external_single_structure;
use external_value;
use external_warnings;
use mod_competvet\local\api\certifications;

/**
 * Set certif supervisors.
 *
 * @package   local_competvet
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class set_certs_supervisors extends external_api {
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
     * Set certif supervisors
     *
     * @param int $declid
     * @param array $supervisors
     * @return array
     */
    public static function execute(int $declid, array $supervisors): array {
        global $USER;
        ['declid' => $declid, 'supervisors' => $supervisors] =
            self::validate_parameters(self::execute_parameters(), ['declid' => $declid, 'supervisors' => $supervisors]);

        $warnings = [];

        // Logic to set the cert supervisors using the certifications API.
        $currentsupervisorids = certifications::set_declaration_supervisors($declid, $supervisors, $USER->id);
        if ($notset = array_diff($supervisors, $currentsupervisorids)) {
            $warnings[] = [
                'item' => $declid,
                'warningcode' => 'supervisors_not_set',
                'message' => 'Some supervisors could not be set.' . json_encode($notset), ];
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
                'declid' => new external_value(PARAM_INT, 'Declaration ID'),
                'supervisors' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'Supervisor ID')
                ),
            ]
        );
    }
}
