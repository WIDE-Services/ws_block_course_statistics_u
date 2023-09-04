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
namespace block_course_statistics\measuresfortheforum;

use block_course_statistics\dbquery;

/**
 * Class main
 */
class forumlogic implements logic_interface {

    /**
     * Construct
     */
    public function __construct() {
    }
    /**
     * Group all info of forums in courses
     * @param bool $isteacher
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return array
     * @throws \dml_exception
     */
    public function group_courses_forums_data($isteacher , $searchperiod = false , $from = null , $to = null) {

        global $USER;

        $dbquery = new dbquery();

        $datacourses = array();
        $measures = array();

        // IF user is teacher find the course that is teacher and calculate Forum measures.
        // ELSE is admin measure Forums in all courses.
        if ($isteacher && !is_siteadmin($USER->id)) {

            $courses = $dbquery->db_teacher_courses($USER->id);

        } else {

            $courses = $dbquery->db_all_courses();
        }
        foreach ($courses as $course) {

            // 1. Find Forums in each course that we look.

            $forums = $dbquery->db_course_forums($course->id , null , $searchperiod , $from , $to);

            $courseforums = count($forums);

            // 2. How many discussions (topics) the course forums has in total.

            $topics = $dbquery->db_forums_topics($course->id , null , $searchperiod , $from , $to);

            $forumtopics = count($topics);

            // 3. How many posts the course has in its Forums.

            $posts = $dbquery->db_topics_posts($course->id , null , null  , $searchperiod , $from , $to);

            $topicposts = count($posts);

            // 4. If posts are more than one in a topic is active and already initialized.

            $initialized = (!empty($forums)) ? $dbquery->db_topics_initialized($course->id , $forums ,
            null , null  , $searchperiod , $from , $to) : 0;

            // 5. How many post answers (replies) the topics have in total.
            // Any other post in a topic except the first one is a reply.

            $topicpostanswers = $topicposts - $forumtopics;

            // 6. How many of the posts are read by the users.
            // Might the Moodle is buggy at mdl_forum_read table is always empty no matter what... so...

            $postreads = 0;

            $data = [
                    'courseid' => $course->id,
                    'course' => $course->fullname,
                    'forums' => $courseforums,
                    'posts' => $topicposts,
                    'topics' => $forumtopics,
                    'postanswers' => $topicpostanswers,
                    'initialized' => $initialized,
                    'postreads' => $postreads,

            ];

            $datacourses[] = $data;

        }

        $measures['generaldata'] = $datacourses;

        return $measures;

    }

    /**
     * Group all info of forums in a course
     * @param int $courseid
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return array
     * @throws \dml_exception
     */
    public function group_viewforums_data($courseid , $searchperiod = false , $from = null , $to = null) {

        $dbquery = new dbquery();

        $dataforums = array();
        $measures = array();

        // 1. Find Forums in this course that we look.

        $forums = $dbquery->db_course_forums($courseid);

        foreach ($forums as $forum) {

            // 2. How many discussions (topics) the course forums has in total.

            $topics = $dbquery->db_forums_topics($courseid , $forum->id , $searchperiod , $from , $to);

            $forumtopics = count($topics);

            // 3. How many posts the course has in its Forums.

            $posts = $dbquery->db_topics_posts($courseid , $forum->id ,
                    null , $searchperiod , $from , $to);

            $topicposts = count($posts);

            // 4. If posts are more than one in a topic is active and already initialized.

            $initialized = $dbquery->db_topics_initialized($courseid , null , $forum->id ,
                    null , $searchperiod , $from , $to);

            // 5. How many post answers (replies) the topics have in total.
            // Any other post in a topic except the first one is a reply.

            $topicpostanswers = $topicposts - $forumtopics;

            // 6. How many of the posts are read by the users.
            // Might the Moodle is buggy at mdl_forum_read table is always empty no matter what... so...

            $postreads = 0;

            $data = [
                    'courseid' => $courseid,
                    'forumid' => $forum->id,
                    'course' => $dbquery->db_course_title($courseid)->fullname,
                    'forums' => $forum->name,
                    'posts' => $topicposts,
                    'topics' => $forumtopics,
                    'postanswers' => $topicpostanswers,
                    'initialized' => $initialized,
                    'postreads' => $postreads,

            ];

            $dataforums[] = $data;

        }
        $measures['forumsdata'] = $dataforums;

        return $measures;
    }

    /**
     * Group all info of topics in a forum
     * @param int $courseid
     * @param int $forumid
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return array
     * @throws \dml_exception
     */
    public function group_viewtopics_data($courseid , $forumid, $searchperiod = false , $from = null , $to = null) {
        $dbquery = new dbquery();

        $datatopics = array();
        $measures = array();

        // 1. Find topics in this forum that we look.

        $topics = $dbquery->db_forums_topics($courseid , $forumid , $searchperiod , $from , $to);

        foreach ($topics as $topic) {
            // 2. How many posts the topic has in this forum.

            $posts = $dbquery->db_topics_posts($courseid , $forumid, $topic->id ,
                    $searchperiod , $from , $to);

            $topicposts = count($posts);

            // 4. If posts are more than one in a topic is active and already initialized.

            $initialized = $dbquery->db_topics_initialized($courseid , null , $forumid , $topic->id ,
                    $searchperiod , $from , $to);

            // 5. How many post answers (replies) the topics have in total.
            // Any other post in a topic except the first one is a reply.

            $topicpostanswers = $topicposts - 1;

            // 6. How many of the posts are read by the users.
            // Might the Moodle is buggy at mdl_forum_read table is always empty no matter what... so...

            $postreads = 0;

            $data = [
                    'courseid' => $courseid,
                    'forumid' => $forumid,
                    'course' => $dbquery->db_course_title($courseid)->fullname,
                    'forums' => $dbquery->db_course_forums($courseid , $forumid)->name,
                    'posts' => $topicposts,
                    'topics' => $topic->name,
                    'postanswers' => $topicpostanswers,
                    'initialized' => $initialized,
                    'postreads' => $postreads,

            ];

            $datatopics[] = $data;

        }
        $measures['topicsdata'] = $datatopics;

        return $measures;

    }
}

