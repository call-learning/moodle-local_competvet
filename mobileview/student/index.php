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
 * Display information about all plannings for a student
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
$userid = optional_param('userid', $USER->id, PARAM_INT);

$context = context_system::instance();
$currenturl = new moodle_url('/local/competvet/mobileview/student/index.php', ['userid' => $userid]);
$backurl = mobileview_helper::mobile_view_header($currenturl, new moodle_url('/local/competvet/mobileview/index.php'));
$debugs = [];
['results' => $planningstats, 'debug' => $debugs[]] =
    mobileview_helper::call_api(\local_competvet\external\get_plannings_infos::class, ['userid' => $userid]);

echo $OUTPUT->header();
$planningbycategory = array_reduce($planningstats, function ($carry, $item) {
    $carry[$item['categorytext']][] = $item;
    return $carry;
}, []);

echo $OUTPUT->heading(get_string('mobileview:situations', 'local_competvet'));
foreach ($planningbycategory as $categorytext => $plannings) {

    print_collapsible_region_start('card', 'category' . sha1($categorytext), $categorytext, '', true);
    foreach ($plannings as $planning) {
        $planningdef = $planning['info'];
        $situationid = $planningdef['situationid'];
        $competvet = competvet::get_from_situation_id($situationid);

        $dates = get_string('mobileview:planningdates', 'local_competvet', [
            'startdate' => planning::get_planning_date_string($planningdef['startdate']),
            'enddate' => planning::get_planning_date_string($planningdef['enddate']),
        ]);
        echo $OUTPUT->container_start('card my-2 p-1', 'planning');
        echo $OUTPUT->container(format_text($competvet->get_instance()->name, FORMAT_HTML), 'font-weight-bold');
        $planninglink = new moodle_url('/local/competvet/mobileview/student/myevaluations.php', [
            'userid' => $userid,
            'planningid' => $planning['id'],
        ]);
        echo $OUTPUT->container(html_writer::link($planninglink, $dates), 'font-weight-bold', 'planningdates');
        echo $OUTPUT->container_end();
    }
    print_collapsible_region_end();
}
echo $OUTPUT->render(new \local_competvet\output\local\mobileview\footer('situation'));
foreach ($debugs as $debug) {
    echo $OUTPUT->render($debug);
}
echo $OUTPUT->footer();
