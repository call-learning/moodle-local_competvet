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

use mod_competvet\local\persistent\observation_comment;

define('CLI_SCRIPT', true);
require(__DIR__ . '/../../../config.php');
debugging() || defined('BEHAT_SITE_RUNNING') || die();

global $CFG;
require_once($CFG->libdir . '/clilib.php');

// Get the cli options.
[$options, $unrecognised] = cli_get_params([
    'help' => false,
], [
    'h' => 'help',
]);

$usage = "Setup eval grid

Usage:
    # php create_grid.php [--help|-h]

Options:
    -h --help                   Print this help.
";
if ($unrecognised) {
    $unrecognised = implode("\n\t", $unrecognised);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognised));
}

if ($options['help']) {
    cli_writeln($usage);
    die();
}
function get_curl_session($url, $postdata) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postdata));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // May be required if SSL certificate verification is not needed.
    return $ch;
}

function authenticate($baseurl, $username, $password) {
    $url = $baseurl . '/local/competvet/webservices/token.php';
    $postdata = [
        'username' => $username,
        'password' => $password,
        'service' => 'competvet_app_service',
    ];
    $ch = curl_init($url);
    $query = http_build_query($postdata, '', '&');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // May be required if SSL certificate verification is not needed
    curl_setopt($ch, CURLOPT_HEADER, false); // No need to include header in output
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']); // Set Content-Type

    $response = curl_exec($ch);
    if ($response === false) {
        echo 'Curl error: ' . curl_error($ch);
    }

    curl_close($ch);
    return json_decode($response, true);
}

function query_api_with_token($baseurl, $token, $functionname, $parameters) {
    $url = $baseurl . '/webservice/rest/server.php';
    $postdata = [
        'wstoken' => $token,
        'wsfunction' => $functionname,
        'moodlewsrestformat' => 'json',
    ];
    $postdata = array_merge($postdata, $parameters);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postdata, '', '&'));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // May be required if SSL certificate verification is not needed.
    curl_setopt($ch, CURLOPT_HEADER, false); // No need to include header in output
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']); // Set Content-Type
    $response = curl_exec($ch);
    if ($response === false) {
        echo 'Curl error: ' . curl_error($ch);
    }
    curl_close($ch);
    return json_decode($response, true);
}

$possiblesites = [
    'http://competveteval.local',
    'https://competveteval-moodle.call-learning.io',
];
$baseurl =
    cli_input(
        'Enter the base URL of the Moodle instance (' . join(", ", $possiblesites) . ') ?',
        $possiblesites[0],
        $possiblesites,
        255
    );
$possibleusers = ['student1', 'observer1'];
$username = cli_input('Enter the username (' . join(", ", $possibleusers) . ') ?', $possibleusers[0], $possibleusers, 255);
$password = cli_input('Enter the password', 'password', null, 255);

cli_writeln("Authenticating...toward $baseurl with $username and $password\n");
$authresponse = authenticate($baseurl, $username, $password);
cli_writeln(json_encode($authresponse, JSON_PRETTY_PRINT));

$token = $authresponse['token'];
$userid = $authresponse['userid'];

// Example - Get Application Mode.
cli_writeln("Example - Get Application Mode: local_competvet_get_application_mode");
$response = query_api_with_token($baseurl, $token, 'local_competvet_get_application_mode', ['userid' => $userid]);
cli_writeln(json_encode($response, JSON_PRETTY_PRINT));

// Example - Get User Profile.
cli_writeln("Example - Get User Profile: local_competvet_get_user_profile");
$response = query_api_with_token($baseurl, $token, 'local_competvet_get_user_profile', ['userid' => $userid]);
cli_writeln(json_encode($response, JSON_PRETTY_PRINT));

// Example - Get Situations for Current User.
cli_writeln("Example - Get Situations for Current User: local_competvet_get_situations");
$situations = query_api_with_token($baseurl, $token, 'local_competvet_get_situations', []);
cli_writeln(json_encode($situations, JSON_PRETTY_PRINT));
if (count($situations) > 0) {
    $firsttwoplannings = array_slice($situations[0]['plannings'], 0, 2);
    $firsttwoplanningsid = array_map(fn($planning) => $planning['id'], $firsttwoplannings);
    // Example - Get Planning Information
    // Assuming $plannings is an array of planning IDs.
    cli_writeln("Example - Get Planning Information: local_competvet_get_plannings_info");
    $response = query_api_with_token(
        $baseurl,
        $token,
        'local_competvet_get_plannings_info',
        ['plannings' => json_encode($firsttwoplanningsid)]
    );
    cli_writeln(json_encode($response, JSON_PRETTY_PRINT));

    // Example - Get User Information for Planning
    // Assuming $planningid is set.
    cli_writeln("Example - Get User Information for Planning: local_competvet_get_users_infos_for_planning");
    $response = query_api_with_token(
        $baseurl,
        $token,
        'local_competvet_get_users_infos_for_planning',
        ['planningid' => $firsttwoplanningsid[0]]
    );
    cli_writeln(json_encode($response, JSON_PRETTY_PRINT));

    // Get situation Grid
    // Get the first situation id.
    $firstsituationid = $situations[0]['id'];
    cli_writeln("Example - Get Situation Criteria: local_competvet_get_eval_situation_criteria");
    $allcriteria = query_api_with_token(
        $baseurl,
        $token,
        'local_competvet_get_eval_situation_criteria',
        ['situationid' => $firstsituationid]
    );
    cli_writeln(json_encode($response, JSON_PRETTY_PRINT));


    // Example - Create eval observation
    // Assuming $planningid is set.
    cli_writeln("Example - Create eval observation: local_competvet_create_eval_observation");
    $response = query_api_with_token($baseurl, $token, 'local_competvet_create_eval_observation', [
        'category' => \mod_competvet\local\persistent\observation::CATEGORY_EVAL_OBSERVATION,
        'planningid' => $firsttwoplanningsid[0],
        'studentid' => $userid,
        'observerid' => $userid,
        'context' => 'test',
        'comments' => [
            [
                'comment' => 'test comment',
                'type' => observation_comment::OBSERVATION_COMMENT,
            ],
        ],
    ]);
    cli_writeln(json_encode($response, JSON_PRETTY_PRINT));

    $observationid = $response['observationid'];
    // Now get the observation information.
    cli_writeln("Example - Get Observation Information: local_competvet_get_eval_observation_info");
    $response = query_api_with_token($baseurl, $token, 'local_competvet_get_eval_observation_info', [
        'observationid' => $observationid,
    ]);
    cli_writeln(json_encode($response, JSON_PRETTY_PRINT));

    // Edit the first criteria of the observation.
    $firstcriteria = $response['criteria'][0];
    $response['criteria'][0] = $firstcriteria;
    cli_writeln("Example - Edit Observation Information");
    $response = query_api_with_token($baseurl, $token, 'local_competvet_edit_eval_observation', [
        'observationid' => $observationid,
        'context' => [
            'id' => $response['context']['id'],
            'comment' => 'Test comment',
        ],
        'criteria' => [
                [
                    'id' => $firstcriteria['id'],
                    'level' => 100,
                    'subcriteria' => [
                        [
                            'id' => $firstcriteria['subcriteria'][0]['id'],
                            'comment' => 'My Comment',
                        ],
                    ],
                ],
            ],
    ]);

    cli_writeln("Example - Get Observation Information: local_competvet_get_eval_observation_info");
    $response = query_api_with_token($baseurl, $token, 'local_competvet_get_eval_observation_info', [
        'observationid' => $observationid,
    ]);
    cli_writeln(json_encode($response['criteria'][0], JSON_PRETTY_PRINT));
    cli_writeln(json_encode($response['context'], JSON_PRETTY_PRINT));

    // Now delete the observation.
    cli_writeln("Example - Delete Observation Information: local_competvet_delete_eval_observation");
    $response = query_api_with_token($baseurl, $token, 'local_competvet_delete_eval_observation', [
        'id' => $observationid,
    ]);
    cli_writeln(json_encode($response, JSON_PRETTY_PRINT));

    // Now ask for observations.
    cli_writeln("Example - Ask for Observation: local_competvet_ask_eval_observation");
    $response = query_api_with_token($baseurl, $token, 'local_competvet_ask_eval_observation', [
        'planningid' => $firsttwoplanningsid[0],
        'studentid' => $userid,
        'observerid' => $userid,
        'context' => 'test',
    ]);
    cli_writeln(json_encode($response, JSON_PRETTY_PRINT));
}
