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
// @phpcs:disable moodle.Files.RequireLogin.Missing
// @phpcs:disable moodle.Files.MoodleInternal.MoodleInternalGlobalState
define('AJAX_SCRIPT', true);
define('REQUIRE_CORRECT_ACCESS', true);
define('NO_MOODLE_COOKIES', true);
define('NO_UPGRADE_CHECK', true);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
use core\session\manager;
use local_competvet\utils;

require_once(__DIR__ . '/../../../config.php');
global $CFG, $USER, $DB;
require_once($CFG->libdir . '/externallib.php');

// Allow CORS requests.

header('Content-Type: application/json; charset=utf-8');

/**
 * Return a JSON error payload for token authentication failures.
 *
 * @param string $errorcode
 * @param string $message
 * @param array $extra
 * @return stdClass
 */
function local_competvet_token_error_response(string $errorcode, string $message, array $extra = []): stdClass {
    $response = new stdClass();
    $response->errorcode = $errorcode;
    $response->message = $message;
    foreach ($extra as $key => $value) {
        $response->{$key} = $value;
    }

    return $response;
}

/**
 * Return the current code release in a human-readable format.
 *
 * @return string|null
 */
function local_competvet_token_get_target_release(): ?string {
    global $CFG;

    if (!empty($CFG->target_release)) {
        return $CFG->target_release;
    }

    if (!empty($CFG->release)) {
        return $CFG->release;
    }

    $version = null;
    $release = null;
    $branch = null;
    $maturity = null;
    require($CFG->dirroot . '/version.php');

    return $release ?: null;
}

/**
 * Return a JSON error payload for an ongoing Moodle upgrade.
 *
 * @return stdClass
 */
function local_competvet_token_upgrade_running_response(): stdClass {
    $release = local_competvet_token_get_target_release();
    $message = get_string('upgraderunning', 'error');

    if (!empty($release)) {
        $message = get_string('tokenupgraderunningwithrelease', 'local_competvet', $release);
    }

    return local_competvet_token_error_response('upgraderunning', $message, [
        'release' => $release,
    ]);
}

/**
 * Map Moodle auth failure reasons to a stable code and a readable message.
 *
 * @param int|null $reason
 * @return stdClass
 */
function local_competvet_token_login_error_response(?int $reason): stdClass {
    switch ($reason) {
        case AUTH_LOGIN_FAILED:
            return local_competvet_token_error_response('invalidlogin', get_string('invalidlogin'));
        case AUTH_LOGIN_NOUSER:
            return local_competvet_token_error_response('usernotexist', get_string('invalidlogin'));
        case AUTH_LOGIN_SUSPENDED:
            return local_competvet_token_error_response('usersuspended', get_string('suspended', 'auth'));
        case AUTH_LOGIN_LOCKOUT:
            return local_competvet_token_error_response('userlockedout', get_string('accountlocked', 'admin'));
        case AUTH_LOGIN_UNAUTHORISED:
            return local_competvet_token_error_response('userunauthorised', get_string('usernotallowed', 'webservice'));
        case AUTH_LOGIN_FAILED_RECAPTCHA:
            return local_competvet_token_error_response('loginrecaptchafailed', get_string('missingrecaptchachallengefield'));
        default:
            return local_competvet_token_error_response('unknownerror', get_string('unknownerror', 'moodle'));
    }
}


try {
    if (!empty($CFG->upgraderunning)) {
        if ((int)$CFG->upgraderunning < time()) {
            unset_config('upgraderunning');
        } else {
            echo json_encode(local_competvet_token_upgrade_running_response());
            exit;
        }
    }

    if (!$CFG->enablewebservices) {
        throw new moodle_exception('enablewsdescription', 'webservice');
    }

    // This script is used by the mobile app to check that the site is available and web services
    // are allowed. In this mode, no further action is needed.
    if (optional_param('appsitecheck', 0, PARAM_INT)) {
        echo json_encode((object) ['appsitecheck' => 'ok']);
        exit;
    }

    $username = required_param('username', PARAM_USERNAME);
    $password = required_param('password', PARAM_RAW);
    $serviceshortname = required_param('service', PARAM_ALPHANUMEXT);

    $username = trim(core_text::strtolower($username));
    if (is_restored_user($username)) {
        throw new moodle_exception('restoredaccountresetpassword', 'webservice');
    }

    $systemcontext = context_system::instance();
    $reason = null;
    $returnedvalue = new stdClass();

    $user = authenticate_user_login($username, $password, false, $reason);
    if (!empty($user)) {
        // Cannot authenticate unless maintenance access is granted.
        $hasmaintenanceaccess = has_capability('moodle/site:maintenanceaccess', $systemcontext, $user);
        if (!empty($CFG->maintenance_enabled) && !$hasmaintenanceaccess) {
            throw new moodle_exception('sitemaintenance', 'admin');
        }

        if (isguestuser($user)) {
            throw new moodle_exception('noguest');
        }
        if (empty($user->confirmed)) {
            throw new moodle_exception('usernotconfirmed', 'moodle', '', $user->username);
        }
        // Check credential expiry.
        $userauth = get_auth_plugin($user->auth);
        if (!empty($userauth->config->expiration) && $userauth->config->expiration == 1) {
            $days2expire = $userauth->password_expire($user->username);
            if (intval($days2expire) < 0) {
                throw new moodle_exception('passwordisexpired', 'webservice');
            }
        }

        // Let enrol plugins deal with new enrolments if necessary.
        enrol_check_plugins($user);

        // Setup user session to check capability.
        manager::set_user($user);

        // Check if the service exists and is enabled.
        $service = $DB->get_record('external_services', ['shortname' => $serviceshortname, 'enabled' => 1]);
        if (empty($service)) {
            // Will throw exception if no token found.
            throw new moodle_exception('servicenotavailable', 'webservice');
        }

        // Get an existing token or create a new one.
        $token = utils::external_generate_token_for_current_user($service);
        $privatetoken = $token->privatetoken;
        external_log_token_request($token);

        $siteadmin = has_capability('moodle/site:config', $systemcontext, $USER->id);

        $returnedvalue->token = $token->token;
        // Private token, only transmitted to https sites and non-admin users.
        if (is_https() && !$siteadmin) {
            $returnedvalue->privatetoken = $privatetoken;
        } else {
            $returnedvalue->privatetoken = null;
        }
    } else {
        $returnedvalue = local_competvet_token_login_error_response($reason);
    }
} catch (moodle_exception $e) {
    $returnedvalue = local_competvet_token_error_response($e->errorcode, $e->getMessage());
} catch (Throwable $e) {
    $returnedvalue = local_competvet_token_error_response('unknownerror', $e->getMessage());
}
$returnedvalue->userid = $USER->id;
echo json_encode($returnedvalue);
