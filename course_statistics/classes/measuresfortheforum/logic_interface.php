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

namespace block_course_statistics\measuresfortheforum;

/**
 * Interface file for block_course_statistics
 *
 * @package    block_course_statistics
 * @copyright 2023 onwards WIDE Services  {@link https://www.wideservices.gr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface logic_interface {

    /**
     * Group all info of forums in a courses
     * @package    block_course_statistics
     * @param bool $isteacher
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return mixed
     */
    public function group_courses_forums_data($isteacher , $searchperiod , $from , $to);

    /**
     * Group all info of forums in a course
     * @package    block_course_statistics
     * @param int $courseid
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return mixed
     */
    public function group_viewforums_data($courseid , $searchperiod , $from , $to);

    /**
     * Group all info of topics in forums
     * @package    block_course_statistics
     * @param int $courseid
     * @param int $forumid
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return mixed
     */
    public function group_viewtopics_data($courseid , $forumid , $searchperiod , $from , $to);

}

