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

namespace block_course_statistics\local\measurespertool;

/**
 * Interface file for block_course_statistics
 *
 * @package    block_course_statistics
 * @copyright 2023 onwards WIDE Services  {@link https://www.wideservices.gr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface logic_interface {

    /**
     *  Fetch the course title.
     * This method must be a general one
     * Should be added to course_statistics class.
     * @package    block_course_statistics
     * @param int $courseid
     * @return mixed
     */
    public function course_title($courseid);

    /**
     * This method calculates all session times in activity modules
     * for each enrolled user and saves it in an array with a userid
     * that indicated whose session time it is.
     * A user may have many sessions in a course
     * @package    block_course_statistics
     * @param array $enrolledusers
     * @param int $courseid
     * @return array
     * @throws \dml_exception
     */
    public function get_enrolled_users_sessions($enrolledusers , $courseid);

    /**
     * For each enrolled user find
     * the activity session times in course
     * @package    block_course_statistics
     * @param int $courseid
     * @param int $userid
     * @param int $scheduledtime
     * @return mixed
     */
    public function calculate_user_activity_session_time($courseid , $userid , $scheduledtime);

    /**
     * Group all info of course activities measures.
     * @package    block_course_statistics
     * @param int $courseid
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return mixed
     */
    public function group_activities_measures_data($courseid , $searchperiod , $from , $to);

    /**
     * Group all info of course activities general data.
     * @package    block_course_statistics
     * @param int $courseid
     * @param bool $isteacher
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return mixed
     */
    public function group_courses_tools_data($courseid , $isteacher , $searchperiod , $from , $to);

    /**
     * Group all info of users in activities.
     * @package    block_course_statistics
     * @param int $courseid
     * @param int $cminstance
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return mixed
     */
    public function group_viewusers_data($courseid , $cminstance , $searchperiod , $from , $to);

}

