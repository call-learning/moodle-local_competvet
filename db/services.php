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
        'classname' => \local_competvet\external\get_application_mode::class,
        'methodname' => 'execute',
        'description' => 'Get the application mode for this user.',
        'ajax' => true,
        'type' => 'read',
        'loginrequired' => true,
        'capabilities' => 'local/competvet:mobileaccess',
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
    'local_competvet_get_user_profile' => [
        'classname' => \local_competvet\external\user_info::class,
        'methodname' => 'execute',
        'description' => 'Get user profile information',
        'ajax' => true,
        'type' => 'read',
        'loginrequired' => true,
        'capabilities' => 'local/competvet:mobileaccess',
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
    'local_competvet_get_idplist' => [
        'classname' => \local_competvet\external\idplist::class,
        'methodname' => 'execute',
        'description' => 'Get IDP list for connexion',
        'ajax' => true,
        'type' => 'read',
        'loginrequired' => false,
    ],
    'local_competvet_get_situations' => [
        'classname' => \local_competvet\external\get_situations::class,
        'methodname' => 'execute',
        'description' => 'Get Situations and planning for a given user or the current user',
        'ajax' => true,
        'type' => 'read',
        'loginrequired' => true,
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
    'local_competvet_get_planning_infos_student' => [
        'classname' => \local_competvet\external\get_planning_infos_student::class,
        'methodname' => 'execute',
        'description' => 'Get planning information stats (nb evaluation done, certif done...) for a given student.
        regarding students for a given planning',
        'ajax' => true,
        'type' => 'read',
        'loginrequired' => true,
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
    'local_competvet_get_plannings_info' => [
        'classname' => \local_competvet\external\get_plannings_infos::class,
        'methodname' => 'execute',
        'description' => 'Get planning information statistics',
        'ajax' => true,
        'type' => 'read',
        'loginrequired' => true,
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
    'local_competvet_get_eval_situation_criteria' => [
        'classname' => \local_competvet\external\get_eval_situation_criteria::class,
        'methodname' => 'execute',
        'description' => 'Get all criteria for a given situation',
        'ajax' => true,
        'type' => 'read',
        'loginrequired' => true,
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
    'local_competvet_get_planning_info' => [
        'classname' => \local_competvet\external\get_planning_info::class,
        'methodname' => 'execute',
        'description' => 'Get info related to a given planning',
        'ajax' => true,
        'type' => 'read',
        'loginrequired' => true,
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
    'local_competvet_get_users_infos_for_planning' => [
        'classname' => \local_competvet\external\get_users_infos_for_planning::class,
        'methodname' => 'execute',
        'description' => 'Get all users involved in this planning (i.e. students and observers) and info about observations',
        'ajax' => true,
        'type' => 'read',
        'loginrequired' => true,
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
    'local_competvet_get_eval_observation_info' => [
        'classname' => \local_competvet\external\get_eval_observation_info::class,
        'methodname' => 'execute',
        'description' => 'Get Observation information for the eval component of application',
        'ajax' => true,
        'type' => 'read',
        'loginrequired' => true,
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
    'local_competvet_get_user_eval_observations' => [
        'classname' => \local_competvet\external\get_user_eval_observations::class,
        'methodname' => 'execute',
        'description' => 'Get all evaluation (EVAL) on a given planning for the given user',
        'ajax' => true,
        'type' => 'read',
        'loginrequired' => true,
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
    'local_competvet_create_eval_observation' => [
        'classname' => \local_competvet\external\create_eval_observation::class,
        'methodname' => 'execute',
        'description' => 'Create a new eval observation',
        'ajax' => true,
        'type' => 'write',
        'loginrequired' => true,
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
    'local_competvet_edit_eval_observation' => [
        'classname' => \local_competvet\external\edit_eval_observation::class,
        'methodname' => 'execute',
        'description' => 'Edit a given eval observation',
        'ajax' => true,
        'type' => 'write',
        'loginrequired' => true,
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
    'local_competvet_delete_eval_observation' => [
        'classname' => \local_competvet\external\delete_eval_observation::class,
        'methodname' => 'execute',
        'description' => 'Delete a given eval observation',
        'ajax' => true,
        'type' => 'write',
        'loginrequired' => true,
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
    'local_competvet_ask_eval_observation' => [
        'classname' => \local_competvet\external\ask_eval_observation::class,
        'methodname' => 'execute',
        'description' => 'Ask for an observation and add it to the list of TODOs',
        'ajax' => true,
        'type' => 'write',
        'loginrequired' => true,
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
    'local_competvet_get_todos' => [
        'classname' => \local_competvet\external\get_todos::class,
        'methodname' => 'execute',
        'description' => 'Get the TODO list for a user',
        'ajax' => true,
        'type' => 'read',
        'loginrequired' => true,
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
    'local_competvet_update_todo_status' => [
        'classname' => \local_competvet\external\update_todo_status::class,
        'methodname' => 'execute',
        'description' => 'Update TODO status (done, refused, cancelled, pending)',
        'ajax' => true,
        'type' => 'write',
        'loginrequired' => true,
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
    'local_competvet_create_certs_decl' => [
        'classname' => \local_competvet\external\create_certs_decl::class,
        'methodname' => 'execute',
        'description' => 'Create a new certification item',
        'ajax' => true,
        'type' => 'write',
        'loginrequired' => true,
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
    'local_competvet_get_certs_item_info' => [
        'classname' => \local_competvet\external\get_certs_item_info::class,
        'methodname' => 'execute',
        'description' => 'Get certification item information for a given declaration id',
        'ajax' => true,
        'type' => 'read',
        'loginrequired' => true,
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
    'local_competvet_get_user_certs_items' => [
        'classname' => \local_competvet\external\get_user_certs_items::class,
        'methodname' => 'execute',
        'description' => 'Get certification items for a given user in a planning',
        'ajax' => true,
        'type' => 'read',
        'loginrequired' => true,
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
    'local_competvet_get_set_certs_supervisors' => [
        'classname' => \local_competvet\external\set_certs_supervisors::class,
        'methodname' => 'execute',
        'description' => 'Set supervisors for a given certification item',
        'ajax' => true,
        'type' => 'write',
        'loginrequired' => true,
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
    'local_competvet_edit_certs_decl' => [
        'classname' => \local_competvet\external\edit_certs_decl::class,
        'methodname' => 'execute',
        'description' => 'Edit a given certification declaration',
        'ajax' => true,
        'type' => 'write',
        'loginrequired' => true,
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
    'local_competvet_create_certs_valid' => [
        'classname' => \local_competvet\external\create_certs_valid::class,
        'methodname' => 'execute',
        'description' => 'Create a new certification validation item',
        'ajax' => true,
        'type' => 'write',
        'loginrequired' => true,
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
    'local_competvet_edit_certs_valid' => [
        'classname' => \local_competvet\external\edit_certs_valid::class,
        'methodname' => 'execute',
        'description' => 'Edit an existing certification validation item',
        'ajax' => true,
        'type' => 'write',
        'loginrequired' => true,
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
    'local_competvet_get_delete_certs_item' => [
        'classname' => \local_competvet\external\delete_certs_item::class,
        'methodname' => 'execute',
        'description' => 'Delete a given certification item',
        'ajax' => true,
        'type' => 'write',
        'loginrequired' => true,
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
    'local_competvet_get_user_cases_items' => [
        'classname' => \local_competvet\external\get_user_cases_items::class,
        'methodname' => 'execute',
        'description' => 'Get certif items for a user in a planning',
        'ajax' => true,
        'type' => 'read',
        'loginrequired' => true,
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
    'local_competvet_get_caselog_structure' => [
        'classname' => \local_competvet\external\get_caselog_structure::class,
        'methodname' => 'execute',
        'description' => 'Get caselog structure',
        'ajax' => true,
        'type' => 'read',
        'loginrequired' => true,
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
    'local_competvet_get_user_cases_item_info' => [
        'classname' => \local_competvet\external\get_user_cases_item_info::class,
        'methodname' => 'execute',
        'description' => 'Get case item info from a caseid',
        'ajax' => true,
        'type' => 'read',
        'loginrequired' => true,
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
    'local_competvet_create_caselog' => [
        'classname' => \local_competvet\external\create_caselog::class,
        'methodname' => 'execute',
        'description' => 'Create caselog',
        'ajax' => true,
        'type' => 'write',
        'loginrequired' => true,
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
    'local_competvet_search_items' => [
        'classname' => \local_competvet\external\search_items::class,
        'methodname' => 'execute',
        'description' => 'Search for items in the application',
        'ajax' => true,
        'type' => 'read',
        'loginrequired' => true,
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
];

$services = utils::get_mobile_services_definition($functions);
