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
 * Settings for the Competvet plugin
 *
 * @package   local_competvet
 * @copyright 2023 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_competvet', get_string('pluginname', 'local_competvet'));

    // Comma-separated list of auth plugin shortnames.
    $settings->add(new admin_setting_configtext(
        'local_competvet/cascompatibleauthplugins',
        get_string('cascompatibleauthplugins', 'local_competvet'),
        get_string('cascompatibleauthplugins_help', 'local_competvet'),
        'cas'
    ));

    // Register the settings page.
    $ADMIN->add('localplugins', $settings);
}
