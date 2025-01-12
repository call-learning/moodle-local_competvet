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
use mod_competvet\output\view\base;
use renderer_base;
use stdClass;

/**
 * Mobile view renderable for the the certification view.
 *
 * @package    local_competvet
 * @copyright  2023 CALL Learning - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class student_cert_view extends base {
    /**
     * @var array $certification The certification information.
     */
    protected array $certification;

    /**
     * Export this data so it can be used in a mustache template.
     *
     * @param renderer_base $output
     * @return array|array[]|stdClass
     */
    public function export_for_template(renderer_base $output) {
        return $this->certification;
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
        if (empty($data)) {
            global $PAGE;
            $context = $PAGE->context;
            $declid = required_param('id', PARAM_INT);
            $data = [
                certifications::get_certification($declid, false),
            ];
        }
        [$this->certification] = $data;
    }
}
