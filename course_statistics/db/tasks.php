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

/**
 * Task to precalculate times from logstore table.
 * @package block_course_statistics
 * @copyright 2022 onwards WIDE Services  {@link https://www.wideservices.gr}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$tasks = [
        [
                'classname' => 'block_course_statistics\task\statistics',
                'blocking' => 0,
                'minute' => '10',
                'hour' => '0',
                'day' => '*',
                'month' => '*',
                'dayofweek' => '*',
                'disabled' => 0,
        ],
];
