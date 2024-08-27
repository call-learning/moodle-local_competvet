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
 * Depending on the current user role or the one provided through the URL, this script will
 * redirect to the appropriate page.
 *
 * @package   local_competvet
 * @copyright 2023 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_competvet\mobileview_helper;
use mod_competvet\local\persistent\situation;

require(__DIR__ . '/../../config.php');
global $PAGE, $DB, $OUTPUT, $USER;
require_login();
$userid = optional_param('userid', $USER->id, PARAM_INT);
if (!has_capability('moodle/site:config', context_system::instance())) {
    $userid = $USER->id;
}
$user = \core_user::get_user($userid);

$debugs = [];
['results' => $usertype, 'debug' => $debugs[]] =
    mobileview_helper::call_api(\local_competvet\external\get_application_mode::class, ['userid' => $user->id]);
$usertype = $usertype['type'];

// Output a single button to continue.
echo $OUTPUT->header();
echo $OUTPUT->container(fullname($user), 'card', 'usertype');
echo $OUTPUT->box_start();
echo "Application mode for $user->username is $usertype";
echo $OUTPUT->box_end();
echo $OUTPUT->box_start();
$situationsid = situation::get_all_situations_id_for($userid);
$list = [];
foreach($situationsid as $situationid) {
    $situation = situation::get_record(['id' => $situationid]);
    $competvet = \mod_competvet\competvet::get_from_situation_id($situationid);
    $situationandroles = "Situation ". $competvet->get_instance()->name . "({$situation->get('shortname')}) ";
    $roles = $situation->get_all_roles($userid);
    $situationandroles .= "Roles: " . implode(', ', $roles) . "<br>";
    $list[] = $situationandroles;
}
echo html_writer::alist($list);
echo $OUTPUT->box_end();
echo $OUTPUT->footer();
