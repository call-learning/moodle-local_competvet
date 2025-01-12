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
 * Renderer class.
 *
 * @package   local_competvet
 * @copyright 2023 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class debug implements \renderable, \templatable {
    /**
     * Constructor
     *
     * @param string $apifunction
     * @param array|null $params
     * @param mixed $results
     * @param float|null $duration
     */
    public function __construct(
        /** @var string The API function name  */
        protected string $apifunction,
        /**@var array Params*/
        protected ?array $params,
        /** @var mixed Results*/
        protected mixed $results,
        /** @var fload|null  Duration */
        protected ?float $duration
    ) {
    }

    /**
     * Exports the data for a template.
     *
     * @param renderer_base $output The renderer instance.
     *
     * @return string[] An array containing the exported data for the template.
     */
    public function export_for_template(renderer_base $output) {
        return [
            'apifunction' => $this->apifunction,
            'params' => json_encode($this->params),
            'results' => json_encode($this->results, JSON_PRETTY_PRINT),
            'duration' => $this->duration,
        ];
    }
}
