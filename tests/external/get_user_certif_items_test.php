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
namespace external;
defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/mod/competvet/tests/test_data_definition.php');

use core_user;
use DateTime;
use external_api;
use externallib_advanced_testcase;
use local_competvet\external\edit_eval_observation;
use local_competvet\external\get_user_certif_items;
use mod_competvet\local\api\plannings;
use mod_competvet\local\persistent\observation;
use mod_competvet\local\persistent\observation_comment;
use mod_competvet\local\persistent\planning;
use mod_competvet\local\persistent\situation;
use test_data_definition;

/**
 * Get user certifs items
 *
 * @package     local_competvet
 * @copyright   2024 CALL Learning <contact@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_user_certif_items_test extends externallib_advanced_testcase {
    use test_data_definition;

    /**
     * As we have a test that does write into the DB, we need to setup and tear down each time
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $competvetgenerator = $generator->get_plugin_generator('mod_competvet');
        $startdate = new DateTime('last Monday');
        $this->generates_definition($this->get_data_definition_set_2($startdate->getTimestamp()), $generator, $competvetgenerator);
    }

    /**
     * Test with user does not exists.
     *
     * @covers \local_competvet\external\user_type::execute
     */
    public function test_user_not_exist_test() {
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
     */
    public function test_planning_not_exist_test() {
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
        $validate = [get_user_certif_items::class, 'validate_parameters'];
        $params = call_user_func(
            $validate,
            get_user_certif_items::execute_parameters(),
            $args
        );
        $params = array_values($params);
        $returnvalue = get_user_certif_items::execute(...$params);
        return external_api::clean_returnvalue(get_user_certif_items::execute_returns(), $returnvalue);
    }

    /**
     * Test with existing observation
     *
     * @covers       \local_competvet\external\get_user_certif_items::execute
     */
    public function test_get_user_certif_items() {
        $this->setAdminUser();
        $student = core_user::get_user_by_username('student1');
        $situation = situation::get_record(['shortname' => 'SIT1']);
        $plannings = plannings::get_plannings_for_situation_id($situation->get('id'), $student->id);
        $planning = array_shift($plannings);
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_competvet');
        $certifs = $this->get_user_certif_items(['userid' => $student->id, 'planningid' => $planning['id']]);
        $this->assertEquals([

        ], $certifs);
    }
}
