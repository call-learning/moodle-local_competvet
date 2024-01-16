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

use external_value;

/**
 * API Helpers
 *
 * External services : user profile
 */
class api_helpers {
    public static function get_user_info_structure() {
        return [
            'id' => new \external_value(PARAM_INT, 'ID type of user'),
            'fullname' => new \external_value(PARAM_TEXT, 'User fullname'),
            'userpictureurl' => new \external_value(PARAM_URL, 'User picture (avatar) URL', VALUE_OPTIONAL),
        ];
    }

    public static function get_planning_info_structure() {
        return [
            'id' => new external_value(PARAM_INT, 'Plan ID'),
            'startdate' => new external_value(PARAM_INT, 'Plan start date'),
            'enddate' => new external_value(PARAM_INT, 'Plan end date'),
            'groupname' => new external_value(PARAM_TEXT, 'Group name'),
            'groupid' => new external_value(PARAM_INT, 'Group id (Internal ID)'),
            'session' => new external_value(PARAM_ALPHANUMEXT, 'Session name (unused now but might be later)'),
            'situationid' => new external_value(PARAM_INT, 'Situation ID'),
        ];
    }

    public static function get_criteria_info_structure() {
        return [
            'id' => new external_value(PARAM_INT, 'Criterion ID'),
            'label' => new external_value(PARAM_TEXT, 'Criterion label'),
            'idnumber' => new external_value(PARAM_TEXT, 'Criterion idnumber'),
            'sort' => new external_value(PARAM_INT, 'Criterion sort'),
            'parentid' => new external_value(PARAM_INT, 'Criterion parentid'),
        ];
    }
}