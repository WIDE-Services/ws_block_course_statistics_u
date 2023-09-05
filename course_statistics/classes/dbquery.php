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

namespace block_course_statistics;

use dml_exception;

/**
 * General class for all queries.
 *
 * @package    block_course_statistics
 * @copyright 2023 onwards WIDE Services  {@link https://www.wideservices.gr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dbquery {

    /**
     * Construct
     */
    public function __construct() {

    }
    /**
     * Fetch id and fullname of the course
     * @param int $courseid
     * @return false|mixed|\stdClass
     * @throws \dml_exception
     */
    public function db_course_title($courseid) {

        global $DB;

        return $DB->get_record('course' , ['id' => $courseid] , 'id,fullname');

    }

    /**
     * Fetch id and name of the quiz
     * @param int  $quizid
     * @return false|mixed|\stdClass
     * @throws \dml_exception
     */
    public function db_quiz_title($quizid) {

        global $DB;

        return $DB->get_record('quiz' , ['id' => $quizid] , 'id,name');

    }

    /**
     * Fetch the modules in the course
     * @param int $instance
     * @return false|mixed
     * @throws \dml_exception
     */
    public function db_course_modules($instance) {

        global $DB;

        $sql = "SELECT m.name FROM {modules} AS m
                        JOIN {course_modules} AS cm ON cm.module = m.id
                        WHERE cm.id = {$instance}";

        return $DB->get_record_sql($sql);

    }

    /**
     * Info of all action in course sessions
     * @param int $courseid
     * @param int $userid
     * @param int $scheduledtime
     * @return array
     * @throws \dml_exception
     */
    public function db_course_sessions($courseid , $userid = null , $scheduledtime = null) {

        global $DB;

        $and = '';

        if (!is_null($userid)) {
            $and .= " AND userid = {$userid} ";
        }

        if (!is_null($scheduledtime)) {
            $and .= " AND timecreated > {$scheduledtime} ";
        }

        $sql = "SELECT id,timecreated, action, target
                FROM {logstore_standard_log}
                WHERE contextlevel in (70,50) ".
                $and." AND userid <> '-1'
                    AND courseid = {$courseid}
                    ORDER BY id DESC";

        try {
            return $DB->get_records_sql($sql);
        } catch (dml_exception $e) {
            // Handle the exception as needed (e.g., log the error, return a default result, etc.).
            throw $e; // Re-throwing the exception for higher-level handling.
        }

    }

    /**
     * Returns the name of the activity
     * @param int $instance
     * @return false|mixed
     * @throws \dml_exception
     */
    public function db_specific_module($instance) {

        global $DB;

        $sql = "SELECT m.name FROM {modules} AS m
                        JOIN {course_modules} AS cm ON cm.module = m.id
                        WHERE cm.id = {$instance}";

        return  $DB->get_record_sql($sql);

    }

    /**
     *  Insert record to DB table
     * @param string $table
     * @param object $insert
     * @return bool|int
     * @throws \dml_exception
     */
    public function db_insert_data($table , $insert) {
        global $DB;

        // Check first if data already exists.
        if (!$DB->record_exists($table , (array)$insert)) {
            return $DB->insert_record($table , $insert);
        }
        return false;
    }

    /**
     * Check if the first element of the capture package
     * in quiz is viewed course_module (the lobby before enter the quiz)
     * @param int $logid
     * @return bool
     * @throws dml_exception
     */
    public function db_is_quiz_lobby($logid) {
        global $DB;

        $lobby = $DB->get_record('logstore_standard_log' , ['id' => $logid]);

        if ($lobby && !empty($lobby)) {

            if ($lobby->action == 'viewed' && $lobby->target == 'course_module') {
                return true;
            }

        }

        return false;

    }
    /**
     * Insert multiple record in DB.
     * @param string $table
     * @param array $inserts
     * @param array $insertactivitysessions
     * @return void
     * @throws dml_exception
     */
    public function db_insert_multidata($table , $inserts , $insertactivitysessions = null) {
        global $DB;
        $thisinsert = 0;
        $moduleid = 0;
        // Check first if data already exists.
        foreach ($inserts as $insert) {

            if (!$DB->record_exists($table , (array)$insert)) {
                $isinsert = $DB->insert_record($table , $insert);

                // Do below actions only if we do measurements for the below table.
                if ($table == 'cs_user_activity_sessions') {

                    if ($isinsert && (!empty($insertactivitysessions) || !is_null($insertactivitysessions) )) {

                        // Find this row that was just inserted to DB.
                        // The get record function will output warnings that more than one row found.
                        // I need to be sure i get the last one.
                        $insertions = $DB->get_records($table , (array)$insert , 'id DESC' , 'id , cminstance' , '' , '1');
                        foreach ($insertions as $last) {

                            $thisinsert = $last->id;
                            $moduleid = $last->cminstance;

                        }

                        if (!empty($insertions) && ($thisinsert != 0 || is_null($thisinsert))) {

                            // Capture the details for start end , total time of session in this module.

                            $sessionnum = 1;

                            foreach ($insertactivitysessions as $key => $sessiontime) {

                                if (!empty($sessiontime)) {

                                    if ($key == $moduleid) {
                                        foreach ($sessiontime as $time) {
                                            $insertactivitysession = new \stdClass();
                                            $insertactivitysession->asid = $thisinsert;
                                            $insertactivitysession->session = $sessionnum;
                                            $insertactivitysession->startsession = (isset($time["startactivitysession"])) ?
                                                    $time["startactivitysession"] : 0;
                                            $insertactivitysession->endsession = (isset($time["endactivitysession"])) ?
                                                    $time["endactivitysession"] : 0;
                                            $insertactivitysession->sessiontime = (isset($time["activitysessiontime"])) ?
                                                    $time["activitysessiontime"] : 0;

                                            $issessioninsert = $DB->insert_record('cs_activities_session_dates' ,
                                                    $insertactivitysession);

                                            if ($issessioninsert) {
                                                $sessionnum++;
                                            }

                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }


    /**
     * Find the inserted user session
     * @param string $table
     * @param array $insert
     * @return false|mixed|\stdClass
     * @throws \dml_exception
     */
    public function db_user_session($table , $insert) {
        global $DB;

        return $DB->get_records($table , $insert , 'id DESC' , 'id' , '' , '1');

    }

    /**
     * Find the specific action
     * @param int $session
     * @return false|mixed|\stdClass
     * @throws \dml_exception\
     */
    public function db_session_user_actions($session) {

        global $DB;

        return $DB->get_record('logstore_standard_log' , ['id' => $session] , 'id,component,action,target,timecreated');

    }

    /**
     * Find the last session time of the user.
     * @param int $courseid
     * @param int $userid
     * @return array
     * @throws \dml_exception
     */
    public function db_find_scheduled_time($courseid , $userid) {

        global $DB;

        return $DB->get_records('cs_user_course_sessions' , ['courseid' => $courseid , 'userid' => $userid] ,
                'endsession DESC' , 'id,endsession' , '', '1');
    }

    /**
     * Prepares the statistics of user in course.
     * @param int $userid
     * @param int $courseid
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return \stdClass
     * @throws \dml_exception
     */
    public function db_user_course_data($userid , $courseid , $searchperiod = false , $from = null , $to = null) {

        global $DB;

        $statistics = new \stdClass();
        $totaltime = 0;
        $totalactions = 0;

        $sql = "SELECT *
        FROM {cs_user_course_sessions}
        WHERE userid = {$userid}
          AND courseid = {$courseid}";

        if ($searchperiod) {
            $sql .= " AND startsession >= {$from} AND endsession <= {$to}";

        }

        $results = $DB->get_records_sql($sql);

        $statistics->sessions = count($results);

        foreach ($results as $res) {

            $totaltime += $res->sessiontime;
            $totalactions += $res->actions;
        }

        $statistics->totaltime = $totaltime;

        $statistics->totalactions = $totalactions;

        $statistics->avgsessiontime = (count($results) != 0) ? $totaltime / count($results) : 0;

        $statistics->avgsessionactions = (count($results) != 0) ? $totalactions / count($results) : 0;

        return $statistics;
    }

    /**
     * Gets all precalculated measures for course activities from DB in a period if set.
     * @param int $courseid
     * @param int $cmid
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return \stdClass
     * @throws \dml_exception
     */
    public function db_course_activities_data($courseid , $cmid , $searchperiod = false , $from = null , $to = null) {

        global $DB;

        $statistics = new \stdClass();

        $totalactivitytime = 0;
        $totalactivitysessions = 0;
        $statistics->activity = '';

        // The data must be retrieved from table cs_activities_session_dates.
        $sql = "SELECT csd.* , cas.cminstance , cas.courseid ,
                        cas.activity , cas.activitytitle ,
                        cas.activitytime , cas.activitysessions
                    FROM {cs_activities_session_dates} csd
                    JOIN {cs_user_activity_sessions} cas ON cas.id = csd.asid
                    WHERE cas.cminstance = {$cmid}
                    AND cas.courseid = {$courseid}";

        if ($searchperiod) {
            $sql .= " AND csd.startsession >= {$from} AND csd.endsession <= {$to}";

        }

        $results = $DB->get_records_sql($sql);

        if (!empty($results)) {

            foreach ($results as $res) {
                $statistics->activity = $res->activity;
                // In case moodle doesnt catch an endsessiontime (happens) then the session time will be negative.
                $totalactivitytime += ($res->sessiontime >= 0) ? $res->sessiontime : 0;
            }

        }
        $totalactivitysessions = count($results);
        $statistics->totalactivitytime = ($totalactivitytime != 0) ? $totalactivitytime : 0;
        $statistics->totalactivitysessions = ($totalactivitysessions != 0) ? $totalactivitysessions : 0;
        $statistics->totalactivityavgtime = ($totalactivitysessions != 0 && $totalactivitytime != 0) ?
                $totalactivitytime / $totalactivitysessions : 0;

        // Calculate all activitysessions in this course for a period time if is set.

        $allsql = "SELECT csd.* , cas.cminstance , cas.courseid ,
                        cas.activity , cas.activitytitle ,
                        cas.activitytime , cas.activitysessions
                    FROM {cs_activities_session_dates} csd
                    JOIN {cs_user_activity_sessions} cas ON cas.id = csd.asid
                    WHERE cas.courseid = {$courseid}";

        if ($searchperiod) {
            $allsql .= " AND csd.startsession >= {$from} AND csd.endsession <= {$to}";

        }

        $activitysessions = $DB->get_records_sql($allsql);

        $statistics->averageusedinsessions = ($totalactivitysessions != 0 && count($activitysessions) != 0) ?
                $totalactivitysessions / count($activitysessions) * 100 : 0;

        return $statistics;

    }



    /**
     * Gets all precalculated measures for users in activity from DB in a period if set.
     * @param int $courseid
     * @param int $cmid
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return array
     * @throws \dml_exception
     */
    public function db_users_measures_in_activity($courseid , $cmid , $searchperiod = false , $from = null , $to = null) {

        global $DB;

        // The data must be retrieved from table cs_activities_session_dates.

        $sql = "SELECT csd.* , cas.userid , cas.cminstance , cas.courseid ,
                        cas.activity , cas.activitytitle , cas.activitytime, u.firstname , u.lastname
                    FROM {cs_activities_session_dates} csd
                    JOIN {cs_user_activity_sessions} cas ON cas.id = csd.asid
                    JOIN {user} u ON u.id = cas.userid
                    WHERE cas.cminstance = {$cmid}
                    AND cas.courseid = {$courseid}";

        if ($searchperiod) {

            $sql .= " AND csd.startsession >= {$from} AND csd.endsession <= {$to}";

        }

        $results = $DB->get_records_sql($sql);

        $usermeasures = array();

        if (!empty($results )) {

            foreach ($results as $user) {
                $objectmeasure = new \stdClass();
                $objectmeasure->userid = $user->userid;
                $objectmeasure->firstname = $user->firstname;
                $objectmeasure->lastname = $user->lastname;
                $objectmeasure->activity = $user->activity;
                $objectmeasure->cminstance = $user->cminstance;
                $objectmeasure->activitytitle = $user->activitytitle;
                // Moodle couldnt find the endsession and the result was negative. Case of loosing session.
                $objectmeasure->activitytime = ($user->sessiontime >= 0) ? $user->sessiontime : 0;
                $objectmeasure->activitysessions = $user->session;

                $usermeasures[] = $objectmeasure;
            }

            return $usermeasures;

        }

        return false;

    }

    /**
     * Prepares the statistics of user in activities in course.
     * @param int $userid
     * @param int $courseid
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return \stdClass
     * @throws \dml_exception
     */
    public function db_course_tools_data($userid , $courseid , $searchperiod = false , $from = null , $to = null) {

        global $DB;

        $statistics = new \stdClass();

        $totalactivitiestime = 0;
        $totalactivitiessessions = 0;
        $sql = "SELECT csd.* , cas.cminstance , cas.courseid ,
                        cas.activity , cas.activitytitle ,
                        cas.activitytime , cas.activitysessions
                    FROM {cs_activities_session_dates} csd
                    JOIN {cs_user_activity_sessions} cas ON cas.id = csd.asid
                    WHERE cas.userid = {$userid}
                    AND cas.courseid = {$courseid}";

        if ($searchperiod) {
            $sql .= " AND csd.startsession >= {$from} AND csd.endsession <= {$to}";

        }

        $results = $DB->get_records_sql($sql);

        foreach ($results as $res) {

            $totalactivitiestime += $res->sessiontime;
            $totalactivitiessessions ++;

        }

        $statistics->totalactivitiestime = ($totalactivitiestime != 0) ? $totalactivitiestime : 0;
        $statistics->totalactivitiessessions = ($totalactivitiessessions != 0) ? $totalactivitiessessions : 0;
        $statistics->totalactivitiesavgtime = ($totalactivitiessessions != 0 && $totalactivitiestime != 0) ?
                $totalactivitiestime / $totalactivitiessessions : 0;

        return $statistics;

    }

    /**
     * Fetch the next action of user when exit the course session
     * @param int $userid
     * @param int $courseid
     * @param int $exittime
     * @return false|mixed
     * @throws \dml_exception
     */
    public function db_user_exit_session($userid , $courseid , $exittime) {

        global $DB;

        $between = "";

        // First find if next session exists!
        $sql = "SELECT Distinct log.id , log.userid , log.timecreated
            FROM {logstore_standard_log} log
            JOIN {enrol} enrol ON log.courseid = enrol.courseid
            JOIN {user_enrolments} ue ON enrol.id = ue.enrolid
            WHERE log.action = 'viewed'
            AND log.component = 'core'
            AND log.target = 'course'
            AND log.contextlevel = :courselevel
            AND log.userid = :userid
            AND log.contextinstanceid = :courseid
            AND log.timecreated > :exittime
            ORDER BY log.timecreated ASC  LIMIT 1";

        $params = [
                'courselevel' => CONTEXT_COURSE,
                'courseid' => $courseid,
                'userid' => $userid,
                'exittime' => $exittime
        ];

        $nextsession = $DB->get_record_sql($sql, $params);

        // If exists then the next action out of the course must be between those sessions.

        if (!empty($nextsession)) {
            $between = " AND timecreated < {$nextsession->timecreated} ";
        }

        $sql = "SELECT id,timecreated FROM {logstore_standard_log}
                                            WHERE userid = {$userid}
                                            AND timecreated > {$exittime}" .$between."
                                            ORDER BY timecreated ASC LIMIT 1";

        return $DB->get_record_sql($sql);
    }

    /**
     * Fetch the next action of user when exit the activity session
     * @param int $userid
     * @param int $exittime
     * @return false|mixed
     * @throws \dml_exception
     */
    public function db_user_exit_activity_session($userid ,  $exittime) {

        global $DB;

        $sql = "SELECT timecreated FROM {logstore_standard_log}
                                            WHERE userid = {$userid}  AND timecreated > {$exittime}
                                            ORDER BY timecreated ASC LIMIT 1";

        return $DB->get_record_sql($sql);

    }

    /**
     * Fetch the min session activity id
     * @param int $minid
     * @return false|mixed
     * @throws \dml_exception
     */
    public function db_activity_session_min($minid) {

        global $DB;

        $logsqlmin = "SELECT timecreated  FROM {logstore_standard_log}
                                            WHERE id = {$minid}";

        return  $DB->get_record_sql($logsqlmin);

    }

    /**
     * Fetch the max session activity id
     * @param int $maxid
     * @return false|mixed
     * @throws \dml_exception
     */
    public function db_activity_session_max($maxid) {

        global $DB;

        $logsqlmax = "SELECT timecreated  FROM {logstore_standard_log}
                                            WHERE id = {$maxid}";

        return $DB->get_record_sql($logsqlmax);
    }

    /**
     * First activity access
     * @param int $firstactivitytime
     * @return false|mixed
     * @throws \dml_exception
     */
    public function db_first_activity_access($firstactivitytime) {
        global $DB;

        $sqlfirstactivityaccess = "SELECT timecreated FROM {logstore_standard_log}
                   WHERE id = {$firstactivitytime}";

        return $DB->get_record_sql($sqlfirstactivityaccess);

    }

    /**
     * Last activity access
     * @param int $lastactivitytime
     * @return false|mixed
     * @throws \dml_exception
     */
    public function db_last_activity_access($lastactivitytime) {

        global $DB;

        $sqllastactivityaccess = "SELECT timecreated FROM {logstore_standard_log}
                   WHERE id = {$lastactivitytime}";

        return $DB->get_record_sql($sqllastactivityaccess);
    }

    /**
     * Activity title
     * @param string $dbtable
     * @param int $user
     * @param int $course
     * @param int $contextinstanceid
     * @return false|mixed
     * @throws \dml_exception
     */
    public function db_activity_title($dbtable , $user , $course , $contextinstanceid) {

        global $DB;

        $sqltitle = "SELECT DISTINCT srm.name as activitytitle  FROM {logstore_standard_log} AS sdl
                    JOIN {user} AS u ON u.id = sdl.userid
                    JOIN {course} AS c ON c.id = sdl.courseid
                    JOIN {course_modules} AS cm ON cm.id = sdl.contextinstanceid
                    JOIN {modules} AS m ON m.id = cm.module
                    JOIN {$dbtable} AS srm ON srm.id = cm.instance
                    WHERE u.id = {$user} AND c.id = {$course} AND cm.id = {$contextinstanceid}";

        return $DB->get_record_sql($sqltitle);
    }

    /**
     * Quiz time and title
     * @param int $user
     * @param int $course
     * @param int $contextinstanceid
     * @return array
     * @throws \dml_exception
     */
    public function db_quiz_title_times($user , $course , $contextinstanceid) {

        global $DB;

        $sqlquiz = "SELECT  DISTINCT qza.* , qz.name as activitytitle FROM {logstore_standard_log} AS sdl
                        JOIN {user} AS u ON u.id = sdl.userid
                        JOIN {course} AS c ON c.id = sdl.courseid
                        JOIN {course_modules} AS cm ON cm.id = sdl.contextinstanceid
                        JOIN {modules} AS m ON m.id = cm.module
                        JOIN {quiz} AS qz ON qz.id =cm.instance
                        JOIN {quiz_attempts} AS qza ON qza.quiz = qz.id AND qza.userid = u.id
                        WHERE u.id = {$user} AND c.id = {$course} AND cm.id = {$contextinstanceid} AND qza.timefinish <> 0";

        return $DB->get_records_sql($sqlquiz);
    }

    /**
     * Fetch a specific row from logstore
     * @param int $standardlogid
     * @return false|mixed|\stdClass
     * @throws \dml_exception
     */
    public function db_get_logstore($standardlogid) {

        global $DB;

        return $DB->get_record('logstore_standard_log' , ['id' => $standardlogid]);

    }

    /**
     * Scorm track info
     * @param int $user
     * @param int $course
     * @param int $contextinstanceid
     * @return array
     * @throws \dml_exception
     */
    public function db_get_scorm_track($user , $course , $contextinstanceid) {

        global $DB;

        $sqltrack = "SELECT DISTINCT sst.* , srm.name as activitytitle  FROM {logstore_standard_log} AS sdl
                    JOIN {user} AS u ON u.id = sdl.userid
                    JOIN {course} AS c ON c.id = sdl.courseid
                    JOIN {course_modules} AS cm ON cm.id = sdl.contextinstanceid
                    JOIN {modules} AS m ON m.id = cm.module
                    JOIN {scorm} AS srm ON srm.id = cm.instance
                    JOIN {scorm_scoes_track} AS sst ON sst.scormid = srm.id AND sst.userid = u.id
                    WHERE sst.element = 'cmi.core.total_time'
                        AND u.id = {$user}
                        AND c.id = {$course}
                        AND cm.id = {$contextinstanceid}";

        return $DB->get_records_sql($sqltrack);

    }

    /**
     * Big Blue Button actions
     * @param int $user
     * @param int $course
     * @param string $modulename
     * @return false|mixed
     * @throws \dml_exception
     */
    public function db_bbb_action($user , $course , $modulename ) {

        global $DB;

        $sql = "SELECT sl.id , sl.timecreated , bn.name , sl.action From {logstore_standard_log} AS sl
                JOIN {context} AS c ON c.id = sl.contextid
                JOIN {course_modules} AS cm ON cm.id = c.instanceid
                JOIN {modules} AS m ON m.id = cm.module
                JOIN {bigbluebuttonbn} AS bn ON bn.id = cm.instance
                WHERE sl.userid = {$user}
                AND sl.courseid = {$course}
                AND sl.objecttable = '{$modulename}'
                AND sl.contextlevel = 70
                AND (sl.action = 'joined' OR sl.action = 'left') ORDER BY sl.timecreated";

        return $DB->get_records_sql($sql);

    }

    /**
     * Big Blue Button Between time
     * @param int $bbbstart
     * @param int $bbbfinish
     * @return array
     * @throws \dml_exception
     */
    public function db_bbb_between_time($bbbstart , $bbbfinish) {

        global $DB;

        $sqlbetweentime = " SELECT DISTINCT timecreated
            FROM {logstore_standard_log}
            WHERE timecreated BETWEEN {$bbbstart}
            AND  {$bbbfinish}";

        return $DB->get_records_sql($sqlbetweentime);

    }

    /**
     * Fetch the course of this user that is teacher.
     * @param int $userid
     * @return array
     * @throws \dml_exception
     */
    public function db_teacher_courses($userid) {

        global $DB;

        $sql = "SELECT DISTINCT c.id , c.fullname
        FROM {course} c
        JOIN {context} ctx ON c.id = ctx.instanceid
        JOIN {role_assignments} ra ON ctx.id = ra.contextid
        WHERE ra.userid = {$userid}
        AND ra.roleid = 3 OR ra.roleid = 4";
        return $DB->get_records_sql($sql);
    }

    /**
     * Fetch all courses.
     * @return array
     * @throws \dml_exception
     */
    public function db_all_courses() {

        global $DB;

        return $DB->get_records('course' , [] , 'id ASC' , 'id , fullname');

    }

    /**
     * Fetch all forums in a course.
     * @param int $courseid
     * @param int $forumid
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return array|false|mixed
     * @throws dml_exception
     */
    public function db_course_forums($courseid , $forumid = null , $searchperiod = false , $from = null , $to = null) {

        global $DB;

        $sql = "SELECT f.* FROM {forum} f
            WHERE f.course = {$courseid}";

        if ($searchperiod) {
            $sql .= " AND f.timemodified >= {$from} AND f.timemodified <= {$to}";
        }

        if (!is_null($forumid)) {

            $sql .= " AND f.id = {$forumid}";

            return $DB->get_record_sql($sql);
        }

        return $DB->get_records_sql($sql );

    }

    /**
     * How many discussions (topics) the course forums has in total.
     * @param int $courseid
     * @param int $forumid
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return array
     * @throws dml_exception
     */
    public function db_forums_topics($courseid , $forumid = null , $searchperiod = false , $from = null , $to = null) {

        global $DB;

        $sql = "SELECT fd.* FROM {forum_discussions} fd
            WHERE fd.course = {$courseid}";

        if (!is_null($forumid)) {

            $sql .= " AND fd.forum = {$forumid}";
        }

        if ($searchperiod) {
            $sql .= " AND fd.timemodified >= {$from} AND fd.timemodified <= {$to}";
        }

        return $DB->get_records_sql($sql);

    }

    /**
     * Return Discussion title
     * @param int $topicid
     * @return false|mixed|\stdClass
     * @throws dml_exception
     */
    public function  db_topic_title($topicid) {

        global $DB;

        return $DB->get_record('forum_discussions' , ['id' => $topicid] , 'name');
    }

    /**
     * How many posts the course has in its Forums.
     * @param int $courseid
     * @param int $forumid
     * @param int $topicid
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return array
     * @throws dml_exception
     */
    public function db_topics_posts($courseid , $forumid = null , $topicid = null ,
            $searchperiod = false , $from = null , $to = null) {

        global $DB;

        $sql = "SELECT fp.* FROM {forum_posts} fp
            JOIN {forum_discussions} fd ON fd.id = fp.discussion
            JOIN {forum} f ON f.id = fd.forum
            WHERE f.course = {$courseid}";

        if (!is_null($forumid) && is_null($topicid)) {

            $sql .= " AND fd.forum = {$forumid}";

        } else if (!is_null($forumid) && !is_null($topicid)) {

            $sql .= " AND fd.forum = {$forumid} AND fp.discussion = {$topicid }";

        } else {

            $sql .= '';
        }

        if ($searchperiod) {
            $sql .= " AND fp.modified >= {$from} AND fp.modified <= {$to}";
        }

        return $DB->get_records_sql($sql);

    }

    /**
     * If posts are more than one in a topic is active and already initialized.
     * @param int $courseid
     * @param array $forums
     * @param int $forumid
     * @param int $topicid
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return int
     * @throws dml_exception
     */
    public function db_topics_initialized($courseid , $forums = null , $forumid = null ,
            $topicid = null , $searchperiod = false , $from = null , $to = null) {

        $countinitialized = 0;

        if (!is_null($forums)) {
            foreach ($forums as $forum) {

                // Total in all forums in course.
                $countinitialized += (count($this->db_topics_posts($courseid , $forum->id ,
                                null , $searchperiod , $from  , $to)) > 1) ? 1 : 0;

            }
        } else if (is_null($forums) && !is_null($forumid) && is_null($topicid)) {

            // Specific forum in course.
            $countinitialized += (count($this->db_topics_posts($courseid , $forumid ,
                            null , $searchperiod , $from  , $to)) > 1) ? 1 : 0;

        } else {

            // Specific topic in forum.
            $countinitialized += (count($this->db_topics_posts($courseid , $forumid , $topicid ,
                             $searchperiod , $from  , $to)) > 1) ? 1 : 0;
        }

        return $countinitialized;

    }

    /**
     * Return post reads in a topic
     * @param int $forumid
     * @param int $topicid
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return array
     * @throws dml_exception
     */
    public function db_topic_post_reads($forumid , $topicid , $searchperiod = false , $from = null , $to = null) {

        global $DB;

        $sql = "SELECT fr.* FROM {forum_read} fr
                    JOIN {forum} f ON f.id = fr.forumid
                    JOIN {forum_discussions} fd ON fd.id = fr.discussionid
                    JOIN {forum_posts} fp ON fp.id = fr.postid
                    WHERE fr.discussionid = {$topicid}
                        AND fr.forumid = {$forumid}";

        if ($searchperiod) {
            $sql .= " AND fp.modified >= {$from} AND fp.modified <= {$to}";
        }

        return $DB->get_records_sql($sql);
    }

    /**
     * Return subscribed users in a forum.
     * @param int $forumid
     * @return array
     * @throws dml_exception
     */
    public function db_forum_subscriptions($forumid) {

        global $DB;

        $sql = " SELECT fs.* , u.lastname , u.firstname
        FROM {forum_subscriptions} fs
        JOIN {forum} f ON f.id = fs.forum
        JOIN {user} u ON u.id = fs.userid
        WHERE fs.forum ={$forumid}";

        return $DB->get_records_sql($sql);

    }

    /**
     * Return users posts in a topic
     * @param int $userid
     * @param int $courseid
     * @param int $forumid
     * @param int $topicid
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return array
     * @throws dml_exception
     */
    public function db_topic_user_posts($userid , $courseid , $forumid , $topicid ,
            $searchperiod = false , $from = null , $to = null) {

        global $DB;

        $sql = "SELECT fp.* FROM {forum_posts} fp
            JOIN {forum_discussions} fd ON fd.id = fp.discussion
            JOIN {forum} f ON f.id = fd.forum
            WHERE f.course = {$courseid} AND fp.userid = {$userid}
                AND fd.forum = {$forumid} AND fp.discussion = {$topicid} ";

        if ($searchperiod) {
            $sql .= " AND fp.modified >= {$from} AND fp.modified <= {$to}";
        }

        return $DB->get_records_sql($sql);
    }

    /**
     * Return posts of user that have a parent discussions
     * @param int $userid
     * @param int $courseid
     * @param int $forumid
     * @param int $topicid
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return array
     * @throws dml_exception
     */
    public function db_topic_user_answers($userid , $courseid , $forumid , $topicid ,
            $searchperiod = false , $from = null , $to = null) {

        global $DB;

        $sql = "SELECT fp.* FROM {forum_posts} fp
                    JOIN {forum_discussions} fd ON fd.id = fp.discussion
                    JOIN {forum} f ON f.id = fd.forum
                     WHERE f.course = {$courseid} AND fp.userid = {$userid}
                        AND fd.forum = {$forumid} AND fp.discussion = {$topicid}
                        AND fp.parent <> 0";

        if ($searchperiod) {
            $sql .= " AND fp.modified >= {$from} AND fp.modified <= {$to}";
        }

        return $DB->get_records_sql($sql);

    }

    /**
     * return user reads in a topic
     * @param int $userid
     * @param int $forumid
     * @param int $topicid
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return array
     * @throws dml_exception
     */
    public function db_user_post_reads($userid , $forumid , $topicid ,
            $searchperiod = false , $from = null , $to = null) {

        global $DB;

        $sql = "SELECT fr.* FROM {forum_read} fr
                    JOIN {forum} f ON f.id = fr.forumid
                    JOIN {forum_discussions} fd ON fd.id = fr.discussionid
                    JOIN {forum_posts} fp ON fp.id = fr.postid
                    WHERE fr.userid = {$userid}
                        AND fr.discussionid = {$topicid}
                        AND fr.forumid = {$forumid}";

        if ($searchperiod) {
            $sql .= " AND fp.modified >= {$from} AND fp.modified <= {$to}";
        }

        return $DB->get_records_sql($sql);
    }

    /**
     * Find Quizzes in each course that we look.
     * @param int $courseid
     * @param int $quizid
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return array|false|mixed
     * @throws dml_exception
     */
    public function db_course_quizzes($courseid , $quizid = null , $searchperiod = false , $from = null , $to = null) {

        global $DB;

        $sql = "SELECT q.* FROM {quiz} q
            WHERE q.course = {$courseid}";

        if ($searchperiod) {

            $sql .= " AND q.timemodified >= {$from} AND q.timemodified <= {$to}";

        }

        if (!is_null($quizid)) {

            $sql .= " AND q.id = {$quizid}";

            return $DB->get_record_sql($sql);
        }

        return $DB->get_records_sql($sql);
    }

    /**
     * In these quizzes what is the total time of the users in it.
     * @param array $quizzes
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return array
     * @throws dml_exception
     */
    public function db_users_quizzes_total_time($quizzes , $searchperiod = false , $from = null , $to = null) {

        global $DB;

        $totaltime = 0;
        $totalattempts = 0;

        foreach ($quizzes as $quiz) {

            // Retrieve quiz attempts.
            $sql = "SELECT qa.* FROM {quiz_attempts} qa
            WHERE qa.quiz = {$quiz->id} ";

            if ($searchperiod) {

                $sql .= " AND qa.timemodified >= {$from} AND qa.timemodified <= {$to}";

            }

            $quizattempts = $DB->get_records_sql($sql);

            foreach ($quizattempts as $attempt) {

                // Calculate time spent in seconds.
                $starttime = $attempt->timestart;
                $endtime = $attempt->timefinish;

                if ($starttime > 0 && $endtime > 0) {
                    $timespent = $endtime - $starttime;
                    $totaltime += $timespent;
                }
            }

            $totalattempts += count( $quizattempts);

        }

        $result = [
                'totaltime' => $totaltime,
                'totalattempts' => $totalattempts
        ];

        return $result;

    }

    /**
     * Calculate the avg score of users in these quizzes.
     * @param array $quizzes
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return float|int
     * @throws dml_exception
     */
    public function db_avg_users_score($quizzes , $searchperiod = false , $from = null , $to = null) {

        global $DB;

        $totalscore = 0;
        $totalattempts = 0;

        foreach ($quizzes as $quiz) {

            // Retrieve quiz grades for the course.
            $sql = "SELECT qg.* FROM {quiz_grades} qg
            WHERE qg.quiz = {$quiz->id} ";

            if ($searchperiod) {

                $sql .= " AND qg.timemodified >= {$from} AND qg.timemodified <= {$to}";

            }

            $quizgrades = $DB->get_records_sql( $sql );

            foreach ($quizgrades as $grade) {
                // Sum up grades.
                $totalscore += $grade->grade;

            }
            $totalattempts += count($quizgrades);
        }

        // Calculate average score.
        $averagescore = ($totalattempts > 0) ? $totalscore / $totalattempts : 0;

        return $averagescore;
    }

    /**
     * In this quiz what is the total time of the users in it.
     * @param int $quizid
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return array
     * @throws dml_exception
     */
    public function db_users_quiz_total_time($quizid , $searchperiod = false , $from = null , $to = null) {

        global $DB;

        $totaltime = 0;

        // Retrieve quiz attempts.

        $sql = "SELECT qa.* FROM {quiz_attempts} qa
            WHERE qa.quiz = {$quizid} ";

        if ($searchperiod) {

            $sql .= " AND qa.timemodified >= {$from} AND qa.timemodified <= {$to}";

        }

        $quizattempts = $DB->get_records_sql($sql);

        foreach ($quizattempts as $attempt) {

            // Calculate time spent in seconds.
            $starttime = $attempt->timestart;
            $endtime = $attempt->timefinish;

            if ($starttime > 0 && $endtime > 0) {
                $timespent = $endtime - $starttime;
                $totaltime += $timespent;
            }
        }

        $result = [
                'totaltime' => $totaltime,
                'totalattempts' => count($quizattempts)
        ];

        return $result;
    }

    /**
     * Find users results of this quiz in the course.
     * @param int $courseid
     * @param int $quizid
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return array
     * @throws dml_exception
     */
    public function db_users_quiz_attempts($courseid , $quizid , $searchperiod = false , $from = null , $to = null) {

        global $DB;

        $userquizdata = [];
        $totalattempts = 1;

        // Retrieve quiz attempts for the specified quiz.
        $sql = "SELECT qa.* , q.name , u.lastname , u.firstname , cm.id as cmid
                FROM {quiz_attempts} qa
                JOIN {quiz} q ON q.id = qa.quiz
                JOIN {user} u ON u.id = qa.userid
                JOIN {course_modules} cm ON cm.instance = q.id
                WHERE qa.quiz = {$quizid}
                    AND cm.course = {$courseid}
                    AND qa.timefinish <> 0";

        if ($searchperiod) {

            $sql .= " AND qa.timemodified >= {$from} AND qa.timemodified <= {$to}";

        }

        $userresults = $DB->get_records_sql($sql);

        foreach ($userresults as $result) {
            $userid = $result->userid;
            $timespent = $result->timefinish - $result->timestart;

            // Store or update user's data.
            if (!isset($userquizdata[$userid])) {
                $userquizdata[$userid] = [
                        'lastname' => $result->lastname,
                        'firstname' => $result->firstname,
                        'quiz' => $result->name,
                        'attempt' => $result->id,
                        'cmid' => $result->cmid,
                        'totaltime' => $timespent,
                        'totalattempts' => $totalattempts
                ];
            } else {
                $userquizdata[$userid]['totaltime'] += $timespent;
                $userquizdata[$userid]['totalattempts'] += $totalattempts;
            }
        }

        return $userquizdata;

    }

    /**
     * Calculate the avg score of users in this quiz.
     * @param int $quizid
     * @param int $userid
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return float|int
     * @throws dml_exception
     */
    public function db_avg_users_quiz_score($quizid , $userid = null , $searchperiod = false , $from = null , $to = null) {

        global $DB;

        $totalscore = 0;
        $totalattempts = 0;

        // Retrieve quiz grades for the course.
        if (is_null($userid)) {

            // Retrieve quiz grades for the course.
            $sql = "SELECT qg.* FROM {quiz_grades} qg
            WHERE qg.quiz = {$quizid} ";

            if ($searchperiod) {

                $sql .= " AND qg.timemodified >= {$from} AND qg.timemodified <= {$to}";

            }

            $quizgrades = $DB->get_records_sql($sql);

        } else {

            // Retrieve quiz grades for the course.
            $sql = "SELECT qg.* FROM {quiz_grades} qg
            WHERE qg.quiz = {$quizid} AND qg.userid = {$userid} ";

            if ($searchperiod) {

                $sql .= " AND qg.timemodified >= {$from} AND qg.timemodified <= {$to}";

            }

            $quizgrades = $DB->get_records_sql($sql);

        }
        foreach ($quizgrades as $grade) {

            // Sum up grades.
            $totalscore += $grade->grade;
            $totalattempts++;

        }

        // Calculate average score.
        $averagescore = ($totalattempts > 0) ? $totalscore / $totalattempts : 0;

        return $averagescore;

    }


}

