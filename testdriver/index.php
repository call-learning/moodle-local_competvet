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

/**
 * Main interface to Moodle PHP code check
 *
 * @package     local_competvet
 * * @copyright   2023 CALL Learning <contact@call-learning.fr>
 * * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../../config.php');

global $CFG;

if (empty($CFG->compet_test_driver_mode) || !$CFG->debugdeveloper) {
    throw new moodle_exception('error:compet_test_driver_mode', 'local_competvet');
}
require_once($CFG->dirroot . '/local/competvet/testdriver/competvet_util.php');
$testdriver = new competvet_util();

$command = optional_param('command', '', PARAM_ALPHA);
ini_set('max_execution_time', 0);
ini_set('memory_limit', -1);
switch ($command) {
    case 'init':
        $testdriver->init_test();
        echo "Starting test.";
        break;
    case 'deinit':
        $testdriver->deinit();
        echo "Stopping test.";
        break;
    case 'run':
        global $CFG;
        $testdriver->init();
        $content = optional_param('scenario', 'scenario_1', PARAM_ALPHANUMEXT);
        $content = file_get_contents($CFG->dirroot . '/local/competvet/tests/app_scenario/' . $content . '.feature');
        $parsedfeature = $testdriver->parse_feature($content);
        $result = $testdriver->execute($parsedfeature);
        echo "Executing scenario. $result";
        break;
    default:
        echo 'Invalid command';
        break;
}