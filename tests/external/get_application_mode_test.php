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
use context_module;
use core_user;
use external_api;
use mod_competvet\competvet;
use mod_competvet\local\persistent\planning;

/**
 * Application mode test
 *
 * @package     local_competvet
 * @copyright   2023 CALL Learning <contact@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_application_mode_test extends \advanced_testcase {
    /**
     * @var $users array
     */
    protected $users = [];

    /**
     * Data provider for roles tests.
     *
     * @return array[]
     */
    public static function enrolment_data_provider(): array {
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
            'observer and evaluator in course' => [
                'definition' => [
                    'course 1' => [
                        'roles' => ['observer'],
                        'activities' => ['SIT1' => null, 'SIT2' => null, 'SIT3' => null],
                    ],
                    'course 2' => [
                        'roles' => ['evaluator'],
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
            'teacher or no role but observer' => [
                'definition' => [
                    'course 1' => [
                        'roles' => ['observer'],
                        'activities' => ['SIT1' => null, 'SIT2' => null, 'SIT3' => null],
                    ],
                    'course 2' => [
                        'roles' => [],
                        'activities' => ['SIT4' => null],
                    ],
                    'course 3' => [
                        'roles' => ['teacher'],
                        'activities' => ['SIT5' => 'observer', 'SIT6' => null, 'SIT7' => null],
                    ],
                    'course 4' => [
                        'roles' => ['manager'],
                        'activities' => ['SIT8' => null],
                    ],
                ],
                'expected' => 'observer',
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
     * @runInSeparateProcess
     */
    public function test_user_type_not_exist_test() {
        $this->setAdminUser();
        $result = $this->get_application_mode(['userid' => 9999]);
        $this->assertEquals('invaliduserid', $result['warnings'][0]['warningcode']);
    }

    /**
     * Helper
     *
     * @param array $args
     * @return mixed
     */
    protected function get_application_mode($args) {
        $validate = [get_application_mode::class, 'validate_parameters'];
        $params = call_user_func(
            $validate,
            get_application_mode::execute_parameters(),
            $args
        );
        $params = array_values($params);
        $returnvalue = get_application_mode::execute(...$params);
        return external_api::clean_returnvalue(get_application_mode::execute_returns(), $returnvalue);
    }

    /**
     * Test with different roles in courses
     *
     * @param array $definition
     * @param string $expected
     * @covers       \local_competvet\external\user_type::execute
     * @dataProvider enrolment_data_provider
     * @runInSeparateProcess
     */
    public function test_type_with_enrolments_as_admin(array $definition, string $expected) {
        $this->setAdminUser();
        $userid = $this->setup_course_and_user_from_data($definition);
        $this->assertEquals($expected, $this->get_application_mode(['userid' => $userid])['type']);
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
            $group = $generator->create_group(['courseid' => $course->id]);
            $generator->create_group_member(['groupid' => $group->id, 'userid' => $user->id]);

            foreach ($data['activities'] as $situationname => $roleoverride) {
                $module = $generator->create_module('competvet', ['course' => $course->id, 'shortname' => $situationname]);
                if (!empty($roleoverride)) {
                    $roleid = $DB->get_field('role', 'id', ['shortname' => $roleoverride]);
                    [$course, $cm] = get_course_and_cm_from_instance($module->id, competvet::MODULE_NAME);
                    role_assign($roleid, $user->id, context_module::instance($cm->id));
                }
                $situation = competvet::get_from_instance_id($module->id)->get_situation();
                // Create at least one planning for students if not test will fail as student will have no role.
                $planning = new planning(0, (object) [
                    'startdate' => time(),
                    'enddate' => time() * 24 * 3600,
                    'groupid' => $group->id,
                    'session' => 'session',
                    'situationid' => $situation->get('id'),
                ]);
                $planning->create();
            }
        }
        return $user->id;
    }

    /**
     * Test with different roles in courses
     *
     * @param array $definition
     * @param string $expected
     * @covers       \local_competvet\external\user_type::execute
     * @dataProvider enrolment_data_provider
     * @runInSeparateProcess
     */
    public function test_type_with_current_user(array $definition, string $expected) {
        $userid = $this->setup_course_and_user_from_data($definition, $expected);
        $user = core_user::get_user($userid);
        $this->setUser($user);
        $this->assertEquals($expected, $this->get_application_mode([])['type']);
    }
}
