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

use context_system;
use context_user;
use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use local_competvet\api_helpers;
use mod_competvet\local\api\plannings;
use mod_competvet\local\persistent\situation;

/**
 * Get planning infos for a given user.
 *
 * @package   local_competvet
 * @copyright 2023 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_plannings_infos extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_multiple_structure
     */
    public static function execute_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'id' => new external_value(PARAM_INT, 'Planning ID'),
                    'category' => new external_value(PARAM_INT, 'Planning category (current, future, past)'),
                    'categorytext' => new external_value(PARAM_TEXT, 'Planning category (current, future, past)'),
                    'info' => new external_single_structure(api_helpers::get_planning_info_structure()),
                    'groupstats' => new external_single_structure([
                        'groupid' => new external_value(PARAM_INT, 'Group ID'),
                        'nbstudents' => new external_value(PARAM_INT, 'Nb of students in this group'),
                    ], '', VALUE_OPTIONAL),
                ]
            )
        );
    }

    /**
     * Return the list of situations the user is registered in
     *
     * @param int|null $userid
     * @param array|null $plannings
     * @return array
     */
    public static function execute(?int $userid = null, ?array $plannings = []): array {
        global $USER;
        ['plannings' => $plannings, 'userid' => $userid] =
            self::validate_parameters(self::execute_parameters(), ['plannings' => $plannings, 'userid' => $userid]);
        self::validate_context(context_system::instance());
        if (!$userid) {
            $userid = $USER->id;
        }
        self::validate_context(context_user::instance($userid));
        if (empty($plannings)) {
            $situationsid = situation::get_all_situations_id_for($userid);
            $plannings = [];
            foreach ($situationsid as $situationid) {
                $allplannings = plannings::get_plannings_for_situation_id($situationid, $userid, true);
                $plannings = array_merge($plannings, array_column($allplannings, 'id'));
            }
        }
        $stats = [];
        if ($plannings) {
            $stats = plannings::get_planning_infos($plannings, $userid);
        }
        return $stats;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            [
                'userid' => new external_value(PARAM_INT, 'id of the user (optional parameter)', VALUE_DEFAULT, 0),
                'plannings' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'Planning ID'),
                    'List of planning IDs (optional parameter)',
                    VALUE_OPTIONAL,
                    []
                ),
            ]
        );
    }
}
