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
 * Specific login page for CAS.
 *
 * The return URL is the URL used in the application to call the competVetEval application
 *
 * @package   local_competvet
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// phpcs:disable moodle.Files.RequireLogin.Missing
use core\session\manager;
use local_competvet\utils;

require_once(__DIR__ . '/../../../config.php');
global $CFG, $SESSION, $USER;
require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/local/competvet/lib.php');

$authsenabled = get_enabled_auth_plugins();
$cascompatible = utils::get_cas_compatible_auth_plugins();

$idplist = [];
// The auth plugin's loginpage_hook() can eventually set $frm and/or $user.
$frm = false;
$user = false;
foreach ($authsenabled as $auth) {
    // Only include IdPs for auth plugins explicitly configured as CAS-compatible.
    if (!in_array($auth, $cascompatible, true)) {
        continue;
    }
    $authplugin = get_auth_plugin($auth);
    $authplugin->loginpage_hook();
}

$mobilelaunchparams = [];
$clock = \core\di::get(\core\clock::class);
if ($frm && isset($frm->username)) {                             // Login WITH cookies.
    $frm->username = trim(core_text::strtolower($frm->username));

    if (is_enabled_auth('none')) {
        if ($frm->username !== core_user::clean_field($frm->username, 'username')) {
            $errormsg = get_string('username') . ': ' . get_string("invalidusername");
            $errorcode = 2;
            $user = null;
        }
    }
    if (!$user) {
        $logintoken = isset($frm->logintoken) ? $frm->logintoken : '';
        $user = authenticate_user_login($frm->username, $frm->password, false, $errorcode, $logintoken);
    }
    if ($user) {
        global $DB;
        // Language setup.
        if (isguestuser($user)) {
            // No predefined language for guests - use existing session or default site lang.
            unset($user->lang);
        } else if (!empty($user->lang)) {
            // Unset previous session language - use user preference instead.
            unset($SESSION->lang);
        }

        if (empty($user->confirmed)) { // This account was never confirmed.
            global $PAGE, $OUTPUT;
            $PAGE->set_title(get_string("mustconfirm"));
            $PAGE->set_heading(get_site()->fullname);
            echo $OUTPUT->header();
            echo $OUTPUT->heading(get_string("mustconfirm"));
            echo $OUTPUT->box(get_string("emailconfirmsent", "", s($user->email)), "generalbox boxaligncenter");
            $resendconfirmurl = new moodle_url(
                '/login/index.php',
                [
                    'username' => $frm->username,
                    'password' => $frm->password,
                    'resendconfirmemail' => true,
                    'logintoken' => manager::get_login_token(),
                ]
            );
            echo $OUTPUT->single_button($resendconfirmurl, get_string('emailconfirmationresend'));
            echo $OUTPUT->footer();
            die;
        }

        // Let's get them all set up.
        complete_user_login($user);

        // The mobile flow expects this login to be treated as a fresh login for the
        // purpose of private token inclusion.
        if (empty($SESSION->justloggedin)) {
            $SESSION->justloggedin = true;
        }

        try {
            $timenow = $clock->time();
            // Check if the service exists and is enabled.
            $service = $DB->get_record('external_services', ['shortname' => utils::COMPETVET_MOBILE_SERVICE, 'enabled' => 1]);
            if (empty($service)) {
                // Will throw exception if no token found.
                throw new moodle_exception('servicenotavailable', 'webservice');
            }

            $token = utils::external_generate_token_for_current_user($service);
            external_log_token_request($token);

            $siteadmin = has_capability('moodle/site:config', context_system::instance(), $USER->id);
            $includeprivatetoken = !empty($SESSION->justloggedin) || ($token->timecreated >= $timenow);
            $apptoken = utils::build_mobile_app_apptoken($token, $siteadmin, is_https(), $includeprivatetoken);

            $mobilelaunchparams['token'] = $apptoken;
            $mobilelaunchparams['userid'] = $user->id;
        } catch (moodle_exception $e) {
            // Prevent partially bootstrapped sessions from forcing a logout/retry workaround.
            manager::init_empty_session();
            debugging('CAS to mobile token bootstrap failed: ' . $e->getMessage(), DEBUG_DEVELOPER);

            global $PAGE, $OUTPUT;
            $PAGE->set_title(get_string('cas-login-error', 'local_competvet'));
            $PAGE->set_heading(get_site()->fullname);
            echo $OUTPUT->header();
            echo $OUTPUT->heading(get_string('cas-login-error', 'local_competvet'));
            $boxcontent = get_string('cas-login-error-message', 'local_competvet', $e->errorcode ?? '');
            echo $OUTPUT->box($boxcontent, 'generalbox boxaligncenter');
            $retryurl = new moodle_url('/local/competvet/webservices/cas-login.php');
            echo $OUTPUT->single_button($retryurl, get_string('retry'));
            echo $OUTPUT->footer();
            die;
        }
    }
}

header('Location: ' . utils::get_application_launch_url($mobilelaunchparams));
