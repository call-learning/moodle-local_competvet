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
 * Web services auto-generated documentation
 *
 * @package    core_webservice
 * @copyright  2009 Jerome Mouneyrac <jerome@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_competvet\utils;

require_once('../../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;
require_once($CFG->dirroot . '/webservice/lib.php');
require_login();
$service = $DB->get_record('external_services', ['shortname' => utils::COMPETVET_MOBILE_SERVICE, 'enabled' => 1]);
if (empty($service)) {
    // Will throw exception if no token found.
    throw new moodle_exception('servicenotavailable', 'webservice');
}

// Get an existing token or create a new one.
$token = utils::external_generate_token_for_current_user($service);
if ($token) {
    redirect(new moodle_url('/webservice/wsdoc.php', ['id' => $token->id]));
}
$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/competvet/webservices/docs.php'));
echo $OUTPUT->header();

echo $OUTPUT->notification(get_string('notoken', 'local_competvet'));

echo $OUTPUT->footer();