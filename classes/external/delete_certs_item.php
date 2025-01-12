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
use core_user;
use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use external_warnings;
use mod_competvet\local\api\cases;
use mod_competvet\local\api\certifications;

/**
 * Delete certification declaration and related items.
 *
 * @package   local_competvet
 * @copyright 2023 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class delete_certs_item extends external_api {
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
     * Delete the certification declaration and related items.
     *
     * @param int $declid
     * @return array
     */
    public static function execute(int $declid): array {
        ['declid' => $declid] =
            self::validate_parameters(self::execute_parameters(), ['declid' => $declid]);
        self::validate_context(context_system::instance());
        if (!\mod_competvet\local\persistent\cert_decl::record_exists($declid)) {
            throw new \moodle_exception('invalidplanningid', 'local_competvet');
        }
        $cases = certifications::delete_cert_declaration($declid);
        $warnings = [];
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
                'declid' => new external_value(PARAM_INT, 'id of the declaration'),
            ]
        );
    }

}
