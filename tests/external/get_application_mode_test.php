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

use context_module;
use external_api;
use externallib_advanced_testcase;
use mod_competvet\competvet;

/**
 * Application mode test
 *
 * @package     local_competvet
 * @copyright   2023 CALL Learning <contact@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_application_mode_test extends externallib_advanced_testcase {
    /**
     * @var $users array
     */
    protected $users = [];

    public static function enrolment_data_provider() {
        return [
            'student in course' => [
                'definition' => [
                    'course 1' => [
                        'roles' => ['student'],
                        'activities' => ['SIT1' => null, 'SIT2' => null, 'SIT3' => null],
                    ],
                    'course 2' => [
                        'roles' => ['student'],
                        'activities' => ['SIT4' => null, 'SIT5' => null, 'SIT6' => null],
                    ],
                ],
                'expected' => 'student',
            ],
            'observer and assessor in course' => [
                'definition' => [
                    'course 1' => [
                        'roles' => ['observer'],
                        'activities' => ['SIT1' => null, 'SIT2' => null, 'SIT3' => null],
                    ],
                    'course 2' => [
                        'roles' => ['assessor'],
                        'activities' => ['SIT4' => null, 'SIT5' => null, 'SIT6' => null],
                    ],
                ],
                'expected' => 'observer',
            ],
            'no competvet roles' => [
                'definition' => [
                    'course 1' => [
                        'roles' => ['teacher'],
                        'activities' => ['SIT1' => null, 'SIT2' => null, 'SIT3' => null],
                    ],
                    'course 2' => [
                        'roles' => ['teacher'],
                        'activities' => ['SIT4' => null, 'SIT5' => null, 'SIT6' => null],
                    ],
                ],
                'expected' => 'unknown',
            ],
            'no roles' => [
                'definition' => [
                    'course 1' => [
                        'roles' => [],
                        'activities' => ['SIT1' => null, 'SIT2' => null, 'SIT3' => null],
                    ],
                    'course 2' => [
                        'roles' => [],
                        'activities' => ['SIT4' => null, 'SIT5' => null, 'SIT6' => null],
                    ],
                ],
                'expected' => 'unknown',
            ],
            'conflicting roles' => [
                'definition' => [
                    'course 1' => [
                        'roles' => ['observer'],
                        'activities' => ['SIT1' => null, 'SIT2' => null, 'SIT3' => null],
                    ],
                    'course 2' => [
                        'roles' => ['student'],
                        'activities' => ['SIT4' => null, 'SIT5' => null, 'SIT6' => null],
                    ],
                ],
                'expected' => 'unknown',
            ],
            'conflicting roles in local role assignments' => [
                'definition' => [
                    'course 1' => [
                        'roles' => ['student'],
                        'activities' => ['SIT1' => null, 'SIT2' => null, 'SIT3' => null],
                    ],
                    'course 2' => [
                        'roles' => ['student'],
                        'activities' => ['SIT4' => 'observer', 'SIT5' => null, 'SIT6' => null],
                    ],
                ],
                'expected' => 'unknown',
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
        $this->users = [];
        for ($i = 0; $i < 2; $i++) {
            $user = $generator->create_user();
            $this->users[$user->id] = $user;
        }
    }

    /**
     * Test with non-existing user.
     *
     * @covers \local_competvet\external\user_type::execute
     */
    public function test_user_type_not_exist_test() {
        $this->setAdminUser();
        $this->expectExceptionMessageMatches('/core_user\/invaliduserid/');
        $this->get_application_mode(9999);
    }

    /**
     * Helper
     *
     * @param mixed ...$params
     * @return mixed
     */
    protected function get_application_mode(...$params) {
        $returnvalue = get_application_mode::execute(...$params);
        return external_api::clean_returnvalue(get_application_mode::execute_returns(), $returnvalue);
    }

    /**
     * Test with different roles in courses
     *
     * @covers       \local_competvet\external\user_type::execute
     * @dataProvider enrolment_data_provider
     */
    public function test_type_with_enrolments($definition, $expected) {
        $this->setAdminUser();
        $userid = $this->setup_course_and_user_from_data($definition);
        $this->assertEquals($expected, $this->get_application_mode($userid)['type']);
    }

    /**
     * Setup courses and enrolment according to defintion.
     *
     * @param array $definition
     * @return int user id
     */
    private function setup_course_and_user_from_data(array $definition): int {
        global $DB;
        $generator = $this->getDataGenerator();
        $user = $generator->create_user();
        foreach ($definition as $coursename => $data) {
            $course = $generator->create_course(['shortname' => $coursename]);
            foreach ($data['roles'] as $rolename) {
                $generator->enrol_user($user->id, $course->id, $rolename);
            }
            foreach ($data['activities'] as $situationname => $roleoverride) {
                $module = $generator->create_module('competvet', ['course' => $course->id, 'shortname' => $situationname]);
                if (!empty($roleoverride)) {
                    $roleid = $DB->get_field('role', 'id', ['shortname' => $roleoverride]);
                    [$course, $cm] = get_course_and_cm_from_instance($module->id, competvet::MODULE_NAME);
                    role_assign($roleid, $user->id, context_module::instance($cm->id));
                }
            }
        }
        return $user->id;
    }
}
