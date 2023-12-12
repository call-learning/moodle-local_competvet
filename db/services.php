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

/**
 * CompetVet services
 *
 * @package   local_competvet
 * @copyright 2023 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_competvet\utils;

defined('MOODLE_INTERNAL') || die();
$functions = [
    'local_competvet_get_application_mode' => [
        'classname' => 'local_competvet\\external\\get_application_mode',
        'methodname' => 'execute',
        'description' => 'Get the application mode for this user.',
        'type' => 'read',
        'capabilities' => 'local/competvet:mobileaccess',
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
    'local_competvet_get_user_profile' => [
        'classname' => 'local_competvet\\external\\user_info',
        'methodname' => 'execute',
        'description' => 'Get user profile information',
        'type' => 'read',
        'capabilities' => 'local/competvet:mobileaccess',
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
    'local_competvet_get_idplist' => [
        'classname' => 'local_competvet\\external\\idplist',
        'methodname' => 'execute',
        'description' => 'Get IDP list for connexion',
        'ajax' => true,
        'type' => 'read',
        'loginrequired' => false,
    ],
    'local_competvet_get_situations' => [
        'classname' => 'local_competvet\\external\\get_situations',
        'methodname' => 'execute',
        'description' => 'Get Situations and planning for a given user or the current user',
        'ajax' => true,
        'type' => 'read',
        'loginrequired' => false,
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
    'local_competvet_get_eval_observation_info' => [
        'classname' => 'local_competvet\\external\\get_eval_observation_info',
        'methodname' => 'execute',
        'description' => 'Get Observation information for the eval component of application',
        'ajax' => true,
        'type' => 'read',
        'loginrequired' => false,
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
    'local_competvet_get_planning_infos_students' => [
        'classname' => 'local_competvet\\external\\get_planning_infos_students',
        'methodname' => 'execute',
        'description' => 'Get planning information stats (nb evaluation done, certif done...)
        regarding students for a given planning',
        'ajax' => true,
        'type' => 'read',
        'loginrequired' => false,
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
    'local_competvet_get_plannings_info' => [
        'classname' => 'local_competvet\external\get_plannings_infos',
        'methodname' => 'execute',
        'description' => 'Get planning information statistics',
        'ajax' => true,
        'type' => 'read',
        'loginrequired' => false,
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
    'local_competvet_get_situation_criteria' => [
        'classname' => 'local_competvet\external\get_situation_criteria',
        'methodname' => 'execute',
        'description' => 'Get all criteria for a given situation',
        'ajax' => true,
        'type' => 'read',
        'loginrequired' => false,
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
    'local_competvet_get_user_evaluations' => [
        'classname' => 'local_competvet\external\get_user_evaluations',
        'methodname' => 'execute',
        'description' => 'Get all evaluation on a given planning for the given user',
        'ajax' => true,
        'type' => 'read',
        'loginrequired' => false,
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
    'local_competvet_get_users_for_planning' => [
        'classname' => 'local_competvet\external\get_users_for_planning',
        'methodname' => 'execute',
        'description' => 'Get all users involved in this planning (i.e. students and observers)',
        'ajax' => true,
        'type' => 'read',
        'loginrequired' => false,
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
];

$services = utils::get_mobile_services_definition($functions);
