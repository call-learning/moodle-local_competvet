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

use external_multiple_structure;
use external_single_structure;
use external_value;

/**
 * API Helpers
 *
 * Common structures used in the API.
 *
 * @package   local_competvet
 * @copyright 2023 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class api_helpers {
    /**
     * Get planning info structure
     *
     * @return array
     */
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

    /**
     * Get comment structure
     *
     * @return array
     */
    public static function get_comment_structure() {
        return [
            'id' => new external_value(PARAM_INT, 'Comment ID', VALUE_OPTIONAL),
            'comment' => new external_value(PARAM_RAW, 'Comment text'),
            'commentlabel' => new external_value(PARAM_RAW, 'Comment label', VALUE_OPTIONAL),
            'type' => new external_value(PARAM_INT, 'Comment type (autoeval, eval, certif...)', VALUE_OPTIONAL),
            'userinfo' => new external_single_structure(self::get_user_info_structure(), 'User information', VALUE_OPTIONAL),
            'timecreated' => new external_value(PARAM_INT, 'Comment creation time', VALUE_OPTIONAL),
            'timemodified' => new external_value(PARAM_INT, 'Comment last modification time', VALUE_OPTIONAL),
        ];
    }

    /**
     * Get user info structure
     *
     * @return array
     */
    public static function get_user_info_structure() {
        return [
            'id' => new \external_value(PARAM_INT, 'ID type of user'),
            'fullname' => new \external_value(PARAM_TEXT, 'User fullname', VALUE_OPTIONAL),
            'userpictureurl' => new \external_value(PARAM_URL, 'User picture (avatar) URL', VALUE_OPTIONAL),
            'role' => new \external_value(PARAM_TEXT, 'User role', VALUE_OPTIONAL),
        ];
    }

    /**
     * Get context structure
     *
     * @return array
     */
    public static function get_context_structure() {
        return [
            'id' => new external_value(PARAM_INT, 'Comment ID', VALUE_OPTIONAL),
            'comment' => new external_value(PARAM_RAW, 'Comment text'),
            'userinfo' => new external_single_structure(self::get_user_info_structure(), VALUE_OPTIONAL),
            'timecreated' => new external_value(PARAM_INT, 'Comment creation time', VALUE_OPTIONAL),
            'timemodified' => new external_value(PARAM_INT, 'Comment last modification time', VALUE_OPTIONAL),
        ];
    }

    /**
     * Get criteria structure
     *
     * @return array
     */
    public static function get_criteria_structure() {
        return [
            'id' => new external_value(PARAM_INT, 'Observation criteria ID', VALUE_OPTIONAL),
            'level' => new external_value(PARAM_INT, 'Criterion level', VALUE_OPTIONAL),
            'subcriteria' => new external_multiple_structure(
                new external_single_structure(
                    self::get_subcriteria_info_structure()
                ),
                'Subcriteria',
                VALUE_OPTIONAL
            ),
            'criterioninfo' => new external_single_structure(
                self::get_criteria_info_structure(),
                'Criterion info',
                VALUE_OPTIONAL
            ),
        ];
    }

    /**
     * Get criteria info structure
     *
     * @return array
     */
    public static function get_subcriteria_info_structure() {
        return [
            'id' => new external_value(PARAM_INT, 'Observation subcriteria ID', VALUE_OPTIONAL),
            'comment' => new external_value(PARAM_RAW, 'Criterion comment', VALUE_OPTIONAL),
            'criterioninfo' => new external_single_structure(
                self::get_criteria_info_structure(),
                'SubCriterion info',
                VALUE_OPTIONAL
            ),
        ];
    }

    /**
     * Get criteria info structure
     *
     * @return array
     */
    public static function get_criteria_info_structure() {
        return [
            'id' => new external_value(PARAM_INT, 'Criterion ID'),
            'label' => new external_value(PARAM_TEXT, 'Criterion label', VALUE_OPTIONAL),
            'idnumber' => new external_value(PARAM_TEXT, 'Criterion idnumber', VALUE_OPTIONAL),
            'sort' => new external_value(PARAM_INT, 'Criterion sort', VALUE_OPTIONAL),
            'parentid' => new external_value(PARAM_INT, 'Criterion parentid', VALUE_OPTIONAL),
        ];
    }


    /**
     * Get criteria info structure
     *
     * @return array
     */
    public static function get_observation_criteria_level_value_structure() {
        return [
            'id' => new external_value(PARAM_INT, 'Criterion Instance (comment or level) ID', VALUE_OPTIONAL),
            'criterionid' => new external_value(PARAM_INT, 'Criterion ID', VALUE_OPTIONAL),
            'level' => new external_value(PARAM_INT, 'Criterion level', VALUE_OPTIONAL),
            'isactive' => new external_value(PARAM_BOOL, 'Criterion is active', VALUE_OPTIONAL),
        ];
    }

    /**
     * Get criteria info structure
     *
     * @return array
     */
    public static function get_observation_criteria_comment_structure() {
        return [
            'id' => new external_value(PARAM_INT, 'Criterion Instance (comment or level) ID', VALUE_OPTIONAL),
            'criterionid' => new external_value(PARAM_INT, 'Criterion ID', VALUE_OPTIONAL),
            'comment' => new external_value(PARAM_RAW, 'Criterion comment', VALUE_OPTIONAL),
        ];
    }
}
