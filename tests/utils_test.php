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
     * @covers \local_competvet\external\utils
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
     * @covers \local_competvet\external\utils
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
     * @covers \local_competvet\external\utils
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
     * @covers \local_competvet\external\utils
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
            'restrictedusers' => 0
        ];
        $this->setUser($USER);
        $token = utils::external_generate_token_for_current_user($service);
        $this->assertNotEmpty($token->token);
    }

    /**
     * Test get_application_launch_url
     *
     * @covers \local_competvet\external\utils
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
     * @covers \local_competvet\external\utils
     * @runInSeparateProcess
     */
    public function test_get_idp_list_with_idp(): void {
        global $CFG;
        $this->resetAfterTest(true);
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
                'id' => 'cas-0'
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
        $idplist = utils::get_idp_list();
        $this->assertEmpty($idplist);
    }
}
