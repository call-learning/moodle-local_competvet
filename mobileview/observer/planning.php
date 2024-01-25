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

use local_competvet\external\get_planning_info;
use local_competvet\external\get_users_infos_for_planning;
use local_competvet\mobileview_helper;
use local_competvet\output\local\mobileview\footer;
use mod_competvet\competvet;
use mod_competvet\local\persistent\planning;
use mod_competvet\output\view\base;

require(__DIR__ . '/../../../../config.php');
global $PAGE, $DB, $OUTPUT, $USER;

require_login();
$planningid = required_param('planningid', PARAM_INT);
$planning = planning::get_record(['id' => $planningid]);
$currenturl =
    new moodle_url('/local/competvet/mobileview/observer/planning.php', ['userid' => $USER->id, 'planningid' => $planningid]);

$backurl = mobileview_helper::mobile_view_header($currenturl,
    new moodle_url('/local/competvet/mobileview/observer/plannings.php', ['situationid' => $planning->get('situationid')]));

$debugs = [];
['results' => $planninginfo, 'debug' => $debugs[]] =
    mobileview_helper::call_api(get_planning_info::class, ['planningid' => $planningid]);
['results' => $userswithinfo, 'debug' => $debugs[]] =
    mobileview_helper::call_api(get_users_infos_for_planning::class, ['planningid' => $planningid]);

/** @var core_renderer $OUTPUT */
echo $OUTPUT->header();
$competvet = competvet::get_from_situation_id($planninginfo['situationid']);
$competvetinstance = $competvet->get_instance();
$competvetname = format_text($competvetinstance->name, FORMAT_HTML);
$dates = get_string('mobileview:planningdates', 'local_competvet', [
    'startdate' => planning::get_planning_date_string($planninginfo['startdate']),
    'enddate' => planning::get_planning_date_string($planninginfo['enddate']),
]);

echo $OUTPUT->heading(format_text($competvetname, FORMAT_HTML));
echo $OUTPUT->heading(format_text($dates, FORMAT_HTML), 3, 'text-right');
$widget = base::factory($USER->id, 'planning', 0, 'local_competvet', $backurl);
$widget->set_data($userswithinfo, $planninginfo['groupname'],
    new moodle_url('/local/competvet/mobileview/observer/evaluations.php', ['planningid' => $planningid]));
$renderer = $PAGE->get_renderer('mod_competvet');
echo $renderer->render($widget);

echo $OUTPUT->render(new footer('situation'));
foreach ($debugs as $debug) {
    echo $OUTPUT->render($debug);
}
echo $OUTPUT->footer();
