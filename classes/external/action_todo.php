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
use mod_competvet\local\api\todos;

/**
 * Act on a todo.
 *
 * @package   local_competvet
 * @copyright 2023 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class action_todo extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure(
                [
                    'id' => new external_value(PARAM_INT, 'id of the todo'),
                    'status' => new external_value(PARAM_INT, 'status of the todo'),
                    'message' => new external_value(PARAM_TEXT, 'message of the todo', VALUE_OPTIONAL, ''),
                    'nextaction' => new external_value(PARAM_ALPHANUMEXT, 'next action of the todo'),
                    'data' => new external_value(PARAM_RAW, 'data of the todo', VALUE_OPTIONAL, ''),
                ]
            );
    }

    /**
     * Return the list of todos for this user (or current user if not specified).
     *
     * @param int $todoid
     * @return array
     */
    public static function execute(int $todoid): array {
        ['id' => $todoid] = self::validate_parameters(self::execute_parameters(), ['id' => $todoid]);
        self::validate_context(context_system::instance());
        return todos::act_on_todo($todoid);
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(['id' => new external_value(PARAM_INT, 'id of the todo')]);
    }
}
