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
 * Version details.
 *
 * @package   local_competvet
 * @copyright 2023 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version = 2024060607;      // The current module version (Date: YYYYMMDDXX).
$plugin->requires = 2020061501;      // Requires this Moodle version (3.9.1).
$plugin->maturity = MATURITY_RC;
$plugin->release = '0.5.0'; // New API.
$plugin->component = 'local_competvet';// Full name of the plugin (used for diagnostics).
$plugin->cron = 0;
$plugin->dependencies = [
    'mod_competvet' => ANY_VERSION,
];
