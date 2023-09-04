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

namespace block_course_statistics\measuresinquizzes;

/**
 * Interface file for block_course_statistics
 *
 * @package    block_course_statistics
 * @copyright 2023 onwards WIDE Services  {@link https://www.wideservices.gr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface logic_interface {

    /**
     * Group all info of quizzes in a courses
     * @package    block_course_statistics
     * @param bool $isteacher
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return mixed
     */
    public function group_courses_quizzes_data($isteacher , $searchperiod , $from , $to);

    /**
     * Group all info of quizzes in a course
     * @package    block_course_statistics
     * @param int $courseid
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return mixed
     */
    public function group_viewquizzes_data($courseid ,  $searchperiod , $from , $to);

    /**
     * Group all info of users in a quiz
     * @package    block_course_statistics
     * @param int $courseid
     * @param int $quizid
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return mixed
     */
    public function group_viewusers_data($courseid , $quizid , $searchperiod , $from , $to);

}

