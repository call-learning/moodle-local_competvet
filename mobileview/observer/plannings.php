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
 * Display information about all plannings
 *
 * @package   mod_competvet
 * @copyright 2023 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_competvet\external\get_plannings_infos;
use local_competvet\external\get_situations;
use local_competvet\mobileview_helper;
use mod_competvet\competvet;
use mod_competvet\output\view\base;

require(__DIR__ . '/../../../../config.php');
global $PAGE, $DB, $OUTPUT, $USER;

require_login();
$situationid = required_param('situationid', PARAM_INT);
$asuserid = optional_param('asuserid', $USER->id, PARAM_INT);

$context = context_system::instance();
$PAGE->set_context($context);
$currenturl = new moodle_url('/local/competvet/mobileview/observer/planning.php', ['userid' => $asuserid]);
$PAGE->set_url($currenturl);
$debugs = [];
['results' => $situations, 'debug' => $debugs[]] =
    mobileview_helper::call_api(get_situations::class, ['userid' => $asuserid, 'nofutureplanning' => true]);

// Now find the situation we are interested in and extract the planning ids.
$planningids = [];
foreach ($situations as $situation) {
    if ($situation['id'] == $situationid) {
        $currentplannings = $situation['plannings'];
        break;
    }
}

$planningids = array_map(function($planning) {
    return $planning['id'];
}, $currentplannings);

['results' => $planningstats, 'debug' => $debugs[]] =
    mobileview_helper::call_api(get_plannings_infos::class, ['userid' => $asuserid, 'plannings' => json_encode($planningids)]);

$competvet = competvet::get_from_situation_id($situationid);
$PAGE->set_button(
    $OUTPUT->single_button(
        new moodle_url('/local/competvet/mobileview/observer/index.php', ['userid' => $asuserid]),
        get_string('back'),
        'get'
    )
);
echo $OUTPUT->header();
$widget = base::factory($asuserid, 'plannings');
$widget->set_data($currentplannings, $planningstats,
    new moodle_url('/local/competvet/mobileview/observer/planning.php', ['asuserid' => $asuserid]));

echo $OUTPUT->heading(format_text($competvet->get_instance()->name, FORMAT_HTML));
$renderer = $PAGE->get_renderer('mod_competvet');
echo $renderer->render($widget);
foreach ($debugs as $debug) {
    echo $OUTPUT->render($debug);
}
echo $OUTPUT->footer();
