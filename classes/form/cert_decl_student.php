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

/**
 * Observation create form
 *
 * @package    mod_competvet
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cert_decl_student extends \mod_competvet\form\cert_decl_student {
    /**
     * Process the form submission, used if form was submitted via AJAX
     *
     * @return array
     */
    public function process_dynamic_submission() {
        global $USER, $PAGE;
        $alldebugs = [];
        try {
            $data = $this->get_data();
            if ($USER->id != $data->studentid) {
                return ['result' => false, 'error' => 'You are not allowed to do this'];
            }
            $renderer = $PAGE->get_renderer('local_competvet');
            $data = $this->get_data();

            if ($data->declid) {
                ['results' => $results, 'debug' => $debugs[]] =
                    mobileview_helper::call_api(
                        \local_competvet\external\edit_certs_item::class,
                        ['planningid' => $data->declid, 'level' => $data->level, 'comment' => $data->comment,
                            'status' => $data->status, ]
                    );

            } else {
                ['results' => $results, 'debug' => $debugs[]] =
                    mobileview_helper::call_api(
                        \local_competvet\external\create_certs_decl::class,
                        ['planningid' => $data->planningid, 'criterionid' => $data->criterionid, 'studentid' => $data->studentid,
                            'level' => $data->level, 'comment' => $data->comment, 'status' => $data->status, ]
                    );
                $data->declid = $results['id'];
            }
            $alldebugs = array_map(fn($debug) => $debug->export_for_template($renderer), $debugs);

            if ($data->supervisors) {
                ['results' => $results, 'debug' => $debugs[]] =
                    mobileview_helper::call_api(
                        \local_competvet\external\set_certs_supervisors::class,
                        ['declid' => $data->declid, 'supervisors' => $data->supervisors]
                    );
                $alldebugs = array_merge($alldebugs, array_map(fn($debug) => $debug->export_for_template($renderer), $debugs));
            }
            return [
                'result' => true,
                'debugs' => $alldebugs,
                'returnurl' => ($this->get_page_url_for_dynamic_submission())->out_as_local_url(),
            ];
        } catch (\Exception $e) {
            return [
                'result' => false,
                'debugs' => $alldebugs,
                'error' => $e->getMessage(),
            ];
        }
    }

}
