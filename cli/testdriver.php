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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * CLI script to test API through CURL.
 *
 * @package   mod_competvet
 * @copyright 2023 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);
require(__DIR__ . '/../../../config.php');
debugging() || defined('BEHAT_SITE_RUNNING') || die();

global $CFG;
require_once($CFG->libdir . '/clilib.php');

// Get the cli options.
[$options, $unrecognised] = cli_get_params([
    'help' => false,
    'command' => 'run',
    'scenario' => null,
], [
    'c' => 'command',
    's' => 'scenario',
    'h' => 'help',
]);

$usage = "Test driver command line interface

Usage:
    # php testdriver.php [--help|-h]

Options:
    -h --help                   Print this help.
    -c --command                Command to execute (init, reset, deinit)
";
if ($unrecognised) {
    $unrecognised = implode("\n\t", $unrecognised);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognised));
}

if ($options['help']) {
    cli_writeln($usage);
    die();
}
if (!in_array($options['command'], ['run'])) {
    if (empty($CFG->compet_test_driver_mode) || !$CFG->debugdeveloper) {
        throw new moodle_exception('error:compet_test_driver_mode', 'local_competvet');
    }
}
require_once($CFG->dirroot . '/local/competvet/testdriver/competvet_util.php');
$testdriver = new competvet_util();
switch ($options['command']) {
    case 'init':
        $testdriver->init_test();
        echo "Init test.";
        break;
    case 'deinit':
        $testdriver->deinit();
        echo "Deinit test.";
        break;
    case 'run':
        $testsscenariorunner = new \mod_competvet\tests\test_scenario();
        $content = $options['scenario'] ?? 'scenario_1';
        $content = file_get_contents($CFG->dirroot . '/local/competvet/tests/app_scenario/' . $content . '.feature');
        $parsedfeature = $testsscenariorunner->parse_feature($content);
        $result = $testsscenariorunner->execute($parsedfeature);
        if (!$result) {
            foreach ($parsedfeature->get_scenarios() as $scenario) {
                foreach ($scenario->steps as $step) {
                    cli_writeln("Step: " . $step->get_text() . $step->get_error());
                }
            }
        }
        echo "Executing scenario. $result";
        break;
    default:
        cli_writeln('Invalid command');

}
cli_writeln('Done !');
