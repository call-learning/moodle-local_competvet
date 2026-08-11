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
/**
 * Utils tests
 *
 * @package     local_competvet
 * @copyright   2023 CALL Learning <contact@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class utils_test extends \advanced_testcase {
    /**
     * Test get_mobile_services_definition
     *
     * @covers \local_competvet\utils
     **/
    public function test_get_mobile_services_definition(): void {
        $functions = [
            'test_function' => ['services' => ['competvet_app_service']],
        ];
        $result = utils::get_mobile_services_definition($functions);
        $this->assertArrayHasKey('CompetVet Mobile Service', $result);
    }

    /**
     * Test setup_mobile_service
     *
     * @covers \local_competvet\utils
     */
    public function test_setup_mobile_service(): void {
        global $CFG;
        $this->resetAfterTest(true);
        utils::setup_mobile_service(true);
        $this->assertEquals('1', get_config('core', 'enablewebservices'));
    }

    /**
     * Test get_or_create_mobile_service
     *
     * @covers \local_competvet\utils
     */
    public function test_get_or_create_mobile_service(): void {
        $this->resetAfterTest(true);
        $service = utils::get_or_create_mobile_service(true);
        $this->assertEquals('competvet_app_service', $service->shortname);
        $this->assertEquals(1, $service->enabled);
    }

    /**
     * Test external_generate_token_for_current_user
     *
     * @covers \local_competvet\utils
     */
    public function test_external_generate_token_for_current_user(): void {
        global $USER;
        $this->resetAfterTest(true);
        $USER = $this->getDataGenerator()->create_user();
        $service = (object) [
            'id' => 1,
            'name' => 'CompetVet Mobile Service',
            'shortname' => 'competvet_app_service',
            'requiredcapability' => '',
            'restrictedusers' => 0,
        ];
        $this->setUser($USER);
        $token = utils::external_generate_token_for_current_user($service);
        $this->assertNotEmpty($token->token);
    }

    /**
     * Test get_application_launch_url
     *
     * @covers \local_competvet\utils
     */
    public function test_get_application_launch_url(): void {
        $this->resetAfterTest(true);
        $params = ['param1' => 'value1'];
        $url = utils::get_application_launch_url($params);
        $this->assertStringContainsString('fr.calllearning.competvet://', $url);
        $this->assertStringContainsString('param1=value1', $url);
    }
    /**
     * Test get idp list with idp
     *
     * @covers \local_competvet\utils::get_idp_list
     * @runInSeparateProcess
     */
    public function test_get_idp_list_with_idp(): void {
        global $CFG;
        if (!file_exists($CFG->dirroot . '/auth/cas/auth.php')) {
            $this->markTestSkipped('auth_cas plugin is not available');
        }
        $this->resetAfterTest(true);

        // Native Moodle CAS should be treated as CAS-compatible by default,
        // but we set it explicitly to make the test independent from config defaults.
        set_config('cascompatibleauthplugins', 'cas', 'local_competvet');

        $CFG->auth = 'manual,cas';
        set_config('hostname', $CFG->wwwroot, 'auth_cas');
        set_config('auth_logo', '', 'auth_cas');
        set_config('auth_name', 'Test CAS', 'auth_cas');
        get_enabled_auth_plugins(true); // Enable cas.
        $idplist = utils::get_idp_list();
        $this->assertEquals(
            [
                'url' => 'https://www.example.com/moodle/local/competvet/webservices/cas-login.php?authCAS=CAS',
                'iconurl' => '',
                'name' => 'Test CAS',
                'id' => 'cas-0',
            ],
            $idplist[0],
        );
    }

    /**
     * Test get idp list without idp
     *
     * @covers \local_competvet\external\utils
     * @runInSeparateProcess
     */
    public function test_get_idp_list_without_idp(): void {
        set_config('cascompatibleauthplugins', 'cas', 'local_competvet');
        $idplist = utils::get_idp_list();
        $this->assertEmpty($idplist);
    }

    /**
     * Test that enabled CAS plugin entries are ignored when CAS-compatible configuration
     * does not include the enabled plugin shortname.
     *
     * @covers \local_competvet\utils::get_idp_list
     * @runInSeparateProcess
     */
    public function test_get_idp_list_when_plugin_not_in_configuration_returns_empty(): void {
        global $CFG;
        if (!file_exists($CFG->dirroot . '/auth/cas/auth.php')) {
            $this->markTestSkipped('auth_cas plugin is not available');
        }

        $this->resetAfterTest(true);
        set_config('cascompatibleauthplugins', 'casattras', 'local_competvet');

        $CFG->auth = 'manual,cas';
        set_config('hostname', $CFG->wwwroot, 'auth_cas');
        set_config('auth_logo', '', 'auth_cas');
        set_config('auth_name', 'Test CAS', 'auth_cas');

        get_enabled_auth_plugins(true); // Enable cas.

        $idplist = utils::get_idp_list();
        $this->assertEmpty($idplist);
    }

    /**
     * Test external auth idp_list with idp
     *
     * @covers \local_competvet\external\auth::idp_list
     * @runInSeparateProcess
     */
    public function test_external_auth_idp_list_with_idp(): void {
        global $CFG;
        if (!file_exists($CFG->dirroot . '/auth/cas/auth.php')) {
            $this->markTestSkipped('auth_cas plugin is not available');
        }

        $this->resetAfterTest(true);

        set_config('cascompatibleauthplugins', 'cas', 'local_competvet');

        $CFG->auth = 'manual,cas';
        set_config('hostname', $CFG->wwwroot, 'auth_cas');
        set_config('auth_logo', '', 'auth_cas');
        set_config('auth_name', 'Test CAS', 'auth_cas');

        get_enabled_auth_plugins(true); // Enable cas.

        $idplist = \local_competvet\external\auth::idp_list();
        $this->assertEquals(
            [
                'url' => 'https://www.example.com/moodle/local/competvet/webservices/cas-login.php?authCAS=CAS',
                'iconurl' => '',
                'name' => 'Test CAS',
                'id' => 'cas-0',
            ],
            $idplist[0],
        );
    }

    /**
     * Test external auth idp_list without idp
     *
     * @covers \local_competvet\external\auth::idp_list
     * @runInSeparateProcess
     */
    public function test_external_auth_idp_list_without_idp(): void {
        global $CFG;

        // Ensure CAS is not enabled.
        $this->resetAfterTest(true);
        set_config('cascompatibleauthplugins', 'cas', 'local_competvet');
        $CFG->auth = 'manual';

        $idplist = \local_competvet\external\auth::idp_list();
        $this->assertEmpty($idplist);
    }

    /**
     * Test the mobile app apptoken includes the private token only when requested.
     *
     * @covers \local_competvet\utils::build_mobile_app_apptoken
     */
    public function test_build_mobile_app_apptoken_private_token_included_when_requested(): void {
        global $CFG;
        $this->resetAfterTest(true);

        $CFG->wwwroot = 'https://www.example.com/';

        $token = (object) [
            'token' => 'mobiletoken',
            'privatetoken' => 'privatetoken',
            'timecreated' => time(),
        ];

        $apptoken = utils::build_mobile_app_apptoken($token, false, true, true);
        $decoded = base64_decode($apptoken);

        $this->assertStringContainsString('mobiletoken', $decoded);
        $this->assertStringContainsString('privatetoken', $decoded);
    }

    /**
     * Test the mobile app apptoken omits the private token when not requested.
     *
     * @covers \local_competvet\utils::build_mobile_app_apptoken
     */
    public function test_build_mobile_app_apptoken_private_token_omitted_when_not_requested(): void {
        global $CFG;
        $this->resetAfterTest(true);

        $CFG->wwwroot = 'https://www.example.com/';

        $token = (object) [
            'token' => 'mobiletoken',
            'privatetoken' => 'privatetoken',
            'timecreated' => time(),
        ];

        $apptoken = utils::build_mobile_app_apptoken($token, false, true, false);
        $decoded = base64_decode($apptoken);

        $this->assertStringContainsString('mobiletoken', $decoded);
        $this->assertStringNotContainsString('privatetoken', $decoded);
    }

    /**
     * Cover the first-login handoff behavior that should include the private token.
     *
     * @covers \local_competvet\utils::external_generate_token_for_current_user
     * @covers \local_competvet\utils::build_mobile_app_apptoken
     */
    public function test_cas_first_login_apptoken_includes_private_token_for_new_user(): void {
        global $USER;
        $this->resetAfterTest(true);

        $USER = $this->getDataGenerator()->create_user();
        $this->setUser($USER);

        $service = (object) [
            'id' => 1,
            'name' => 'CompetVet Mobile Service',
            'shortname' => 'competvet_app_service',
            'requiredcapability' => '',
            'restrictedusers' => 0,
        ];

        $token = utils::external_generate_token_for_current_user($service);
        $apptoken = utils::build_mobile_app_apptoken($token, false, true, true);
        $decoded = base64_decode($apptoken);

        $this->assertStringContainsString($token->privatetoken, $decoded);
    }

    /**
     * Cover the returning-user case: with the first-login fix, the private token should
     * still be included when the mobile flow treats the login as fresh.
     *
     * @covers \local_competvet\utils::external_generate_token_for_current_user
     * @covers \local_competvet\utils::build_mobile_app_apptoken
     */
    public function test_cas_first_login_apptoken_includes_private_token_for_returning_user(): void {
        global $USER;
        $this->resetAfterTest(true);

        $USER = $this->getDataGenerator()->create_user();
        $this->setUser($USER);

        $service = (object) [
            'id' => 1,
            'name' => 'CompetVet Mobile Service',
            'shortname' => 'competvet_app_service',
            'requiredcapability' => '',
            'restrictedusers' => 0,
        ];

        $token1 = utils::external_generate_token_for_current_user($service);
        $token2 = utils::external_generate_token_for_current_user($service);

        // Ensure token still has a private token.
        $this->assertNotEmpty($token2->privatetoken);

        $apptoken = utils::build_mobile_app_apptoken($token2, false, true, true);
        $decoded = base64_decode($apptoken);

        $this->assertStringContainsString($token2->privatetoken, $decoded);
    }
}
