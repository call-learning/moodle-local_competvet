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
use mod_competvet\local\api\todos;
use mod_competvet\local\persistent\todo;

/**
 * Delete a todo.
 *
 * @package   local_competvet
 * @copyright 2023 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class delete_todo extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return null
     */
    public static function execute_returns() {
        return null;
    }

    /**
     * Set the status of a todo to deleted.
     *
     * @param int $id
     * @return array
     */
    public static function execute(int $id) {
        ['id' => $id] =
            self::validate_parameters(self::execute_parameters(), ['id' => $id]);
        self::validate_context(context_system::instance());
        todos::update_todo_status($id, todo::STATUS_DELETED);
        return null;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            [
                'id' => new external_value(PARAM_INT, 'id of the todo'),
            ]
        );
    }
}
