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
use context_course;

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
     * @param int $courseid
     * @param bool $isteacher
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return array
     * @throws \dml_exception
     */
    public function group_courses_forums_data($courseid , $isteacher , $searchperiod = false , $from = null , $to = null) {

        $dbquery = new dbquery();

        $datacourses = [];
        $measures = [];

        // 1. Find Forums in each course that we look.

        $forums = $dbquery->db_course_forums($courseid , null , $searchperiod , $from , $to);

        $courseforums = count($forums);

        // 2. How many discussions (topics) the course forums has in total.

        $topics = $dbquery->db_forums_topics($courseid , null , $searchperiod , $from , $to);

        $forumtopics = count($topics);

        // 3. How many posts the course has in its Forums.

        $posts = $dbquery->db_topics_posts($courseid , null , null  , $searchperiod , $from , $to);

        $topicposts = count($posts);

        // 4. If posts are more than one in a topic is active and already initialized.

        $initialized = (!empty($forums)) ? $dbquery->db_topics_initialized($courseid , $forums ,
                null , null  , $searchperiod , $from , $to) : 0;

        // 5. How many post answers (replies) the topics have in total.
        // Any other post in a topic except the first one is a reply.

        $topicpostanswers = $topicposts - $forumtopics;

        // 6. How many of the posts were read by the users.
        // Might the Moodle is buggy at mdl_forum_read table is always empty no matter what... so...

        $postreads = 0;

        $data = [
                'courseid' => $courseid,
                'course' => $dbquery->db_course_title($courseid)->fullname ,
                'forums' => $courseforums,
                'posts' => $topicposts,
                'topics' => $forumtopics,
                'postanswers' => $topicpostanswers,
                'initialized' => $initialized,
                'postreads' => $postreads,

        ];

        $datacourses[] = $data;

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

        $dataforums = [];
        $measures = [];

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
    public function group_viewtopics_data($courseid , $forumid , $searchperiod = false , $from = null , $to = null) {
        $dbquery = new dbquery();

        $datatopics = [];
        $measures = [];

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

            $postreads = $dbquery->db_topic_post_reads($forumid , $topic->id ,
                    $searchperiod , $from , $to);

            $data = [
                    'courseid' => $courseid,
                    'forumid' => $forumid,
                    'topicid' => $topic->id,
                    'course' => $dbquery->db_course_title($courseid)->fullname,
                    'forums' => $dbquery->db_course_forums($courseid , $forumid)->name,
                    'posts' => $topicposts,
                    'topics' => $topic->name,
                    'postanswers' => $topicpostanswers,
                    'initialized' => $initialized,
                    'postreads' => count($postreads),

            ];

            $datatopics[] = $data;

        }
        $measures['topicsdata'] = $datatopics;

        return $measures;

    }

    /**
     * Activity for each users in forum , topics
     * @param int $courseid
     * @param int $forumid
     * @param int $topicid
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return array
     */
    public function group_viewusers_data($courseid , $forumid , $topicid , $searchperiod = false , $from = null , $to = null) {

        $dbquery = new dbquery();

        $datausers = [];
        $measures = [];

        // Im not getting the subscribed users in the forum cause the subscription might be optional.

        // So im getting the the enrolled users in course instead to be sure that ill catch the ones that posted in topic.
        $enrolledusers = get_enrolled_users(context_course::instance($courseid));

        // Foreach subscribed user how many posts , answers , reads has in this topic of the forum?
        if ($enrolledusers  && !empty($enrolledusers )) {

            foreach ($enrolledusers as $sub) {

                $topictitle = $dbquery->db_topic_title($topicid);

                // Find user activity in forum how many post , answers , reads has.
                $posts = $dbquery->db_topic_user_posts($sub->id , $courseid , $forumid , $topicid ,
                        $searchperiod , $from , $to);

                // Find answers , posts that have a parent discussion.
                $answers = $dbquery->db_topic_user_answers($sub->id , $courseid , $forumid , $topicid ,
                        $searchperiod , $from , $to);

                // Find post read by the user.

                $reads = $dbquery->db_user_post_reads($sub->id , $forumid , $topicid ,
                        $searchperiod , $from , $to);

                // I need to catchy only the ones that made a post in the topic.
                if (count($posts) > 0) {
                    $data = [
                            'courseid' => $courseid,
                            'forumid' => $forumid,
                            'userid' => $sub->id,
                            'lastname' => $sub->lastname,
                            'firstname' => $sub->firstname,
                            'coursetitle' => $dbquery->db_course_title($courseid)->fullname,
                            'forum' => $dbquery->db_course_forums($courseid , $forumid)->name,
                            'posts' => count($posts),
                            'topic' => $topictitle->name,
                            'postanswers' => count($answers),
                            'postreads' => count($reads),

                    ];

                    $datausers[] = $data;
                }

            }
        }

        $measures['usersdata'] = $datausers;

        return $measures;
    }

}

