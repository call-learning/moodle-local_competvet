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

use mod_competvet\tests\test_scenario;
/**
 * Main interface to Moodle PHP code check.
 *
 * @copyright   2023 CALL Learning <contact@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../../config.php');

global $CFG, $PAGE;

require_once($CFG->dirroot . '/local/competvet/testdriver/competvet_util.php');
$testdriver = new competvet_util();

$command = optional_param('command', '', PARAM_ALPHA);
ini_set('max_execution_time', 0);
ini_set('memory_limit', -1);
if (empty($CFG->compet_test_driver_mode)) {
    throw new moodle_exception('error:compet_test_driver_mode', 'local_competvet');
}
if (!$CFG->debugdeveloper) {
    throw new moodle_exception('error:compet_test_driver_debug', 'local_competvet');
}
if (!in_array($command, ['run', 'init', 'deinit', 'breakapi', 'fixapi'])) {
    throw new moodle_exception('error:invalid_command', 'local_competvet');
}
$PAGE->set_context(context_system::instance());

switch ($command) {
    case 'init':
        $testdriver->init_test();
        echo 'Starting test.';
        $testdriver->break_api(false);

        break;

    case 'deinit':
        $testdriver->deinit();
        $testdriver->break_api(false);
        echo 'Stopping test.';

        break;

    case 'run':
        global $CFG;
        $testsscenariorunner = new test_scenario();
        $content = optional_param('scenario', 'scenario_1', PARAM_ALPHANUMEXT);
        $content = file_get_contents($CFG->dirroot.'/local/competvet/tests/app_scenario/'.$content.'.feature');
        $parsedfeature = $testsscenariorunner->parse_feature($content);
        $result = $testsscenariorunner->execute($parsedfeature);
        if (!$result) {
            foreach ($parsedfeature->get_scenarios() as $scenario) {
                foreach ($scenario->steps as $step) {
                    echo html_writer::div('Step: '.$step->get_text().$step->get_error());
                }
            }
        }
        echo "Executing scenario. {$result}";

        break;

    case 'breakapi':
        $testdriver->break_api();
        echo 'Breaking API.';

        break;

    case 'fixapi':
        $testdriver->break_api(false);
        echo 'Fixing API.';

        break;

    default:
        echo 'Invalid command';

        break;
}
