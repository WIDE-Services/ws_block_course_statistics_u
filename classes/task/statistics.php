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

namespace block_course_statistics\task;

use block_course_statistics\local\generalmeasures\logic;
use block_course_statistics\local\measurespertool\toollogic;
use context_course;
use context_system;
use core\task\scheduled_task;
use stdClass;

/**
 * Schedule task statistics
 *
 * @package block_course_statistics
 * @copyright 2023 onwards WIDE Services  {@link https://www.wideservices.gr}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class statistics extends scheduled_task {

    /**
     * Basic mathod
     * @return string
     */
    public function get_name() {
        return 'Pre calculate statistics from logstore table';
    }

    /**
     * Execute method . Get data from logstore table and calculates times, action, etc
     * and saves them to plugins table.
     * @return void
     * @throws \dml_exception
     */
    public function execute() {
        global $DB;

        // Fetch all courses. Problem with big databases , never ends.

        // Solution select course to get measures from plugins configuration page.

        $courses = $DB->get_records('block_course_statistics_meas' , ['measure' => 1] , 'courseid' , 'courseid');
        foreach ($courses as $course) {

            $enrolledusers = get_enrolled_users(context_course::instance($course->courseid));

            if (!empty($enrolledusers)) {

                $toollogic = new toollogic();

                $toollogic->get_enrolled_users_sessions($enrolledusers , $course->courseid);

                $logic = new logic();

                $logic->get_enrolled_users_sessions($enrolledusers , $course->courseid);
            }
        }
    }
}

