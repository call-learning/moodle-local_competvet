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
namespace local_competvet;


use context_system;
use moodle_exception;
use moodle_url;

/**
 * Mobile view helper class.
 *
 * @package   local_competvet
 * @copyright 2023 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mobileview_helper {

    public static function call_api(string $apifunctionname, array $parameters): array {
        $now = microtime(true);
        try {
            $parameters = $apifunctionname::validate_parameters($apifunctionname::execute_parameters(), $parameters);
            $results = $apifunctionname::execute(...$parameters);
            $results = $apifunctionname::clean_returnvalue($apifunctionname::execute_returns(), $results);
        } catch (\Throwable $e) {
            $results = $e;
            debugging($e->getMessage());
        } finally {
            $duration = microtime(true) - $now;
            $debug = new output\local\mobileview\debug($apifunctionname, $parameters, $results, $duration);
            return ['results' => $results, 'debug' => $debug];
        }
    }

    public static function mobile_view_header(moodle_url $currenturl, ?moodle_url $backurl = null): moodle_url {
        global $PAGE, $OUTPUT;
        $context = context_system::instance();
        $PAGE->set_context($context);
        $PAGE->set_url($currenturl);
        if ($backurl) {
            $PAGE->set_button(
                $OUTPUT->single_button(
                    $backurl,
                    get_string('back'),
                    'get'
                )
            );
        }
        return $backurl ?? $currenturl;
    }
}
