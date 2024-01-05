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
use mod_competvet\output\view\base;

require(__DIR__ . '/../../../../config.php');
global $PAGE, $DB, $OUTPUT, $USER;

require_login();
$planningid = required_param('planningid', PARAM_INT);
$asuserid = optional_param('asuserid', $USER->id, PARAM_INT);

$context = context_system::instance();
$PAGE->set_context($context);
$currenturl =
    new moodle_url('/local/competvet/mobileview/observer/planning.php', ['userid' => $asuserid, 'planningid' => $planningid]);
$PAGE->set_url($currenturl);

$debugs = [];
['results' => $planninginfo, 'debug' => $debugs[]] =
    mobileview_helper::call_api(\local_competvet\external\get_planning_info::class, ['planningid' => $planningid]);

$PAGE->set_button(
    $OUTPUT->single_button(
        new moodle_url(
            '/local/competvet/mobileview/observer/plannings.php',
            ['asuserid' => $asuserid, 'situationid' => $planninginfo['situationid']]
        ),
        get_string('back'),
        'get'
    )
);


['results' => $userswithinfo, 'debug' => $debugs[]] =
    mobileview_helper::call_api(\local_competvet\external\get_users_infos_for_planning::class, ['planningid' => $planningid]);

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
$widget = base::factory($asuserid, 'planning');
$widget->set_data($userswithinfo, $planninginfo['groupname'],
    new moodle_url('/local/competvet/mobileview/observer/evaluations.php', ['planningid' => $planningid]));
$renderer = $PAGE->get_renderer('mod_competvet');
echo $renderer->render($widget);
foreach ($debugs as $debug) {
    echo $OUTPUT->render($debug);
}
echo $OUTPUT->footer();
