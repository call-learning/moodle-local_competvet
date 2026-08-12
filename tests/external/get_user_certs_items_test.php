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
use mod_competvet\tests\test_data_definition;
defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');

use core_user;
use external_api;
use externallib_advanced_testcase;
use mod_competvet\local\persistent\planning;
use mod_competvet\local\persistent\situation;

/**
 * Get user certifications items
 *
 * @package     local_competvet
 * @copyright   2024 CALL Learning <contact@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class get_user_certs_items_test extends externallib_advanced_testcase {
    use test_data_definition;

    /**
     * As we have a test that does write into the DB, we need to setup and tear down each time
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $competvetgenerator = $generator->get_plugin_generator('mod_competvet');
        $startdate = $this->get_start_date();
        $this->generates_definition($this->get_data_definition_set_2($startdate->getTimestamp()), $generator, $competvetgenerator);
    }

    /**
     * Test with user does not exists.
     *
     * @covers \local_competvet\external\user_type::execute
     * @runInSeparateProcess
     */
    public function test_user_not_exist_test(): void {
        $this->setAdminUser();
        $plannings = planning::get_records();
        $planning = end($plannings);
        $this->expectExceptionMessage('local_competvet/invaliduserid');
        $result = $this->get_user_certif_items(['userid' => 9999, 'planningid' => $planning->get('id')]);
    }

    /**
     * Test with planning does not exists.
     *
     * @covers \local_competvet\external\user_type::execute
     * @runInSeparateProcess
     */
    public function test_planning_not_exist_test(): void {
        $this->setAdminUser();
        $user = core_user::get_user_by_username('student1');
        $this->expectExceptionMessage('local_competvet/invalidplanningid');
        $result = $this->get_user_certif_items(['userid' => $user->id, 'planningid' => 9999]);
    }

    /**
     * Helper
     *
     * @param array $args
     * @return mixed
     */
    protected function get_user_certif_items($args) {
        $validate = [get_user_certs_items::class, 'validate_parameters'];
        $params = call_user_func(
            $validate,
            get_user_certs_items::execute_parameters(),
            $args
        );
        $params = array_values($params);
        $returnvalue = get_user_certs_items::execute(...$params);
        return external_api::clean_returnvalue(get_user_certs_items::execute_returns(), $returnvalue);
    }

    /**
     * Test with existing observation
     *
     * @covers       \local_competvet\external\get_user_certs_items::execute
     * @runInSeparateProcess
     */
    public function test_get_user_certif_items(): void {
        $this->setAdminUser();
        $student = core_user::get_user_by_username('student1');
        $situation = situation::get_record(['shortname' => 'SIT1']);
        // Select the planning containing the fixture certification explicitly. The student is
        // also enrolled in a later planning, so relying on the returned list order makes this
        // test depend on which planning is returned first.
        $plannings = planning::get_records(
            ['situationid' => $situation->get('id'), 'session' => '2023'],
            'startdate'
        );
        $planning = reset($plannings);
        $certifs = $this->get_user_certif_items(['userid' => $student->id, 'planningid' => $planning->get('id')]);
        $this->assertEquals([0, 1, 2, 3], array_map(fn($cert) => $cert['category'], $certifs));

        $declaredcategories = array_values(array_filter(
            $certifs,
            fn($cert) => count(array_filter($cert['items'], fn($item) => $item['isdeclared'])) > 0
        ));
        $this->assertCount(1, $declaredcategories);
        $this->assertSame(2, $declaredcategories[0]['category']);

        $declared = array_values(array_filter($declaredcategories[0]['items'], fn($item) => $item['isdeclared']))[0];
        $this->assertTrue($declared['rejected']);
        $this->assertFalse($declared['confirmed']);

        $nonrejected = array_values(array_filter(
            array_merge(...array_column($certifs, 'items')),
            fn($item) => !$item['rejected']
        ));
        $this->assertNotEmpty($nonrejected);
        $this->assertFalse($nonrejected[0]['rejected']);
    }
}
