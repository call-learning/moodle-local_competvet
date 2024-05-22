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
namespace local_competvet\external;
defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');

use external_api;
use externallib_advanced_testcase;

/**
 * User info tests
 *
 * @package     local_competvet
 * @copyright   2023 CALL Learning <contact@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_info_test extends externallib_advanced_testcase {
    /**
     * @var $users array
     */
    protected $users = [];

    /**
     * As we have a test that does write into the DB, we need to setup and tear down each time
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $this->users = [];
        for ($i = 0; $i < 2; $i++) {
            $user = $generator->create_user();
            $this->users[$user->id] = $user;
        }
    }

    /**
     * Test with non existing user.
     *
     * @covers \local_competvet\external\user_info
     *
     */
    public function test_user_profile_not_exist_test() {
        $this->setAdminUser();
        $this->expectExceptionMessageMatches('/core_user\/invaliduserid/');
        $this->get_user_profile(['userid' => 9999]);
    }

    /**
     * Helper
     *
     * @param array $args
     * @return mixed
     */
    protected function get_user_profile(array $args) {
        $validate = [user_info::class, 'validate_parameters'];
        $params = call_user_func(
            $validate,
            user_info::execute_parameters(),
            $args
        );
        $params = array_values($params);
        $returnvalue = user_info::execute(...$params);
        return external_api::clean_returnvalue(user_info::execute_returns(), $returnvalue);
    }

    /**
     * Test with existing user
     *
     * @covers \local_competvet\external\user_info
     *
     */
    public function test_user_profile_existing_test() {
        $this->setAdminUser();
        $firstuser = end($this->users);
        $user = $this->get_user_profile(['userid' => $firstuser->id]);
        foreach ($user as $key => $value) {
            switch ($key) {
                case 'userid':
                    $this->assertEquals($firstuser->id, $value);
                    break;
                case 'fullname':
                    $this->assertEquals(fullname($firstuser), $value);
                    break;
                case 'userpictureurl':
                    $this->assertNotEmpty($value);
                    break;
                default:
                    $this->assertEquals($firstuser->$key, $value);
            }
        }
    }
}
