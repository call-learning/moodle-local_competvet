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
use mod_competvet\form\eval_observation_helper;
use mod_competvet\local\api\observations;
use mod_competvet\local\persistent\observation;

/**
 *
 * Observation edit form
 *
 * @package    mod_competvet
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class eval_observation_edit extends \mod_competvet\form\eval_observation_edit {
    /**
     * @var array debugs messages to add to the end of the form.
     */
    private $debugs = [];

    public function set_data_for_dynamic_submission(): void {
        $observationid = $this->optional_param('id', null, PARAM_INT);
        ['results' => $data, 'debug' => $this->debugs[]] =
            mobileview_helper::call_api(
                \local_competvet\external\get_eval_observation_info::class,
                ['observationid' => $observationid]
            );
        $this->set_data_for_dynamic_submission_helper($data);
    }
    public function definition_after_data() {
        global $PAGE;
        $mform = $this->_form;
        $renderer = $PAGE->get_renderer('local_competvet');
        parent::definition_after_data();
        foreach ($this->debugs as $debugwidget) {
            $mform->addElement('html', $renderer->render($debugwidget));
        }
    }
    /**
     * Set form data from observation information
     */
    public function process_dynamic_submission() {
        global $PAGE;
        $renderer = $PAGE->get_renderer('local_competvet');
        try {
            $data = $this->get_data();
            $observation = observation::get_record(['id' => $data->id]);
            $situation = $observation->get_situation();
            $context = eval_observation_helper::process_form_data_context($data);
            $comments = eval_observation_helper::process_form_data_comments($data);
            $criteria = eval_observation_helper::process_form_data_criteria($data, $situation);
            ['results' => $data, 'debug' => $debugs[]] =
                mobileview_helper::call_api(
                    \local_competvet\external\edit_eval_observation::class,
                    [
                        'observationid' =>  $data->id,
                        'context' => (array) $context,
                        'comments' => $comments,
                        'criteria' => $criteria,
                    ]
                );
            return [
                'result' => true,
                'content' => get_string('observation:edited', 'local_competvet'),
                'debugs' =>  array_map(fn($debug) => $debug->export_for_template($renderer), $debugs),
                'returnurl' => ($this->get_page_url_for_dynamic_submission())->out_as_local_url(),
            ];
        } catch (\Exception $e) {
            return [
                'result' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
