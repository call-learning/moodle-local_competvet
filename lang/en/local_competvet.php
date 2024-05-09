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
 * You may localized strings in your plugin
 *
 * @package   local_competvet
 * @copyright 2023 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['pluginname'] = 'CompetVet Local Plugin';
$string['appservicename'] = 'CompetVet Mobile Service';
$string['competvet:mobileaccess'] = 'Access CompetVet Mobile Service';
$string['competvet:mobileaccess_help'] = 'Access CompetVet Mobile Service';
$string['invalidroleforuser'] = 'Invalid role for user {$a->userid}, check with you administrator to see if the roles
 assigned in different situation are consistent (for example it is not possible to be both observer and student)';
$string['mobileview:situations']  = 'Situations';
$string['mobileview:planningdates']  = '{$a->startdate} to {$a->enddate}';
$string['mobileview:observer:groupinfo']  = '{$a->groupname} ({$a->nbstudents} students) '; // We changed here and only
// display students number in this group.
$string['mobileview:usertype:observers']  = 'Observers';
$string['mobileview:usertype:students']  = 'Students ({$a})';
$string['mobileview:eval']  = 'Eval';
$string['mobileview:certif']  = 'Certif';
$string['mobileview:list']  = 'List';
$string['usertype:display'] = 'User type: {$a}';
$string['mobileview:tab:situation']  = 'Situation';
$string['mobileview:tab:search']  = 'Search';
$string['mobileview:tab:todo']  = 'TODO';

$string['observation:add'] = 'Add an observation';
$string['observation:created'] = 'Observation created';
$string['observation:edited'] = 'Observation edited';
$string['observation:ask'] = 'Ask for an observation';
$string['observation:ask:save'] = 'Select observer';
$string['observation:asked'] = 'Observation asked';
$string['observation:asked:body'] = 'Observation asked to {$a}';
$string['observation:edit'] = 'Edit observation';
$string['observation:delete'] = 'Delete the observation';
$string['observation:delete:confirm'] = 'Confirm you want to delete the observation';
$string['observation:add:save'] = 'Save';
$string['observation:edit:save'] = 'Save';
$string['observation:comment:commentno'] = 'Comment {no}';
$string['observation:comment:add'] = 'Add comment';
$string['observation:comment:deleteno'] = 'Delete comment {no}';
$string['todo:updated'] = 'Todo updated';
$string['error:compet_test_driver_mode'] = 'Competency test driver mode is not set';