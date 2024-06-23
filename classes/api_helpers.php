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
     * Get comment structure
     *
     * @return array
     */
    public static function get_comment_structure(): array {
        return [
            'id' => new external_value(PARAM_INT, 'Comment ID', VALUE_OPTIONAL),
            'comment' => new external_value(PARAM_RAW, 'Comment text'),
            'commentlabel' => new external_value(PARAM_RAW, 'Comment label', VALUE_OPTIONAL),
            'type' => new external_value(PARAM_ALPHANUMEXT, 'Comment type (autoeval, eval, certif...)', VALUE_OPTIONAL),
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
    public static function get_user_info_structure(): array {
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
    public static function get_context_structure(): array {
        return [
            'id' => new external_value(PARAM_INT, 'Comment ID', VALUE_OPTIONAL),
            'comment' => new external_value(PARAM_RAW, 'Comment text'),
            'userinfo' => new external_single_structure(self::get_user_info_structure(), 'User information', VALUE_OPTIONAL),
            'timecreated' => new external_value(PARAM_INT, 'Comment creation time', VALUE_OPTIONAL),
            'timemodified' => new external_value(PARAM_INT, 'Comment last modification time', VALUE_OPTIONAL),
        ];
    }

    /**
     * Get criteria structure
     *
     * @return array
     */
    public static function get_criteria_structure(): array {
        return [
            'id' => new external_value(PARAM_INT, 'Observation criteria ID', VALUE_OPTIONAL),
            'level' => new external_value(PARAM_INT, 'Criterion level', VALUE_OPTIONAL, 50),
            'isactive' => new external_value(PARAM_BOOL, 'Criterion active', VALUE_OPTIONAL, false),
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
    public static function get_subcriteria_info_structure(): array {
        return [
            'id' => new external_value(PARAM_INT, 'Observation subcriteria ID', VALUE_OPTIONAL),
            'comment' => new external_value(PARAM_RAW, 'Criterion comment', VALUE_OPTIONAL),
            'isactive' => new external_value(PARAM_BOOL, 'Criterion active', VALUE_OPTIONAL, false),
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
    public static function get_criteria_info_structure(): array {
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
    public static function get_observation_criteria_level_value_structure(): array {
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
    public static function get_observation_criteria_comment_structure(): array {
        return [
            'id' => new external_value(PARAM_INT, 'Criterion Instance (comment or level) ID', VALUE_OPTIONAL),
            'criterionid' => new external_value(PARAM_INT, 'Criterion ID', VALUE_OPTIONAL),
            'comment' => new external_value(PARAM_RAW, 'Criterion comment', VALUE_OPTIONAL),
        ];
    }

    public static function get_todo_info_structure(): array {
        return [
            'id' => new external_value(PARAM_INT, 'TODO internal ID'),
            'user' => new external_single_structure(self::get_user_info_structure(), 'User information'),
            'targetuser' => new external_single_structure(
                self::get_user_info_structure(),
                'Target User information'
            ),
            'planning' => new external_single_structure(self::get_planning_info_structure(), 'Planning information'),
            'status' => new external_value(PARAM_INT, 'TODO current Status'),
            'action' => new external_value(PARAM_INT, 'TODO action to perform'),
            'data' => new external_value(PARAM_RAW, 'TODO data (JSON)'),
        ];
    }

    /**
     * Get planning info structure
     *
     * @return array
     */
    public static function get_planning_info_structure(): array {
        return [
            'id' => new external_value(PARAM_INT, 'Plan ID'),
            'startdate' => new external_value(PARAM_INT, 'Plan start date'),
            'enddate' => new external_value(PARAM_INT, 'Plan end date'),
            'groupname' => new external_value(PARAM_TEXT, 'Group name'),
            'groupid' => new external_value(PARAM_INT, 'Group id (Internal ID)'),
            'session' => new external_value(PARAM_ALPHANUMEXT, 'Session name (unused now but might be later)'),
            'situationid' => new external_value(PARAM_INT, 'Situation ID'),
            'situationname' => new external_value(PARAM_TEXT, 'Situation name', VALUE_OPTIONAL),
        ];
    }

    public static function get_certif_info_structure(): array {
        return
            [
                'declid' => new external_value(PARAM_INT, 'Declaration ID'),
                'label' => new external_value(PARAM_TEXT, 'Item name'),
                'grade' => new external_value(PARAM_INT, 'Grade'),
                'criterionid' => new external_value(PARAM_INT, 'Criterion ID'),
                'status' => new external_value(PARAM_INT, 'The declaration status (this can be different from the global status)'),
                'isdeclared' => new external_value(PARAM_BOOL, 'Declared'),
                'seendone' => new external_value(PARAM_BOOL, 'Seen and done (student)'),
                'notseen' => new external_value(PARAM_BOOL, 'Not seen (student)'),
                'observernotseen' => new external_value(PARAM_BOOL, 'Not seen (observer)'),
                'confirmed' => new external_value(PARAM_BOOL, 'Confirmed by observer'),
                'levelnotreached' => new external_value(PARAM_BOOL, 'Level Not reached (observer)'),
                'level' => new external_value(PARAM_INT, 'Declaration level', VALUE_OPTIONAL),
                'comment' => new external_value(PARAM_TEXT, 'Declaration comment'),
                'feedback' => new external_single_structure([
                    'picture' => new external_value(PARAM_TEXT, 'The picture'),
                    'fullname' => new external_value(PARAM_TEXT, 'The fullname'),
                    'comment' => new external_value(PARAM_TEXT, 'The comment'),
                ], 'The feedback', VALUE_OPTIONAL),
                'validations' => new external_multiple_structure(
                    new external_single_structure([
                        'id' => new external_value(PARAM_INT, 'The validation id'),
                        'feedback' => new external_single_structure([
                            'picture' => new external_value(PARAM_TEXT, 'The picture'),
                            'fullname' => new external_value(PARAM_TEXT, 'The fullname'),
                            'comment' => new external_value(PARAM_TEXT, 'The comment'),
                        ], 'The feedback', VALUE_OPTIONAL),
                        'comment' => new external_value(PARAM_TEXT, 'Declaration comment'),
                        'status' => new external_value(PARAM_INT, 'The status'),
                    ]),
                    'The validations',
                    VALUE_OPTIONAL
                ),
            ];
    }

    public static function get_case_info_structure(): array {
        return
            [
                'id' => new external_value(PARAM_INT, 'Case id ID'),
                'date' => new external_value(PARAM_INT, 'Case date (timestamp)'),
                'animal' => new external_value(PARAM_TEXT, 'Animal name'),
                'label' => new external_value(PARAM_TEXT, 'Case label'),
            ];
    }

    public static function get_case_item_info_structure() {
        return [
            'id' => new external_value(PARAM_INT, 'The case id'),
            'timecreated' => new external_value(PARAM_INT, 'The time the case was created'),
            'categories' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'The category id'),
                    'name' => new external_value(PARAM_TEXT, 'The category name'),
                    'fields' => new external_multiple_structure(
                        self::get_case_field_info_structure()
                    ),
                ])
            ),
        ];
    }

    public static function get_caselog_category_info_structure() {
        return
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'The category id'),
                'name' => new external_value(PARAM_TEXT, 'The category name'),
                'fields' => new external_multiple_structure(
                    new external_single_structure([
                        'id' => new external_value(PARAM_INT, 'The field id'),
                        'idnumber' => new external_value(PARAM_TEXT, 'The field shortname'),
                        'name' => new external_value(PARAM_TEXT, 'The field name'),
                        'type' => new external_value(PARAM_TEXT, 'The field type'),
                        'configdata' => new external_value(PARAM_RAW, 'The field configdata'),
                        'description' => new external_value(PARAM_TEXT, 'The field description'),
                    ])
                ),
            ]);
    }

    /**
     * Get caselog field structrure
     *
     * @return external_single_structure
     */
    public static function get_case_field_info_structure() {
        return new external_single_structure([
            'id' => new external_value(PARAM_INT, 'The field id'),
            'idnumber' => new external_value(PARAM_TEXT, 'The field shortname'),
            'name' => new external_value(PARAM_TEXT, 'The field name'),
            'type' => new external_value(PARAM_TEXT, 'The field type'),
            'configdata' => new external_value(PARAM_RAW, 'The field configdata'),
            'description' => new external_value(PARAM_TEXT, 'The field description'),
            'value' => new external_value(PARAM_TEXT, 'The field value', VALUE_OPTIONAL),
            'displayvalue' => new external_value(PARAM_TEXT, 'The field display value', VALUE_OPTIONAL),
        ]);
    }
}
