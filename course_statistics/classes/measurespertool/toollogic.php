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
 * @package    block_course_statistics
 * @copyright 2023 onwards WIDE Services  {@link https://www.wideservices.gr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_course_statistics\measurespertool;


use block_course_statistics\dbquery;
use block_course_statistics\utils\utils;
use context_course;

/**
 * Class main
 */
class toollogic implements logic_interface {

    /**
     * Construct
     */
    public function __construct() {
    }
    /**
     * Course title
     * @param int $courseid
     * @return mixed
     * @throws \dml_exception
     */
    public function course_title($courseid) {
        $dbquery = new dbquery();

        return $dbquery->db_course_title($courseid)->fullname;
    }

    /**
     * This method calculates all session times in activity modules
     * for each enrolled user and saves it in an array with a userid
     * that indicated whose session time it is.
     * A user may have many sessions in a course
     * @param array $enrolledusers
     * @param int $courseid
     * @return void
     * @throws \dml_exception
     */
    public function get_enrolled_users_sessions($enrolledusers , $courseid) {

        $scheduledtime = null;

        $dbquery = new dbquery();

        foreach ($enrolledusers as $enrolleduser) {

            // Find the last session time of this user in this course!

            $scheduled = $dbquery->db_find_scheduled_time($courseid , $enrolleduser->id);

            if (!empty($scheduled)) {
                foreach ($scheduled as $sc) {
                    $scheduledtime = $sc->endsession;
                }
            }

            // The scheduledtime param is for the schedule task to retrieve data.
            // From logstore from that time and after and not from the beginning of logstore table again every day.
            // The schedule task the 1st time will be very slow cause will try to do calculation for all users.
            // From all the logstore table and find the activity sessions from the beginning which is wrong.
            $activitysessions = $this->calculate_user_activity_session_time($courseid , $enrolleduser->id , $scheduledtime);

            $insertdata = array();
            $insertactivitysessions = array();
            $insertbbbsessions = array();

            foreach ($activitysessions as $instance => $row) {

                $insertactivity = new \stdClass();
                $insertactivity->courseid = $courseid;
                $insertactivity->userid = $enrolleduser->id;
                $insertactivity->activity = $row->module;
                $insertactivity->cminstance = $instance;
                $insertactivity->activitytitle = $row->activitytitle;
                $insertactivity->activitytime = $row->activitytime;
                $insertactivity->activitysessions = (isset($row->activitysessions) && !empty($row->activitysessions)) ?
                        $row->activitysessions : 0;
                $insertdata[] = $insertactivity;

                // For this module capture separately the start , end and total time of activity sessions.
                if (isset($row->activitysessiontime) && !is_null($row->activitysessiontime) || !empty($row->activitysessiontime)) {
                    $insertactivitysessions[$instance] = $row->activitysessiontime;
                }
            }

            // Insert records to DB once for every user.
            $dbquery->db_insert_multidata('cs_user_activity_sessions' , $insertdata , $insertactivitysessions , $insertbbbsessions);

        }

    }

    /**
     * Calculate activity time for a user
     * @param int $courseid
     * @param int $userid
     * @param int $scheduledtime
     * @return array
     * @throws \dml_exception
     */
    public function calculate_user_activity_session_time($courseid , $userid , $scheduledtime = null) {

        $dbquery = new dbquery();

        $and = (!is_null($scheduledtime)) ? ' AND timecreated > '.$scheduledtime : '';

        $where = 'courseid = :courseid AND userid = :userid AND contextlevel = 70 '.$and;

        $params = array(
                    'courseid' => $courseid,
                    'userid' => $userid
            );
        $logs = utils::get_activity_events_select($where, $params);

        $rows = array();

        $packages = array();

        if ($logs) {

            $mods = get_course_mods($courseid);

            foreach ($mods as $cm) {
                $i = 0;
                foreach ($logs as $log) {

                    if ($cm->id == $log->instanceid) {

                        $packages[$log->instanceid][$i] = $log->id;
                        $i++;

                    }

                }

            }

            foreach ($packages as $instance => $package) {

                $module = $dbquery->db_specific_module($instance);

                if ($module->name == "forum") {

                    $chartcolour = '#FF5733';
                    $activitytitle = utils::get_module_activity_title(
                            $module->name , $package , $userid , $courseid
                    );
                    $rows[$instance] = utils::get_module_activity_time(
                            $module->name , $activitytitle ,  $package , $userid , $courseid , $chartcolour
                    );

                } else if ($module->name == "book") {

                    $chartcolour = '#FFB333';
                    $activitytitle = utils::get_module_activity_title(
                            $module->name , $package , $userid , $courseid
                    );

                    $rows[$instance] = utils::get_module_activity_time(
                            $module->name , $activitytitle ,  $package , $userid , $courseid , $chartcolour
                    );

                } else if ($module->name == "appointment") {

                    $chartcolour = '#BAFF33';
                    $activitytitle = utils::get_module_activity_title(
                            $module->name , $package , $userid , $courseid
                    );
                    $rows[$instance] = utils::get_module_activity_time(
                            $module->name , $activitytitle ,  $package , $userid , $courseid , $chartcolour
                    );

                } else if ($module->name == "assign") {

                    $chartcolour = '#50FF33';
                    $activitytitle = utils::get_module_activity_title(
                            $module->name , $package , $userid , $courseid
                    );
                    $rows[$instance] = utils::get_module_activity_time(
                            $module->name , $activitytitle ,  $package , $userid , $courseid , $chartcolour
                    );

                } else if ($module->name == "assignment") {

                    $chartcolour = '#30DA6D';
                    $activitytitle = utils::get_module_activity_title(
                            $module->name , $package , $userid , $courseid
                    );
                    $rows[$instance] = utils::get_module_activity_time(
                            $module->name , $activitytitle ,  $package , $userid , $courseid , $chartcolour
                    );

                } else if ($module->name == "chat") {

                    $chartcolour = '#30DAB9';
                    $activitytitle = utils::get_module_activity_title(
                            $module->name , $package , $userid , $courseid
                    );
                    $rows[$instance] = utils::get_module_activity_time(
                            $module->name , $activitytitle ,  $package , $userid , $courseid , $chartcolour
                    );

                } else if ($module->name == "choice") {

                    $chartcolour = '#24BECE ';
                    $activitytitle = utils::get_module_activity_title(
                            $module->name , $package , $userid , $courseid
                    );
                    $rows[$instance] = utils::get_module_activity_time(
                            $module->name , $activitytitle ,  $package , $userid , $courseid , $chartcolour
                    );

                } else if ($module->name == "coursecertificate") {

                    $chartcolour = '#2489CE';
                    $activitytitle = utils::get_module_activity_title(
                            $module->name , $package , $userid , $courseid
                    );
                    $rows[$instance] = utils::get_module_activity_time(
                            $module->name , $activitytitle ,  $package , $userid , $courseid , $chartcolour
                    );

                } else if ($module->name == "data") {

                    $chartcolour = '#245DCE';
                    $activitytitle = utils::get_module_activity_title(
                            $module->name , $package , $userid , $courseid
                    );
                    $rows[$instance] = utils::get_module_activity_time(
                            $module->name , $activitytitle ,  $package , $userid , $courseid , $chartcolour
                    );

                } else if ($module->name == "feedback") {

                    $chartcolour = '#6D68B6';
                    $activitytitle = utils::get_module_activity_title(
                            $module->name , $package , $userid , $courseid
                    );
                    $rows[$instance] = utils::get_module_activity_time(
                            $module->name , $activitytitle ,  $package , $userid , $courseid , $chartcolour
                    );

                } else if ($module->name == "folder") {

                    $chartcolour = '#764CAE';
                    $activitytitle = utils::get_module_activity_title(
                            $module->name , $package , $userid , $courseid
                    );
                    $rows[$instance] = utils::get_module_activity_time(
                            $module->name , $activitytitle ,  $package , $userid , $courseid , $chartcolour
                    );

                } else if ($module->name == "glossary") {

                    $chartcolour = '#AD6BCB';
                    $activitytitle = utils::get_module_activity_title(
                            $module->name , $package , $userid , $courseid
                    );
                    $rows[$instance] = utils::get_module_activity_time(
                            $module->name , $activitytitle ,  $package , $userid , $courseid , $chartcolour
                    );

                } else if ($module->name == "h5pactivity") {

                    $chartcolour = '#2BB99D';
                    $activitytitle = utils::get_module_activity_title(
                            $module->name , $package , $userid , $courseid
                    );

                    $rows[$instance] = utils::get_module_activity_time(
                            $module->name , $activitytitle ,  $package , $userid , $courseid , $chartcolour
                    );

                } else if ($module->name == "imscp") {

                    $chartcolour = '#737A10';
                    $activitytitle = utils::get_module_activity_title(
                            $module->name , $package , $userid , $courseid
                    );
                    $rows[$instance] = utils::get_module_activity_time(
                            $module->name , $activitytitle ,  $package , $userid , $courseid , $chartcolour
                    );

                } else if ($module->name == "label") {

                    $chartcolour = '#06D830';
                    $activitytitle = utils::get_module_activity_title(
                            $module->name , $package , $userid , $courseid
                    );
                    $rows[$instance] = utils::get_module_activity_time(
                            $module->name , $activitytitle ,  $package , $userid , $courseid , $chartcolour
                    );

                } else if ($module->name == "lesson") {

                    $chartcolour = '#E5C799';
                    $activitytitle = utils::get_module_activity_title(
                            $module->name , $package , $userid , $courseid
                    );
                    $rows[$instance] = utils::get_module_activity_time(
                            $module->name , $activitytitle ,  $package , $userid , $courseid , $chartcolour
                    );

                } else if ($module->name == "lti") {

                    $chartcolour = '#C0C77B ';
                    $activitytitle = utils::get_module_activity_title(
                            $module->name , $package , $userid , $courseid
                    );
                    $rows[$instance] = utils::get_module_activity_time(
                            $module->name , $activitytitle ,  $package , $userid , $courseid , $chartcolour
                    );

                } else if ($module->name == "page") {

                    $chartcolour = '#80E7B5 ';
                    $activitytitle = utils::get_module_activity_title(
                            $module->name , $package , $userid , $courseid
                    );
                    $rows[$instance] = utils::get_module_activity_time(
                            $module->name , $activitytitle ,  $package , $userid , $courseid , $chartcolour
                    );

                } else if ($module->name == "resource") {

                    $chartcolour = '#E9C4AE ';
                    $activitytitle = utils::get_module_activity_title(
                            $module->name , $package , $userid , $courseid
                    );
                    $rows[$instance] = utils::get_module_activity_time(
                            $module->name , $activitytitle ,  $package , $userid , $courseid , $chartcolour
                    );

                } else if ($module->name == "survey") {

                    $chartcolour = '#B7F843 ';
                    $activitytitle = utils::get_module_activity_title(
                            $module->name , $package , $userid , $courseid
                    );
                    $rows[$instance] = utils::get_module_activity_time(
                            $module->name , $activitytitle ,  $package , $userid , $courseid , $chartcolour
                    );

                } else if ($module->name == "url") {

                    $chartcolour = '#2382E5 ';
                    $activitytitle = utils::get_module_activity_title(
                            $module->name , $package , $userid , $courseid
                    );
                    $rows[$instance] = utils::get_module_activity_time(
                            $module->name , $activitytitle ,  $package , $userid , $courseid , $chartcolour
                    );

                } else if ($module->name == "wiki") {

                    $chartcolour = '#74E34A ';
                    $activitytitle = utils::get_module_activity_title(
                            $module->name , $package , $userid , $courseid
                    );
                    $rows[$instance] = utils::get_module_activity_time(
                            $module->name , $activitytitle ,  $package , $userid , $courseid , $chartcolour
                    );

                } else if ($module->name == "workshop") {

                    $chartcolour = '#EE63B7 ';
                    $activitytitle = utils::get_module_activity_title(
                            $module->name , $package , $userid , $courseid
                    );
                    $rows[$instance] = utils::get_module_activity_time(
                            $module->name , $activitytitle , $package , $userid , $courseid , $chartcolour
                    );

                } else if ($module->name == "quiz") {

                    $chartcolour = '#FFE333';
                    // Session.
                    $activitytitle = utils::get_module_activity_title(
                            $module->name , $package , $userid , $courseid
                    );

                    $rows[$instance] = utils::get_module_activity_time(
                            $module->name , $activitytitle , $package , $userid , $courseid , $chartcolour
                    );
                } else if ($module->name == "scorm") {

                    $chartcolour = '#AD63EE ';

                    // Session.
                    $activitytitle = utils::get_module_activity_title(
                            $module->name , $package , $userid , $courseid
                    );

                    $rows[$instance] = utils::get_module_activity_time(
                            $module->name , $activitytitle , $package , $userid , $courseid , $chartcolour
                    );

                } else if ($module->name == "bigbluebuttonbn") {

                    $chartcolour = '#107a67';

                    $activitytitle = utils::get_module_activity_title(
                            $module->name , $package , $userid , $courseid
                    );

                    $rows[$instance] = utils::get_module_bigbluebuttonbn_time(
                            $module->name , $activitytitle,  $userid , $courseid , $chartcolour
                    );
                } else {
                    $chartcolour = '#107a00';

                    $activitytitle = utils::get_module_activity_title(
                            $module->name , $package , $userid , $courseid
                    );
                    $rows[$instance] = utils::get_module_activity_time(
                            $module->name , $activitytitle , $package , $userid , $courseid , $chartcolour
                    );
                }

            }

        }
        return $rows;
    }

    /**
     * Group all info of users in activities.
     * @param int $courseid
     * @param int $cminstance
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return array
     * @throws \dml_exception
     */
    public function group_viewusers_data($courseid , $cminstance , $searchperiod = false , $from = null , $to = null) {

        $dbquery = new dbquery();

        $results = $dbquery->db_users_measures_in_activity($courseid , $cminstance , $searchperiod , $from , $to);
        $measures = array();
        $usersdata = array();

        if (!empty($results)) {

            foreach ($results as $res) {

                // Find all course sessions and divide the specific activity session.
                $info = $dbquery->db_user_course_data($res->userid , $courseid , $searchperiod , $from , $to);

                $avgtimesession = (isset($res->activitysessions) && $res->activitysessions != 0) ?
                        $res->activitytime / $res->activitysessions : 0;

                $avgusesession = (isset($res->activitysessions) &&  $res->activitysessions != 0 && $info->sessions != 0) ?
                        number_format(($res->activitysessions / $info->sessions) * 100 , 1) : 0;

                $data = [
                        'firstname' => $res->firstname,
                        'lastname' => $res->lastname,
                        'coursetitle' => $this->course_title($courseid),
                        'activity' => $res->activity,
                        'activitytime' => utils::format_activitytime($res->activitytime),
                        'activitysessions' => $res->activitysessions,
                        'avgtimesession' => utils::format_activitytime($avgtimesession),
                        'avgusesession' => number_format($avgusesession  , 1).'%',
                ];
                $usersdata[] = $data;
            }
        }

        $measures['usersdata'] = $usersdata;

        return $measures;
    }

    /**
     * Group all info of course activities measures.
     * @param int $courseid
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return array
     * @throws \dml_exception
     */
    public function group_activities_measures_data($courseid , $searchperiod = false , $from = null , $to = null) {

        $dbquery = new dbquery();

        $measures = array();

        // Find the statistics For each activity/module in Course.
        $coursemodules = get_course_mods($courseid);

        foreach ($coursemodules as $module) {
                $info = $dbquery->db_course_activities_data($module->course , $module->id , $searchperiod , $from , $to);
                $data = [
                    'coursetitle' => $this->course_title($courseid),
                    'activity' => $info->activity,
                    'cminstance' => $module->id,
                    'activitytotaltime' => utils::format_activitytime($info->totalactivitytime),
                    'activitytotalsessions' => $info->totalactivitysessions,
                    'activityavgtime' => utils::format_activitytime($info->totalactivityavgtime),
                    'averageusedinsessions' => number_format($info->averageusedinsessions  , 1).'%',
                ];
                $activitiesdata[] = $data;
        }
        $measures['activitiesdata'] = $activitiesdata;
        return $measures;
    }

    /**
     * Group all info for template
     * @param int $courseid
     * @param bool $isteacher
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return array
     * @throws \dml_exception
     */
    public function group_courses_tools_data($courseid , $isteacher , $searchperiod = false , $from = null , $to = null) {

        global $USER;

        $dbquery = new dbquery();

        $measures = array();

        $generaldata = array();
        // If user is teacher find the course that is teacher and calculate measures.
        // Else is admin and measure all courses.

        $courses = ($isteacher) ? $dbquery->db_teacher_courses($USER->id) : $dbquery->db_all_courses();

        foreach ($courses as $course) {
            $coursedata = array();
            $enrolledusers = get_enrolled_users(context_course::instance($course->id));
            // How many modules the course has?
            $coursemodules = get_course_mods($course->id);
            // Course Data.
            foreach ($enrolledusers as $enrolleduser) {
                $info = $dbquery->db_course_tools_data($enrolleduser->id , $course->id , $searchperiod  , $from , $to);
                $data = [
                        'totaltime' => $info->totalactivitiestime,
                        'totalsessions' => $info->totalactivitiessessions,
                ];
                $coursedata[] = $data;
            }
            $totalsessions = 0;
            $totaltime = 0;
            // General Data.
            foreach ($coursedata as $thiscourse) {
                $totalsessions += $thiscourse["totalsessions"];
                $totaltime += $thiscourse["totaltime"];
            }
            $data = [
                    'courseid' => $course->id,
                    'coursetitle' => $this->course_title($course->id),
                    'activities' => count($coursemodules),
                    'activitiestotaltime' => utils::format_activitytime($totaltime),
                    'activitiessessions' => $totalsessions,
                    'activitiesavgtime' => ($totaltime != 0 || $totalsessions != 0) ?
                            utils::format_activitytime($totaltime / $totalsessions) : 0,
            ];
            $generaldata[] = $data;
        }
        $measures['generaldata'] = $generaldata;
        return $measures;
    }

}

