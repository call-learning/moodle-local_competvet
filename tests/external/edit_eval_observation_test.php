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
use core_user;
use DateTime;
use core_external\external_api;
use mod_competvet\local\api\plannings;
use mod_competvet\local\persistent\observation;
use mod_competvet\local\persistent\observation_comment;
use mod_competvet\local\persistent\situation;
use mod_competvet\tests\test_data_definition;

/**
 * Edit eval observation tests
 *
 * @package     local_competvet
 * @copyright   2023 CALL Learning <contact@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class edit_eval_observation_test extends \advanced_testcase {
    use test_data_definition;

    /**
     * Data provider for test_edit_eval_observation
     *
     * @return array[]
     */
    public static function data_edit_observation_comment_for_user(): array {
        return [
            'observer1 edit comment' =>
                [
                    'category' => observation::CATEGORY_EVAL_OBSERVATION,
                    'student' => 'student1',
                    'observer' => 'observer1',
                    'context' => 'A context',
                    'comments' => [
                        ['type' => observation_comment::OBSERVATION_COMMENT, 'comment' => 'A comment'],
                        ['type' => observation_comment::OBSERVATION_PRIVATE_COMMENT, 'comment' => 'Another comment'],
                    ],
                    'criteria' => [
                        ['id' => 'Q001', 'level' => 1],
                        ['id' => 'Q002', 'comment' => 'Comment 1'],
                        ['id' => 'Q003', 'comment' => 'Comment 2'],
                    ],
                    'editpayload' => [
                        'currentuser' => 'observer1',
                        'payload' => [
                            'comments' =>
                                [
                                    ['type' => observation_comment::OBSERVATION_COMMENT, 'comment' => 'A new comment'],
                                    ['type' => observation_comment::OBSERVATION_PRIVATE_COMMENT, 'comment' => 'Comment 1'],
                                ],
                        ],
                    ],
                ],
            // TODO MDL-99999: check student cannot edit observer comments.
            'student edit autoeval' =>
                [
                    'category' => observation::CATEGORY_EVAL_AUTOEVAL,
                    'student' => 'student1',
                    'observer' => 'observer1',
                    'context' => 'A context',
                    'comments' => [
                        ['type' => observation_comment::AUTOEVAL_AMELIORATION, 'comment' => 'A comment'],
                        ['type' => observation_comment::AUTOEVAL_MANQUE, 'comment' => 'A comment'],
                        ['type' => observation_comment::AUTOEVAL_PROGRESS, 'comment' => 'A comment'],
                        ['type' => observation_comment::AUTOEVAL_OBSERVER_COMMENT, 'comment' => 'An observer comment'],
                    ],
                    'criteria' => [
                        ['id' => 'Q001', 'level' => 1],
                        ['id' => 'Q002', 'comment' => 'Comment 1'],
                        ['id' => 'Q003', 'comment' => 'Comment 2'],
                    ],
                    'editpayload' => [
                        'currentuser' => 'student1',
                        'payload' => [
                            'comments' =>
                                [
                                    ['type' => observation_comment::AUTOEVAL_AMELIORATION, 'comment' => 'A comment'],
                                    ['type' => observation_comment::AUTOEVAL_PROGRESS, 'comment' => 'A comment'],
                                ],
                        ],
                    ],
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
        $startdate = new DateTime('2023-01-01 10:00:00');

        $this->generates_definition($this->get_data_definition_set_2($startdate->getTimestamp()), $generator, $competvetgenerator);
    }

    /**
     * Test with existing observation.
     *
     * @covers \local_competvet\external\user_type::execute
     * @runInSeparateProcess
     */
    public function test_observation_not_exist_test(): void {
        $this->setAdminUser();
        $result = $this->edit_eval_observation(['observationid' => 9999]);
        $this->assertEquals('invalidobservationid', $result['warnings'][0]['warningcode']);
    }

    /**
     * Helper
     *
     * @param array $args
     * @return mixed
     */
    protected function edit_eval_observation($args) {
        $validate = [edit_eval_observation::class, 'validate_parameters'];
        $params = call_user_func(
            $validate,
            edit_eval_observation::execute_parameters(),
            $args
        );
        $params = array_values($params);
        $returnvalue = edit_eval_observation::execute(...$params);
        return external_api::clean_returnvalue(edit_eval_observation::execute_returns(), $returnvalue);
    }

    /**
     * Test with existing observation
     *
     * @covers       \local_competvet\external\edit_eval_observation
     * @dataProvider data_edit_observation_comment_for_user
     * @runInSeparateProcess
     *
     * @param int $category
     * @param string $student
     * @param string $observer
     * @param string $context
     * @param array $comments
     * @param array $criteria
     * @param array $editpayload
     * @return void
     */
    public function test_edit_comment_eval_observation(
        int $category,
        string $student,
        string $observer,
        string $context,
        array $comments,
        array $criteria,
        array $editpayload
    ): void {
        $this->setAdminUser();
        $student = core_user::get_user_by_username($student);
        $observer = core_user::get_user_by_username($observer);
        $situation = situation::get_record(['shortname' => 'SIT1']);
        $plannings = plannings::get_plannings_for_situation_id($situation->get('id'), $student->id);
        $planning = array_shift($plannings);
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_competvet');
        $newobs = $generator->create_observation_with_comment([
            'planningid' => $planning['id'],
            'studentid' => $student->id,
            'observerid' => $observer->id,
            'category' => $category,
            'context' => $context,
            'comments' => $comments,
            'criteria' => $criteria,

        ]);
        ['currentuser' => $currentuser, 'payload' => $payload] = $editpayload;
        $currentuser = core_user::get_user_by_username($currentuser);
        $this->setUser($currentuser);
        $this->edit_eval_observation(
            array_merge(
                ['observationid' => $newobs->id],
                $payload
            )
        );
        foreach ($payload['comments'] as $comment) {
            $this->assertEquals($comment['comment'], observation_comment::get_record([
                'observationid' => $newobs->id,
                'type' => $comment['type'],
            ])->get('comment'));
        }
    }
}
