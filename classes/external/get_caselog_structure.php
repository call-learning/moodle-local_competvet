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
use local_competvet\api_helpers;
use mod_competvet\local\api\cases;

/**
 * Get case item info for a user.
 *
 * @package   local_competvet
 * @copyright 2023 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_caselog_structure extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_multiple_structure
     */
    public static function execute_returns() {
        return
            new \external_multiple_structure(
                    api_helpers::get_caselog_category_info_structure()
            );
    }

    /**
     * Return the list of situations the user is registered in
     *
     * @return array
     */
    public static function execute(): array {
        self::validate_context(context_system::instance());
        $warnings = [];
        $casestructure = cases::get_case_structure();
        return array_values($casestructure);
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([]);
    }

}