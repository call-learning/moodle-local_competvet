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

use context_system;
use stdClass;
use webservice;

/**
 * Class utils
 *
 * @package   local_competvet
 * @copyright 2023 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utils {
    /**
     * Application service name
     */
    const COMPETVET_MOBILE_SERVICE = 'competvet_app_service';

    /**
     * Get mobile services definition
     *
     * @param array $functions
     * @return array[]
     */
    public static function get_mobile_services_definition(array $functions): array {
        $cvemobilename = get_string('appservicename', 'local_competvet');
        $servicesfunctions = array_filter($functions, function($funct) {
            return in_array(self::COMPETVET_MOBILE_SERVICE, $funct['services']);
        });
        return [
            $cvemobilename => [
                'enabled' => 0,
                'requiredcapability' => 'local/competvet:mobileaccess',
                'component' => 'local_competvet',
                'shortname' => self::COMPETVET_MOBILE_SERVICE,
                'restrictedusers' => 0,
                'downloadfiles' => true,
                'uploadfiles' => false,
                'functions' => array_keys($servicesfunctions),
            ],
        ];
    }

    /**
     * Enable or disable mobile service and associated capabilities
     *
     * @param bool $enabled
     */
    public static function setup_mobile_service(bool $enabled) {
        global $CFG;
        require_once($CFG->dirroot . '/webservice/lib.php');
        require_once($CFG->dirroot . '/local/cveteval/lib.php');
        // Same routine as when we enable Moodle MOBILE APP Services.
        global $DB;
        if ($enabled) {
            // Similar code as in adminlib.php (admin_setting_enablemobileservice).
            set_config('enablewebservices', true);

            // Enable at least REST server.
            $activeprotocols = empty($CFG->webserviceprotocols) ? [] : explode(',', $CFG->webserviceprotocols);

            $updateprotocol = false;
            if (!in_array('rest', $activeprotocols)) {
                $activeprotocols[] = 'rest';
                $updateprotocol = true;
            }

            if ($updateprotocol) {
                set_config('webserviceprotocols', implode(',', $activeprotocols));
            }

            // Enable mobile service.
            static::get_or_create_mobile_service(true);

            // Allow rest:use capability for authenticated user.
            static::set_protocol_cap(true);
        } else {
            // Disable web service system if no other services are enabled.
            static::get_or_create_mobile_service(); // Make sure service is created but disabled.
            $otherenabledservices = $DB->get_records_select(
                'external_services',
                'enabled = :enabled AND (shortname != :shortname OR shortname IS NULL)',
                [
                    'enabled' => 1,
                    'shortname' => self::COMPETVET_MOBILE_SERVICE,
                ]
            );
            if (empty($otherenabledservices)) {
                set_config('enablewebservices', false);

                // Also disable REST server.
                $activeprotocols = empty($CFG->webserviceprotocols) ? [] : explode(',', $CFG->webserviceprotocols);

                $protocolkey = array_search('rest', $activeprotocols);
                if ($protocolkey !== false) {
                    unset($activeprotocols[$protocolkey]);
                    $updateprotocol = true;
                }

                if ($updateprotocol) {
                    set_config('webserviceprotocols', implode(',', $activeprotocols));
                }

                // Disallow rest:use capability for authenticated user.
                static::set_protocol_cap(false);
            }

        }
        require_once($CFG->dirroot . '/lib/upgradelib.php');
        external_update_descriptions('local_competvet');
    }

    /**
     * Get or create mobile service
     *
     * @param bool $isenabled
     * @return stdClass
     */
    public static function get_or_create_mobile_service($isenabled = false): stdClass {
        global $CFG;
        require_once($CFG->dirroot . '/webservice/lib.php');
        require_once($CFG->dirroot . '/local/cveteval/lib.php');

        $webservicemanager = new webservice();
        $mobileservice = $webservicemanager->get_external_service_by_shortname(self::COMPETVET_MOBILE_SERVICE);
        if (!$mobileservice) {
            // Create it.
            // Load service info.
            require_once($CFG->dirroot . '/lib/upgradelib.php');
            external_update_descriptions('local_competvet');
            $mobileservice = $webservicemanager->get_external_service_by_shortname(self::COMPETVET_MOBILE_SERVICE);
        }
        $mobileservice->enabled = $isenabled;
        $webservicemanager->update_external_service($mobileservice);

        return $mobileservice;
    }

    /**
     * This is a replica of the admin settings for mobile application
     *
     * Set the 'webservice/rest:use' to the Authenticated user role (allow or not)
     *
     * @param bool $status true to allow, false to not set
     */
    private static function set_protocol_cap(bool $status) {
        global $CFG, $DB;
        $roleid = $CFG->defaultuserroleid ?? $DB->get_field('role', 'id', ['shortname' => 'user']);
        if ($roleid) {
            $params = [];
            $params['permission'] = CAP_ALLOW;
            $params['roleid'] = $roleid;
            $params['capability'] = 'webservice/rest:use';
            $protocolcapallowed = $DB->record_exists('role_capabilities', $params);
            if ($status && !$protocolcapallowed) {
                // Need to allow the cap.
                $permission = CAP_ALLOW;
                $assign = true;
            } else if (!$status && $protocolcapallowed) {
                // Need to disallow the cap.
                $permission = CAP_INHERIT;
                $assign = true;
            }
            if (!empty($assign)) {
                $systemcontext = context_system::instance();
                assign_capability('webservice/rest:use', $permission, $roleid, $systemcontext->id, true);
            }
        }
    }
}
