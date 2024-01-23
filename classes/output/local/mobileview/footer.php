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
namespace local_competvet\output\local\mobileview;

use renderer_base;

/**
 * Renderer class for footer to emulate the app display.
 *
 * @package   local_competvet
 * @copyright 2023 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class footer implements \renderable, \templatable {
    /**
     * Constructor.
     *
     * @param string $selectedtab
     */
    public function __construct(protected string $selectedtab) {
    }

    /**
     * Exports the data for a template.
     *
     * @param renderer_base $output The renderer instance.
     *
     * @return array An array containing the exported data for the template.
     */
    public function export_for_template(renderer_base $output): array {
        $definition = [
            [
                'label' => get_string('mobileview:tab:situation', 'local_competvet'),
                'baseurl' => new \moodle_url('/local/competvet/mobileview/index.php'),
                'name' => 'situation',
            ],
            [
                'label' => get_string('mobileview:tab:search', 'local_competvet'),
                'name' => 'search',
                'baseurl' => new \moodle_url('/local/competvet/mobileview/search/index.php'),
            ],
            [
                'label' => get_string('mobileview:tab:todo', 'local_competvet'),
                'baseurl' => new \moodle_url('/local/competvet/mobileview/todo/index.php'),
                'name' => 'todo',
            ]
        ];
        foreach ($definition as &$tab) {
            if ($tab['name'] == $this->selectedtab) {
                $tab['selected'] = true;
            }
        }
        return ['tabs' => $definition];
    }
}
