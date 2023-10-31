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
 * Main page output file for block_course_statistics
 *
 * @package block_course_statistics
 * @copyright 2023 onwards WIDE Services  {@link https://www.wideservices.gr}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_course_statistics\measuresinquizzes;

use block_course_statistics\dbquery;
use block_course_statistics\utils\utils;

/**
 * Class main
 */
class quizlogic implements logic_interface {

    /**
     * Construct
     */
    public function __construct() {
    }
    /**
     * Group all info of quizzes in a courses
     * @param int $courseid
     * @param bool $isteacher
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return mixed
     */
    public function group_courses_quizzes_data($courseid , $isteacher , $searchperiod = false , $from = null , $to = null) {

        $dbquery = new dbquery();
        $datacourses = array();
        $measures = array();

        $totaltime = 0;
        $totalattempts = 0;
        $avgscore = 0;

        // 1. Find Quizzes in each course that we look.

        $quizzes = $dbquery->db_course_quizzes($courseid , null , $searchperiod , $from , $to);

        $coursequizzes = count($quizzes);

        // 2. In these quizzes what is the total time  and total attempts of the users in it.

        if ($quizzes && !empty($quizzes)) {
            $totaldata = $dbquery->db_users_quizzes_total_time($quizzes , $searchperiod , $from , $to);
            $totaltime = $totaldata['totaltime'];
            $totalattempts = $totaldata['totalattempts'];

            // 3. Calculate the avg score of users in these quizzes.
            $avgscore = $dbquery->db_avg_users_score($quizzes , $searchperiod , $from , $to);
        }
        $data = [
                'courseid' => $courseid,
                'course' => $dbquery->db_course_title($courseid)->fullname ,
                'quiz' => $coursequizzes,
                'totaltime' => utils::format_activitytime($totaltime),
                'numtotaltime' => $totaltime,
                'attempts' => $totalattempts,
                'avgscore' => number_format($avgscore , 2).' %',

        ];

        $datacourses[] = $data;

        $measures['generaldata'] = $datacourses;

        return $measures;
    }

    /**
     * Group all info of quizzes in a course
     * @param int $courseid
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return mixed
     */
    public function group_viewquizzes_data($courseid , $searchperiod = false , $from = null , $to = null) {

        $dbquery = new dbquery();
        $dataquizzes = array();
        $measures = array();

        // 1. Find Quizzes in this course that we look.

        $quizzes = $dbquery->db_course_quizzes($courseid , null , $searchperiod , $from , $to);

        foreach ($quizzes as $quiz) {

            $totaltime = 0;
            $totalattempts = 0;
            $avgscore = 0;

            // 2. In this quiz what is the total time  and total attempts of the users in it.

            $totaldata = $dbquery->db_users_quiz_total_time($quiz->id , $searchperiod , $from , $to);

            $totaltime = $totaldata['totaltime'];
            $totalattempts = $totaldata['totalattempts'];

            // 3. Calculate the avg score of users in this quiz.
            $avgscore = $dbquery->db_avg_users_quiz_score($quiz->id , null , $searchperiod , $from , $to);
            $data = [

                    'courseid' => $courseid,
                    'quizid' => $quiz->id,
                    'course' => $dbquery->db_course_title($courseid)->fullname,
                    'quiz' => $quiz->name,
                    'totaltime' => utils::format_activitytime($totaltime),
                    'numtotaltime' => $totaltime,
                    'attempts' => $totalattempts,
                    'avgscore' => number_format($avgscore , 2).' %',

            ];

            $dataquizzes[] = $data;
        }

        $measures['quizdata'] = $dataquizzes;

        return $measures;
    }

    /**
     * Group all info of users in a quiz
     * @param int $courseid
     * @param int $quizid
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return mixed
     */
    public function group_viewusers_data($courseid , $quizid , $searchperiod = false , $from = null , $to = null) {
        $dbquery = new dbquery();
        $datausers = array();
        $measures = array();

        // 1. Find users results of this quiz in the course.

        $results = $dbquery->db_users_quiz_attempts($courseid ,  $quizid , $searchperiod , $from , $to);

        foreach ($results as $userid => $res) {

            $quiztitle = $res['quiz'];
            $lastname = $res['lastname'];
            $firstname = $res['firstname'];
            $totaltime = $res['totaltime'];
            $totalattempts = $res['totalattempts'];
            $attempt = $res['attempt'];
            $cmid = $res['cmid'];
            // 3. Calculate the avg score of users in this quiz.
            $avgscore = $dbquery->db_avg_users_quiz_score($quizid , $userid , $searchperiod , $from , $to);
            $data = [

                    'courseid' => $courseid,
                    'quizid' => $quizid,
                    'userid' => $userid,
                    'course' => $dbquery->db_course_title($courseid)->fullname,
                    'lastname' => $lastname,
                    'firstname' => $firstname,
                    'quiz' => $quiztitle,
                    'totaltime' => utils::format_activitytime($totaltime),
                    'numtotaltime' => $totaltime,
                    'attempts' => $totalattempts,
                    'attempt' => $attempt,
                    'cmid' => $cmid,
                    'avgscore' => number_format($avgscore , 2).' %',

            ];

            $datausers[] = $data;

        }

        $measures['usersdata'] = $datausers;

        return $measures;
    }
}

