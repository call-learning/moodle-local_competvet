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
namespace local_competvet\external;
defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/externallib.php');

use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use local_competvet\utils;
use moodle_url;

/**
 * IDP List management
 *
 * @package   local_competvet
 * @copyright 2023 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class idplist extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([]);
    }
    /**
     * Returns description of method parameters
     *
     * @return external_multiple_structure
     */
    public static function execute_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'url' => new external_value(PARAM_RAW, 'URL to launch IDP connexion', VALUE_OPTIONAL),
                    'name' => new external_value(PARAM_TEXT, 'IDP fullname'),
                    'iconurl' => new external_value(PARAM_RAW, 'IDP icon url', VALUE_OPTIONAL),
                ]
            )
        );
    }

    /**
     * Return the current information for the user
     * @return array
     */
    public static function execute(): array {
        $authsenabled = get_enabled_auth_plugins();
        $idplist = [];
        foreach ($authsenabled as $auth) {
            $authplugin = get_auth_plugin($auth);
            $currentidplist = $authplugin->loginpage_idp_list(utils::get_application_launch_url([]));
            foreach ($currentidplist as $index => $idp) {
                if ($auth == 'cas') {
                    $idp['url'] = (new moodle_url('/local/competvet/webservices/cas-login.php', ['authCAS' => 'CAS']))->out();
                } else {
                    $idp['url'] = $idp['url'] ? $idp['url']->out() : '';
                }
                $idp['iconurl'] = $idp['iconurl'] ? $idp['iconurl']->out() : '';
                $currentidplist[$index] = $idp;
            }
            if ($currentidplist) {
                $idplist = array_merge($currentidplist, $idplist);
            }
        }
        return $idplist;
    }
}
