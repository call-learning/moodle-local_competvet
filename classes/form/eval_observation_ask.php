<?php
// This file is part of Moodle - http://moodle.org/
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

namespace local_competvet\form;

use local_competvet\mobileview_helper;
use mod_competvet\local\api\plannings;

/**
 * Observation create form
 *
 * @package    mod_competvet
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class eval_observation_ask extends \mod_competvet\form\eval_observation_ask {
    public function process_dynamic_submission() {
        global $PAGE;
        $renderer = $PAGE->get_renderer('local_competvet');
        $data = $this->get_data();
        $debugs = [];
        ['results' => $allusers, 'debug' => $debugs[]] =
            mobileview_helper::call_api(
                \local_competvet\external\get_users_infos_for_planning::class,
                ['planningid' => $data->planningid]
            );
        $observersinfo = $allusers['observers'];
        return [
            'context' => $data->context,
            'planningid' => $data->planningid,
            'studentid' => $data->studentid,
            'observers' => array_column($observersinfo, 'userinfo'),
            'debugs' => array_map(fn($debug) => $debug->export_for_template($renderer), $debugs),
        ];
    }
}
