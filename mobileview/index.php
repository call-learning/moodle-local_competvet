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

require(__DIR__ . '/../../../config.php');
global $PAGE, $DB, $OUTPUT, $USER;
require_login();
$userid = optional_param('userid', $USER->id, PARAM_INT);

$context = context_system::instance();
if (!($user = \core_user::get_user($userid))) {
    $user = $USER;
}
$debugs = [];
['results' => $usertype, 'debug' => $debugs[]] =
    mobileview_helper::call_api(\local_competvet\external\get_application_mode::class, ['userid' => $user->id]);
$usertype = $usertype->type;
if ($usertype == 'unknown' && !is_primary_admin($user->id)) {
    throw new moodle_exception('unknownusertype', 'mod_competvet');
}
$params = [];
if ($userid != $USER->id) {
    $params['userid'] = $userid;
}
if ($usertype === 'student') {
    $redirecturl = new moodle_url('/local/competvet/mobileview/student/index.php', $params);
} else {
    $redirecturl = new moodle_url('/local/competvet/mobileview/observer/index.php', $params);
}
$currenturl = new moodle_url('/local/competvet/mobileview/index.php', ['userid' => $userid]);
$PAGE->set_url($currenturl);

// Output a single button to continue
echo $OUTPUT->header();
echo $OUTPUT->continue_button($redirecturl);
foreach ($debugs as $debug) {
    echo $OUTPUT->render($debug);
}
echo $OUTPUT->footer();

