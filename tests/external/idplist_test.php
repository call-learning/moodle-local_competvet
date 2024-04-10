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

use advanced_testcase;

global $CFG;

require_once($CFG->libdir . '/externallib.php');

/**
 * Auth tests
 *
 * @package     local_competvet
 * @copyright   2023 CALL Learning <contact@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class idplist_test extends advanced_testcase {
    /**
     * Test an API function
     *
     * @covers \local_competvet\external\idplist
     */
    public function test_auth_without_idps() {
        $this->assertEmpty(\local_competvet\external\idplist::execute());
    }

    /**
     * Test an API function
     *
     * @covers \local_competvet\external\idplist
     */
    public function test_auth_with_idp() {
        $this->resetAfterTest();
        $this->setup_cas();
        $idplist = \local_competvet\external\idplist::execute();
        $this->assertEquals(
            [
                'url' => 'https://www.example.com/moodle/local/competvet/webservices/cas-login.php?authCAS=CAS',
                'iconurl' => '',
                'name' => 'Test CAS',
            ],
            $idplist[0],
        );
    }

    /**
     * Setup CAS for auth
     *
     * @return void
     */
    private function setup_cas() {
        global $CFG;
        $CFG->auth = 'manual,cas';
        set_config('hostname', $CFG->wwwroot, 'auth_cas');
        set_config('auth_logo', '', 'auth_cas');
        set_config('auth_name', 'Test CAS', 'auth_cas');
        get_enabled_auth_plugins(true); // Enable cas.
    }
}
