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

namespace block_course_statistics\generalmeasures;

/**
 * Interface file for block_course_statistics
 *
 * @package    block_course_statistics
 * @copyright 2023 onwards WIDE Services  {@link https://www.wideservices.gr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface logic_interface {

    /**
     * Course title
     * @package    block_course_statistics
     * @param int $courseid
     * @return mixed
     * @throws \dml_exception
     */
    public function course_title($courseid);

    /**
     * Finds the enrolled users in a course
     * And does calculations about the time spent in session.
     * @package    block_course_statistics
     * @param array  $enrolledusers
     * @param int $courseid
     * @return mixed
     */
    public function get_enrolled_users_sessions($enrolledusers , $courseid);


    /**
     * For each enrolled user find
     * the course session time
     * @package    block_course_statistics
     * @param int $courseid
     * @param int $userid
     * @param int $scheduledtime
     * @return mixed
     */
    public function calculate_user_course_session_time($courseid , $userid , $scheduledtime);

    /**
     * Prepares courses data for the view template
     * @package    block_course_statistics
     * @param int $courseid
     * @param int $isteacher
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return mixed
     */
    public function group_courses_measures_data($courseid , $isteacher , $searchperiod , $from , $to);

    /**
     * Prepares users data for the view template
     * @package    block_course_statistics
     * @param int $courseid
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return mixed
     */
    public function group_users_measures_data($courseid , $searchperiod , $from , $to);

}

