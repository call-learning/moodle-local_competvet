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
    'local_competvet_get_user_type' => [
        'classname' => 'local_competvet\\external\\user_type',
        'methodname' => 'execute',
        'description' => 'Get user type (student, appraiser, assessor). See local_competvet\local\role\entity',
        'type' => 'read',
        'capabilities' => 'local/competvet:mobileaccess',
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
    'local_competvet_get_user_profile' => [
        'classname' => 'local_competvet\\external\\user_profile',
        'methodname' => 'execute',
        'description' => 'Get user profile information',
        'type' => 'read',
        'capabilities' => 'local/competvet:mobileaccess',
        'services' => [utils::COMPETVET_MOBILE_SERVICE],
    ],
    'local_competvet_get_idplist' => [
        'classname' => 'local_competvet\\external\\auth',
        'methodname' => 'idp_list',
        'description' => 'Get IDP list for connexion',
        'ajax' => true,
        'type' => 'read',
        'loginrequired' => false,
    ],
];

$services = utils::get_mobile_services_definition($functions);
