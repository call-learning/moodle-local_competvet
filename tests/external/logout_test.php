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

use core_external\external_api;

/**
 * User info tests
 *
 * @package     local_competvet
 * @copyright   2023 CALL Learning <contact@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class logout_test extends \advanced_testcase {
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
     * Test with non logged in user.
     *
     * @covers \local_competvet\external\logout
     * @runInSeparateProcess
     */
    public function test_logout_no_login(): void {
        $this->assertFalse(isloggedin());
        $return = $this->logout([]);
        $this->assertEmpty($return);
        $this->assertFalse(isloggedin());
    }

    /**
     * Test with non logged in user.
     *
     * @covers \local_competvet\external\logout
     * @runInSeparateProcess
     */
    public function test_logout_loggedin(): void {
        $this->setUser($this->users[array_key_first($this->users)]);
        $this->assertTrue(isloggedin());
        $return = $this->logout([]);
        $this->assertEmpty($return);
        $this->assertFalse(isloggedin());
    }

    /**
     * Helper
     *
     * @param array $args
     * @return mixed
     */
    protected function logout(array $args) {
        $validate = [\local_competvet\external\logout::class, 'validate_parameters'];
        $params = call_user_func(
            $validate,
            \local_competvet\external\logout::execute_parameters(),
            $args
        );
        $params = array_values($params);
        $returnvalue = \local_competvet\external\logout::execute(...$params);
        return external_api::clean_returnvalue(\local_competvet\external\logout::execute_returns(), $returnvalue);
    }
}
