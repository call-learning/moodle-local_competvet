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
use external_api;
use mod_competvet\tests\test_data_definition;

/**
 * Search items external test class
 *
 * @package     local_competvet
 * @copyright   2023 CALL Learning <contact@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class search_items_test extends \advanced_testcase {
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
        $this->generates_definition($this->get_data_definition_set_1($startdate->getTimestamp()), $generator, $competvetgenerator);
    }

    /**
     * Test to seacrh with empty query
     *
     * @covers \local_competvet\external\search_items
     * @runInSeparateProcess
     */
    public function test_search_item_empty(): void {
        $this->setAdminUser();
        $returnval = $this->search_items(['query' => '']);
        $this->assertIsArray($returnval);
        $this->assertCount(0, $returnval);
    }

    /**
     * Helper
     *
     * @param array $args
     * @return mixed
     */
    protected function search_items(array $args) {
        $validate = [\local_competvet\external\search_items::class, 'validate_parameters'];
        $params = call_user_func(
            $validate,
            \local_competvet\external\search_items::execute_parameters(),
            $args
        );
        $params = array_values($params);
        $returnvalue = \local_competvet\external\search_items::execute(...$params);
        return external_api::clean_returnvalue(\local_competvet\external\search_items::execute_returns(), $returnvalue);
    }

    /**
     * Test with existing user
     *
     * @covers \local_competvet\external\search_items
     * @runInSeparateProcess
     */
    public function test_search_user(): void {
        global $DB;
        $this->setAdminUser();
        // Admin is not in any situation so should return nothing.
        $returnval = $this->search_items(['query' => 'student']);
        $this->assertIsArray($returnval);
        $this->assertCount(0, $returnval);

        $this->setUser(\core_user::get_user_by_username('observer1')); // Observer1 is in situations with student1 and student2.
        $returnval = $this->search_items(['query' => 'student']);
        $this->assertIsArray($returnval);
        $this->assertCount(2, $returnval);
        $usernames = array_column($returnval, 'identifier');
        $this->assertTrue(in_array('student1', $usernames));
        $this->assertTrue(in_array('student2', $usernames));

        $this->setUser(\core_user::get_user_by_username('student1')); // Student 1 can see only himself and observers.
        $returnval = $this->search_items(['query' => 'student']);
        $this->assertIsArray($returnval);
        $this->assertCount(0, $returnval);
        $returnval = $this->search_items(['query' => 'observer']);
        $this->assertIsArray($returnval);
        $this->assertCount(2, $returnval);
        $usernames = array_column($returnval, 'identifier');
        $this->assertTrue(in_array('observer1', $usernames));
        $this->assertTrue(in_array('observerandevaluator', $usernames));

        $DB->set_field('user', 'firstname', 'observé', ['username' => 'observer1']);
        $returnval = $this->search_items(['query' => 'observé']);
        $this->assertIsArray($returnval);
        $this->assertCount(1, $returnval);
    }

    /**
     * Test with existing situation
     *
     * @covers \local_competvet\external\search_items
     * @runInSeparateProcess
     */
    public function test_search_situation(): void {
        $this->setAdminUser();
        // Admin is not in any situation so should return nothing.
        $returnval = $this->search_items(['query' => 'SIT']);
        $this->assertIsArray($returnval);
        $this->assertCount(0, $returnval);

        $this->setUser(\core_user::get_user_by_username('observer1')); // Observer1 is in situations with student1 and student2.
        $returnval = $this->search_items(['query' => 'SIT']);
        $this->assertIsArray($returnval);
        $this->assertCount(3, $returnval);
        $situationnames = array_column($returnval, 'identifier');
        $this->assertTrue(in_array('SIT1', $situationnames));
        $this->assertTrue(in_array('SIT2', $situationnames));
        $this->assertTrue(in_array('SIT3', $situationnames));

        $this->setUser(\core_user::get_user_by_username('student1')); // Student 1 can see its own situations.
        $returnval = $this->search_items(['query' => 'SIT']);
        $this->assertIsArray($returnval);
        $this->assertCount(3, $returnval);
        $this->assertTrue(in_array('SIT1', $situationnames));
        $this->assertTrue(in_array('SIT2', $situationnames));
        $this->assertTrue(in_array('SIT3', $situationnames));

        // Check with accent.
        $this->setUser(\core_user::get_user_by_username('student2')); // Student 1 can see its own situations.
        $returnval = $this->search_items(['query' => 'SIT']);
        $this->assertIsArray($returnval);
        $this->assertCount(1, $returnval);
        $this->assertTrue(in_array('SIT1', $situationnames));
        // Sit 3 group is in the future so student2 should not see it.
    }
}
