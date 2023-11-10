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
require_once($CFG->dirroot . '/mod/competvet/tests/test_data_definition.php');
use external_api;
use externallib_advanced_testcase;
use test_data_definition;

/**
 * Get My situations tests
 *
 * @package     local_competvet
 * @copyright   2023 CALL Learning <contact@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_my_situations_test extends externallib_advanced_testcase {
    use test_data_definition;
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
        $competvetgenerator = $generator->get_plugin_generator('mod_competvet');
        $this->generates_definition($this->get_data_definition_set_1(), $generator, $competvetgenerator);
    }

    /**
     * Test with non existing user.
     *
     * @covers \local_competvet\external\user_profile
     *
     */
    public function test_user_not_logged_in() {
        $this->expectExceptionMessageMatches('/You are not logged in/');
        $this->get_my_situations();
    }

    /**
     * Helper
     *
     * @param mixed ...$params
     * @return mixed
     */
    protected function get_my_situations(...$params) {
        $returnvalue = get_my_situations::execute(...$params);
        return external_api::clean_returnvalue(get_my_situations::execute_returns(), $returnvalue);
    }

    /**
     * Test with existing user
     *
     * @covers \local_competvet\external\user_profile
     *
     */
    public function test_get_my_situations() {
        $this->setUser(\core_user::get_user_by_username('student1'));
        $situations = $this->get_my_situations();
    }
}
