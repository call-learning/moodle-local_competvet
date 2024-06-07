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
namespace local_competvet\output\view;

use mod_competvet\competvet;
use mod_competvet\local\api\cases;
use mod_competvet\local\api\certifications;
use mod_competvet\local\api\observations;
use mod_competvet\local\api\plannings;
use mod_competvet\local\api\user_role;
use mod_competvet\local\persistent\situation;
use mod_competvet\output\view\base;
use moodle_url;
use renderer_base;
use stdClass;

/**
 * Mobile view renderable for the view: list all evaluation (eval, certif, list)
 * Will be deprecated once we remove the mobileview in the local plugin.
 *
 * @package    mod_competvet
 * @copyright  2023 CALL Learning - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class student_mobile_evaluations extends base {
    /**
     * @var array $observations The evaluations' information.
     */
    protected array $observations;

    /**
     * @var array $certifications The certifications' information.
     */
    protected array $certifications;
    /**
     * @var array $cases The cases' information.
     */
    protected array $cases;

    /**
     * @var array $planninginfo The planning information.
     */
    protected array $planninginfo;

    /**
     * @var moodle_url[] $view The url to view different evaluation types.
     */
    protected array $views;
    /**
     * @var string $currenttab The current tab name.
     */
    protected string $currenttab;

    /**
     * Export this data so it can be used in a mustache template.
     *
     * @param renderer_base $output
     * @return array|array[]|stdClass
     */
    public function export_for_template(renderer_base $output) {
        $data = parent::export_for_template($output);
        $data['tabs'] = [];
        $stringcontext = (object) [
            'done' => 0,
            'required' => 0,
            'certdone' => 0,
            'certopen' => 0,
            'cases' => 0,
        ];
        // Concatenate stats for autoeval and eval.
        foreach ($this->planninginfo['info'] as $userinfovalue) {
            $infotype = $userinfovalue['type'];
            if ($infotype == 'autoeval') {
                continue;
            }
            if ($infotype == 'autoeval' || $infotype == 'eval') {
                $stringcontext->done += $userinfovalue['nbdone'];
                $stringcontext->required += $userinfovalue['nbrequired'];
            }
            if ($infotype == 'cert') {
                $stringcontext->certdone = $userinfovalue['nbdone'];
                $stringcontext->certopen = $userinfovalue['nbrequired'];
            }
            if ($infotype == 'list') {
                $stringcontext->cases = $userinfovalue['nbdone'];
            }
            $tab = [
                'id' => $userinfovalue['type'],
                'url' => new moodle_url($this->baseurl, ['currenttab' => $infotype]),
                'itemtype' => $userinfovalue['type'],
                'label' => get_string('tab:' . $infotype, 'mod_competvet', $stringcontext),
                'isactive' => $this->currenttab == $infotype,
            ];
            switch ($infotype) {
                case 'eval':
                    $tab['observations'] = array_values(
                        array_reduce($this->observations, function($carry, $item) use ($output) {
                            $observer = $item['observerinfo'];
                            $evaluationinfo = [
                                'userpictureurl' => $observer['userpictureurl'],
                                'fullname' => $observer['fullname'],
                                'evaluationtime' => $item['time'],
                                'viewurl' => (new moodle_url(
                                    $this->views['eval'],
                                    ['obsid' => $item['id']]
                                ))->out(false),
                            ];
                            if (!isset($carry[$item['category']])) {
                                $carry[$item['category']] = [
                                    'categorytext' => $item['categorytext'],
                                    'category' => $item['category'],
                                    'obslist' => [],
                                ];
                            }
                            $carry[$item['category']]['obslist'][] = $evaluationinfo;
                            return $carry;
                        },
                            [])
                    );
                    $tab['hasobservations'] = count($tab['observations']) > 0;
                    break;
                case 'cert':
                    $viewcertsurl = $this->views['certif'];
                    $certs = [];
                    foreach($this->certifications as $cert) {
                        $cert['items'] = array_map(function($item) use($viewcertsurl) {
                            if (!empty($item['declid'])) {
                                $item['viewurl'] = (new moodle_url(
                                    $viewcertsurl,
                                    ['id' => $item['declid']]
                                ))->out(false);
                            }
                            return $item;
                        }, $cert['items']);
                        $certs[] = $cert;
                    }
                    $tab['certifications'] = $certs;
                    $tab['hascertifications'] = count($tab['certifications']) > 0;
                    break;
                case 'list':
                    $viewcaseurl = $this->views['case'];
                    $cases = array_map(function($item) use($viewcaseurl) {
                        $item['viewurl'] = (new moodle_url(
                            $viewcaseurl,
                                ['id' => $item['id']]
                            ))->out(false);
                        return $item;
                    }, $this->cases);
                    $tab['cases'] = $cases;
                    $tab['hascases'] = count($tab['cases']) > 0;
                    break;
            }
            $data['tabs'][] = $tab;
        }
        // Find planning, module infos.
        $planning = \mod_competvet\local\persistent\planning::get_record(['id' => $this->planninginfo['planningid']]);
        $situation = situation::get_record(['id' => $planning->get('situationid')]);
        $competvet = competvet::get_from_situation($situation);
        $data['cmid'] = $competvet->get_course_module_id();
        $data['planningid'] = $this->planninginfo['planningid'];
        $data['studentid'] = $this->planninginfo['id'];
        $userrole = user_role::get_top($this->currentuserid, $situation->get('id'));
        $data['isstudent'] = $userrole == 'student';
        return $data;
    }

    /**
     * Set data for the object.
     *
     * If data is empty we autofill information from the API and the current user.
     * If not, we get the information from the parameters.
     *
     * The idea behind it is to reuse the template in mod_competvet and local_competvet
     *
     * @param mixed ...$data Array containing two elements: $plannings and $planningstats.
     * @return void
     */
    public function set_data(...$data) {
        $this->currenttab = optional_param('currenttab', 'eval', PARAM_ALPHA);
        if (empty($data)) {
            global $PAGE;
            $context = $PAGE->context;
            $planningid = required_param('planningid', PARAM_INT);
            $studentid = required_param('studentid', PARAM_INT);
            $userobservations = observations::get_user_observations($planningid, $studentid);
            $competvet = competvet::get_from_context($context);
            $planninginfo = plannings::get_planning_info_for_student($planningid, $studentid);
            $usercertifications = certifications::get_certifications($planningid, $studentid);
            $usercases = cases::get_case_list($planningid, $studentid);
            $data = [
                $planninginfo,
                [
                    'eval' => new moodle_url(
                        $this->baseurl,
                        ['pagetype' => 'student_eval', 'id' => $competvet->get_course_module_id()]
                    ),
                    'list' => new moodle_url(
                        $this->baseurl,
                        ['pagetype' => 'student_list', 'id' => $competvet->get_course_module_id()]
                    ),
                    'certif' => new moodle_url(
                        $this->baseurl,
                        ['pagetype' => 'student_certif', 'id' => $competvet->get_course_module_id()]
                    ),
                ],
                $userobservations,
                $usercertifications,
                $usercases,
            ];
            $this->set_backurl(new moodle_url(
                $this->baseurl,
                ['pagetype' => 'planning', 'id' => $competvet->get_course_module_id(), 'planningid' => $planningid]
            ));
        }
        [$this->planninginfo, $this->views, $this->observations, $this->certifications, $this->cases] = $data;
    }
}
