<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Library of interface functions and constants.
 *
 * @package   local_competvet
 * @copyright 2023 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Override the webservice execution.
 *
 * @param stdClass $functionname
 * @param mixed ...$args
 * @return false
 * @throws moodle_exception
 */
function local_competvet_override_webservice_execution(stdClass $functionname, ...$args) {
    global $CFG;
    require_once($CFG->dirroot . '/local/competvet/testdriver/competvet_util.php');
    if (!competvet_util::is_api_broken()) {
        return false;
    }
    throw new moodle_exception('competvet:webserviceexecutionoverriden', 'local_competvet', '', $functionname);
}
