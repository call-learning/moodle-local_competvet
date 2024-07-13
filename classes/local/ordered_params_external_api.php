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
namespace local_competvet\local;
defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/externallib.php');

use external_api;
use external_description;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;

/**
 * External API with ordered parameters
 *
 * @package   local_competvet
 * @copyright 2023 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ordered_params_external_api extends external_api {
    /**
     * Fix for when any root parameter is empty
     *
     * @param external_description $description
     * @param $params
     * @return array|bool|mixed
     */
    public static function validate_parameters(external_description $description, $params) {
        // Check if param has a key missing and add null to it.
        if ($description instanceof external_function_parameters) {
            $orderedparams = [];
            foreach ($description->keys as $key => $param) {
                if (array_key_exists($key, $params)) {
                    // If the key exists in $params, use its value.
                    $orderedparams[$key] = $params[$key];
                } else {
                    if ($param instanceof external_multiple_structure) {
                        // If the key does not exist in $params and the param is single_value, use null.
                        $orderedparams[$key] = [];
                    }
                    if ($param instanceof external_single_structure) {
                        // If the key does not exist in $params and the param is single_value, use null but still should
                        // seen as an array.
                        $orderedparams[$key] = (array) null;
                    }
                }
            }
            $params = $orderedparams;
        }
        if ($description instanceof external_single_structure && $description->default == null && $params == null) {
            return null;
        }
        $params = parent::validate_parameters($description, $params);
        return $params;
    }
}