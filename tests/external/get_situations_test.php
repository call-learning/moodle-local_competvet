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
require_once($CFG->dirroot . '/mod/competvet/tests/test_data_definition.php');

use DateTime;
use external_api;
use mod_competvet\tests\test_helpers;
use test_data_definition;

/**
 * Get My situations tests
 *
 * @package     local_competvet
 * @copyright   2023 CALL Learning <contact@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_situations_test extends \advanced_testcase {
    use test_data_definition;

    /**
     * @var $users array
     */
    protected $users = [];

    /**
     * All for user provider with planning
     *
     * @return array[]
     */
    public static function all_for_user_provider_with_planning(): array {
        global $CFG;
        $results = [];
        $startdate = (new DateTime('last Monday'))->getTimestamp();
        include_once($CFG->dirroot . '/local/competvet/tests/fixtures/get_situations_results.php');
        return [
            'student1 situations' => [
                'student1',
                true,
                $results['student1results'],
            ],
            'student2 situations' => [
                'student2',
                false, // Show future sessions.
                $results['student2results'],
            ],
            'observer1 situations' => [
                'observer1',
                true,
                $results['observer1results'],
            ],
            'observer2 situations' => [
                'observer2',
                false, // Show future planning.
                $results['observer2results'],
            ],
            'teacher1 situations' => [
                'teacher1',
                true,
                $results['teacher1results'],
            ],
            'observer and teacher situations' => [
                'observerandteacher',
                false,
                $results['observerandteacherresults'],
            ],
        ];
    }

    /**
     * As we have a test that does write into the DB, we need to setup and tear down each time
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $competvetgenerator = $generator->get_plugin_generator('mod_competvet');
        $startdate = new DateTime('last Monday');
        $this->generates_definition($this->get_data_definition_set_1($startdate->getTimestamp()), $generator, $competvetgenerator);
    }

    /**
     * Test with non existing user.
     *
     * @covers \local_competvet\external\user_profile
     * @runInSeparateProcess
     */
    public function test_user_not_logged_in() {
        $this->expectExceptionMessageMatches('/You are not logged in/');
        $this->get_situations();
    }

    /**
     * Helper
     *
     * @param mixed ...$params
     * @return mixed
     */
    protected function get_situations(...$params) {
        $returnvalue = get_situations::execute(...$params);
        return external_api::clean_returnvalue(get_situations::execute_returns(), $returnvalue);
    }

    /**
     * Test with existing user
     *
     * @params string $username
     * @params bool $nofutureplanning
     * @params array $expected
     *
     * @covers       \local_competvet\external\user_profile
     * @dataProvider all_for_user_provider_with_planning
     * @runInSeparateProcess
     */
    public function test_get_situations(string $username, bool $nofutureplanning, array $expected) {
        $this->setUser(\core_user::get_user_by_username($username));
        $situations = $this->get_situations(null, $nofutureplanning); // Ignore future situations.
        test_helpers::remove_elements_for_assertions($situations, ['id', 'intro', 'translatedcategory']);
        $this->assertEquals($expected, $situations);
    }
}
