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
 * Return token or information ref. SSO
 *
 * @package    local_competvet
 *
 * Inspired from the login/token.php file and modified
 * according to our needs:
 *  - the competveteval application can create tokens
 * @copyright  2011 Dongsheng Cai <dongsheng@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);
// Services can declare 'readonlysession' in their config located in db/services.php, if not present will default to false.
define('READ_ONLY_SESSION', true);
if (!empty($_GET['nosessionupdate'])) {
    define('NO_SESSION_UPDATE', true);
}
header('Access-Control-Allow-Origin: *');
require_once('../../../config.php');
define('PREFERRED_RENDERER_TARGET', RENDERER_TARGET_GENERAL);
use local_competvet\utils;
global $PAGE;
$PAGE->set_context(context_system::instance());
echo json_encode(utils::get_idp_list());
