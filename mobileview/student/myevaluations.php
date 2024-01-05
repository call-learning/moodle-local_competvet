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
use mod_competvet\local\persistent\planning;

require(__DIR__ . '/../../../../config.php');
global $PAGE, $DB, $OUTPUT, $USER;

require_login();
$planningid = required_param('planningid', PARAM_INT);
$userid = required_param('userid', PARAM_INT);
$currenttab = optional_param('currenttab', 'eval', PARAM_ALPHA);

$context = context_system::instance();
$PAGE->set_context($context);
$currenturl = new moodle_url('/local/competvet/mobileview/student/userevaluations.php',
    ['userid' => $userid, 'planningid' => $planningid]);
$PAGE->set_url($currenturl);

$planning = planning::get_record(['id' => $planningid]);
$groupname = groups_get_group_name($planning->get('groupid'));
$PAGE->set_button(
    $OUTPUT->single_button(
        new moodle_url(
            '/local/competvet/mobileview/student/index.php',
            ['userid' => $userid, 'situationid' => $planning->get('situationid')]
        ),
        get_string('back'),
        'get'
    )
);
$debugs = [];
['results' => $evaluations, 'debug' => $debugs[]] =
    mobileview_helper::call_api(\local_competvet\external\get_user_eval_observations::class,
        ['planningid' => $planningid, 'userid' => $userid]);

['results' => $studentinfo, 'debug' => $debugs[]] =
    mobileview_helper::call_api(\local_competvet\external\get_planning_infos_student::class,
        ['planningid' => $planningid, 'userid' => $userid]);

// Add 3 pages tabs 'eval', 'certif' and 'list'.
$userplanninginfo = $studentinfo['info'];
if (!empty($userplanninginfo)) {
    $userplanninginfo = array_combine(array_column($userplanninginfo, 'type'), $userplanninginfo);
}
$tabs = [];
$tabs[] = new tabobject(
    'eval',
    new moodle_url($currenturl, ['currenttab' => 'eval']),
    get_string('mobileview:eval', 'local_competvet') . ' (' . ($userplanninginfo['eval']['nbdone'] ?? 0) . '/' .
    ($userplanninginfo['eval']['nbrequired'] ?? 0) . ')'
);
$tabs[] = new tabobject(
    'certif',
    new moodle_url($currenturl, ['currenttab' => 'certif']),
    get_string('mobileview:certif', 'local_competvet') . ' (' . ($userplanninginfo['certif']['nbdone'] ?? 0) . '/' .
    ($userplanninginfo['certif']['nbrequired'] ?? 0) . ')'
);
$tabs[] = new tabobject(
    'list',
    new moodle_url($currenturl, ['currenttab' => 'list']),
    get_string('mobileview:list', 'local_competvet') . ' (' . ($userplanninginfo['list']['nbdone'] ?? 0) . '/' .
    ($userplanninginfo['list']['nbrequired'] ?? 0) . ')'
);

// Build the tabs.

/** @var core_renderer $OUTPUT */
echo $OUTPUT->header();
$competvet = competvet::get_from_situation_id($planning->get('situationid'));
$competvetinstance = $competvet->get_instance();
$competvetname = format_text($competvetinstance->name, FORMAT_HTML);
$dates = get_string('mobileview:planningdates', 'local_competvet', [
    'startdate' => planning::get_planning_date_string($planning->get('startdate')),
    'enddate' => planning::get_planning_date_string($planning->get('enddate')),
]);

$user = core_user::get_user($userid);
echo $OUTPUT->heading(format_text($competvetname, FORMAT_HTML));
echo $OUTPUT->user_picture($user, ['size' => 100, 'class' => 'd-inline-block']);
echo $OUTPUT->heading(format_text($dates, FORMAT_HTML), 3, 'text-right');
echo $OUTPUT->tabtree($tabs, $currenttab);
foreach ($evaluations as $evaluationtype => $evaluationlist) {
    if ($evaluationtype != $currenttab) {
        continue;
    }
    $evaluationsbycategory = array_reduce($evaluationlist, function($carry, $item) {
        $carry[$item['categorytext']][] = $item;
        return $carry;
    }, []);
    foreach ($evaluationsbycategory as $evaltype => $evaluations) {
        print_collapsible_region_start(
            'card',
            sha1($evaltype),
            $evaltype,
            '',
            true
        );
        foreach ($evaluations as $evaluation) {
            $observer = core_user::get_user($evaluation['observerid']);
            $observerurl = new moodle_url('/user/view.php', ['id' => $evaluation['observerid']]);
            $observerinfo = $OUTPUT->user_picture($observer) . fullname($observer);
            $observerinfo .= html_writer::div(userdate($evaluation['time'], get_string('strftimedate', 'core_langconfig')));
            echo $OUTPUT->container($observerinfo, 'user-info card m-2');
        }
        print_collapsible_region_end();
    }
}
foreach ($debugs as $debug) {
    echo $OUTPUT->render($debug);
}
echo $OUTPUT->footer();
