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
defined('MOODLE_INTERNAL') || die();
$oneweek = 60 * 60 * 24 * 7; // 1 week in seconds.
$onemonth = $oneweek * 4; // 1 month in seconds.
$results = [];
$results['student1results'] = [
    [
        'plannings' =>
            [

                [
                    'startdate' => (string) $startdate,
                    'enddate' => (string) ($startdate + $oneweek),
                    'session' => '2023',
                    'groupname' => 'group 8.1',
                ],
            ],
        'tags' => '["y:1"]',
        'shortname' => 'SIT1',
        'name' => 'SIT1',
        'evalnum' => 1,
        'autoevalnum' => 1,
        'roles' => '["student"]',
    ],
    [
        'plannings' =>
            [

                [
                    'startdate' => (string) ($startdate),
                    'enddate' => (string) ($startdate + $oneweek * 2),
                    'session' => '2023',
                    'groupname' => 'group 8.1',
                ],
            ],
        'tags' => '["y:2"]',
        'shortname' => 'SIT2',
        'name' => 'SIT2',
        'evalnum' => 1,
        'autoevalnum' => 1,
        'roles' => '["student"]',
    ],
    [
        'plannings' =>
            [
                [
                    'startdate' => (string) ($startdate),
                    'enddate' => (string) ($startdate + $oneweek),
                    'session' => '2023',
                    'groupname' => 'group 8.1',
                ],
            ],
        'tags' => '["y:3"]',
        'shortname' => 'SIT3',
        'name' => 'SIT3',
        'evalnum' => 1,
        'autoevalnum' => 1,
        'roles' => '["student"]',
    ]
];
$results['student2results'] = [
    [
        'plannings' =>
            [

                [
                    'startdate' => (string) ($startdate + $oneweek),
                    'enddate' => (string) ($startdate + $oneweek * 2),
                    'session' => '2023',
                    'groupname' => 'group 8.2',
                ],
            ],
        'tags' => '["y:1"]',
        'shortname' => 'SIT1',
        'name' => 'SIT1',
        'evalnum' => 1,
        'autoevalnum' => 1,
        'roles' => '["student"]',
    ],
    [
        'plannings' =>
            [
                [
                    'startdate' => (string) ($startdate + $onemonth * 2 + $oneweek),
                    'enddate' => (string) ($startdate + $onemonth * 2 + $oneweek * 2),
                    'session' => '2023',
                    'groupname' => 'group 8.2',
                ],
            ],
        'tags' => '["y:3"]',
        'shortname' => 'SIT3',
        'name' => 'SIT3',

        'evalnum' => 1,
        'autoevalnum' => 1,
        'roles' => '["student"]',
    ],
    [
        'plannings' =>
            [
                [
                    'startdate' => (string) ($startdate + $onemonth * 3),
                    'enddate' => (string) ($startdate + $onemonth * 3 + $oneweek),
                    'groupname' => 'group 8.1',
                    'session' => '2023',
                ],
            ],
        'tags' => '["y:1"]',
        'shortname' => 'SIT4',
        'name' => 'SIT4',
        'evalnum' => 1,
        'autoevalnum' => 1,
        'roles' => '["student"]',
    ],
    [
        'plannings' =>
            [

                [
                    'startdate' => (string) ($startdate + $onemonth * 6),
                    'enddate' => (string) ($startdate + $onemonth * 6 + $oneweek),
                    'groupname' => 'group 8.1',
                    'session' => '2023',
                ],
            ],
        'tags' => '["y:1"]',
        'shortname' => 'SIT7',
        'name' => 'SIT7',
        'evalnum' => 1,
        'autoevalnum' => 1,
        'roles' => '["student"]',
    ],
];
$results['observer1results'] = [
    [
        'plannings' =>
            [
                [
                    'startdate' => (string) $startdate,
                    'enddate' => (string) ($startdate + $oneweek),
                    'session' => '2023',
                    'groupname' => 'group 8.1',
                ],
            ],
        'tags' => '["y:1"]',
        'shortname' => 'SIT1',
        'name' => 'SIT1',
        'evalnum' => 1,
        'autoevalnum' => 1,
        'roles' => '["observer"]',
    ],
    [
        'plannings' =>
            [

                [
                    'startdate' => (string) ($startdate),
                    'enddate' => (string) ($startdate + $oneweek * 2),
                    'session' => '2023',
                    'groupname' => 'group 8.1',
                ],
            ],
        'tags' => '["y:2"]',
        'shortname' => 'SIT2',
        'name' => 'SIT2',

        'evalnum' => 1,
        'autoevalnum' => 1,
        'roles' => '["observer"]',
    ],
    [
        'plannings' =>
            [
                [
                    'startdate' => (string) ($startdate),
                    'enddate' => (string) ($startdate + $oneweek),
                    'session' => '2023',
                    'groupname' => 'group 8.1',
                ],
            ],
        'tags' => '["y:3"]',
        'shortname' => 'SIT3',
        'name' => 'SIT3',

        'evalnum' => 1,
        'autoevalnum' => 1,
        'roles' => '["observer"]',
    ],
];
$results['observer2results'] = [
    [
        'plannings' =>
            [
                [
                    'startdate' => (string) ($startdate + $onemonth * 3),
                    'enddate' => (string) ($startdate + $onemonth * 3 + $oneweek),
                    'session' => '2023',
                    'groupname' => 'group 8.1',
                ],

                [
                    'startdate' => (string) ($startdate + $onemonth * 3 + $oneweek),
                    'enddate' => (string) ($startdate + $onemonth * 3 + $oneweek * 2),
                    'session' => '2023',
                    'groupname' => 'group 8.3',
                ],
            ],
        'tags' => '["y:1"]',
        'shortname' => 'SIT4',
        'name' => 'SIT4',
        'evalnum' => 1,
        'autoevalnum' => 1,
        'roles' => '["observer"]',
    ],
    [
        'plannings' =>
            [

                [
                    'startdate' => (string) ($startdate + $onemonth * 4),
                    'enddate' => (string) ($startdate + $onemonth * 4 + $oneweek),
                    'session' => '2023',
                    'groupname' => 'group 8.2',
                ],

                [
                    'startdate' => (string) ($startdate + $onemonth * 4 + $oneweek),
                    'enddate' => (string) ($startdate + $onemonth * 4 + $oneweek * 2),
                    'session' => '2023',
                    'groupname' => 'group 8.3',
                ],
            ],
        'tags' => '["y:2"]',
        'shortname' => 'SIT5',
        'name' => 'SIT5',
        'evalnum' => 1,
        'autoevalnum' => 1,
        'roles' => '["observer"]',
    ],
    [
        'plannings' =>
            [

                [
                    'startdate' => (string) ($startdate + $onemonth * 5),
                    'enddate' => (string) ($startdate + $onemonth * 5 + $oneweek),
                    'session' => '2023',
                    'groupname' => 'group 8.3',
                ],
            ],
        'tags' => '["y:3"]',
        'shortname' => 'SIT6',
        'name' => 'SIT6',
        'evalnum' => 1,
        'autoevalnum' => 1,
        'roles' => '["observer"]',
    ],
    [
        'plannings' =>
            [

                [
                    'startdate' => (string) ($startdate + $onemonth * 6),
                    'enddate' => (string) ($startdate + $onemonth * 6 + $oneweek),
                    'session' => '2023',
                    'groupname' => 'group 8.1',
                ],
            ],
        'tags' => '["y:1"]',
        'shortname' => 'SIT7',
        'name' => 'SIT7',
        'evalnum' => 1,
        'autoevalnum' => 1,
        'roles' => '["observer"]',
    ],
    [
        'plannings' =>
            [

                [
                    'startdate' => (string) ($startdate + $onemonth * 7),
                    'enddate' => (string) ($startdate + $onemonth * 7 + $oneweek),
                    'groupname' => 'group 8.3',
                    'session' => '2023',
                ],
                [
                    'startdate' => (string) ($startdate + $onemonth * 12), // Future time.
                    'enddate' => (string) ($startdate + $onemonth * 12 + $oneweek),
                    'session' => '2030',
                    'groupname' => 'group 8.3',
                ],
            ],
        'tags' => '["y:2"]',
        'shortname' => 'SIT8',
        'name' => 'SIT8',
        'evalnum' => 1,
        'autoevalnum' => 1,
        'roles' => '["observer"]',
    ],
    [
        'plannings' => [
            [
                'startdate' => (string) ($startdate + $onemonth * 8),
                'enddate' => (string) ($startdate + $onemonth * 8 + $oneweek),
                'groupname' => 'group 8.4',
                'session' => '2023',
            ],
            [
                'startdate' => (string) ($startdate + $onemonth * 12), // Future time.
                'enddate' => (string) ($startdate + $onemonth * 12 + $oneweek),
                'groupname' => 'group 8.4',
                'session' => '2030',
            ],
        ],
        'tags' => '["y:3"]',
        'shortname' => 'SIT9',
        'name' => 'SIT9',
        'evalnum' => 1,
        'autoevalnum' => 1,
        'roles' => '["observer"]',
    ],
];
$results['teacher1results'] = [
    [
        'plannings' =>
            [
                [
                    'startdate' => (string) $startdate,
                    'enddate' => (string) ($startdate + $oneweek),
                    'session' => '2023',
                    'groupname' => 'group 8.1',
                ],
            ],
        'tags' => '["y:1"]',
        'shortname' => 'SIT1',
        'name' => 'SIT1',
        'evalnum' => 1,
        'autoevalnum' => 1,
        'roles' => '["unknown"]',
    ],
    [
        'plannings' =>
            [

                [
                    'startdate' => (string) ($startdate),
                    'enddate' => (string) ($startdate + $oneweek * 2),
                    'session' => '2023',
                    'groupname' => 'group 8.1',
                ],
            ],
        'tags' => '["y:2"]',
        'shortname' => 'SIT2',
        'name' => 'SIT2',

        'evalnum' => 1,
        'autoevalnum' => 1,
        'roles' => '["unknown"]',
    ],
    [
        'plannings' =>
            [
                [
                    'startdate' => (string) ($startdate),
                    'enddate' => (string) ($startdate + $oneweek),
                    'session' => '2023',
                    'groupname' => 'group 8.1',
                ],
            ],
        'tags' => '["y:3"]',
        'shortname' => 'SIT3',
        'name' => 'SIT3',
        'evalnum' => 1,
        'autoevalnum' => 1,
        'roles' => '["unknown"]',
    ],
];
