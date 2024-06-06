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
 * Display information about a given planning
 *
 * @package   mod_competvet
 * @copyright 2023 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_competvet\mobileview_helper;
use mod_competvet\competvet;
use mod_competvet\local\persistent\observation;
use mod_competvet\local\persistent\planning;
use mod_competvet\output\view\base;

require(__DIR__ . '/../../../../../config.php');
global $PAGE, $DB, $OUTPUT, $USER;

require_login();
$declid = required_param('id', PARAM_INT);

$currenturl = new moodle_url(
    '/local/competvet/mobileview/common/certif/view.php',
    ['obsid' => $declid]
);

if ($returnurl = optional_param('returnurl', null, PARAM_URL)) {
    $currenturl->param('returnurl', $returnurl);
}
// Store the origin URL in the session so that we can return to it after the user has finished.

$backurl = mobileview_helper::mobile_view_header($currenturl, $returnurl ? new moodle_url($returnurl) : null);

$debugs = [];
['results' => $certification, 'debug' => $debugs[]] =
    mobileview_helper::call_api(
        \local_competvet\external\get_user_certs_item_info::class,
        ['id' => $declid]
    );
$certdecl = \mod_competvet\local\persistent\cert_decl::get_record(['id' => $declid]);

$planning = planning::get_record(['id' => $certdecl->get('planningid')]);
$groupname = groups_get_group_name($planning->get('groupid'));
$userid = $certdecl->get('studentid');

echo $OUTPUT->header();
$competvet = competvet::get_from_situation_id($planning->get('situationid'));
$competvetinstance = $competvet->get_instance();
$competvetname = format_text($competvetinstance->name, FORMAT_HTML);
$dates = get_string('mobileview:planningdates', 'local_competvet', [
    'startdate' => planning::get_planning_date_string($planning->get('startdate')),
    'enddate' => planning::get_planning_date_string($planning->get('enddate')),
]);

$studentuser = core_user::get_user($userid);
echo $OUTPUT->heading(format_text($competvetname, FORMAT_HTML));
echo $OUTPUT->user_picture($studentuser, ['size' => 100, 'class' => 'd-inline-block']);
echo $OUTPUT->heading(format_text($dates, FORMAT_HTML), 3, 'text-right');
$widget = base::factory($userid, 'student_cert_view', 0, 'local_competvet', $backurl);
$widget->set_data($certification);
$renderer = $PAGE->get_renderer('mod_competvet');
echo $renderer->render($widget);

echo $OUTPUT->render(new \local_competvet\output\local\mobileview\footer('situation'));
foreach ($debugs as $debug) {
    echo $OUTPUT->render($debug);
}
echo $OUTPUT->footer();
