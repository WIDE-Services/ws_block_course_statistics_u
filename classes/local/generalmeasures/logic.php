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
namespace block_course_statistics\local\generalmeasures;

use block_course_statistics\local\dbquery;
use block_course_statistics\local\utils\utils;
use context_course;

/**
 * Class main
 */
class logic implements logic_interface {

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
     * This method calculates all session times in course
     * for each enrolled user and saves it in an array with a userid
     * that indicated whose session time it is.
     * A user may have many sessions in a course
     *
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

            // The scheduledtime param is when the schedule task running to retrieve data
            // From logstore from that time and after and not from all the logstore table again every day.
            // The schedule task the 1st time will be very slow cause will try to do calculation for all users.
            // From all the logstore table.
            $this->calculate_user_course_session_time($courseid , $enrolleduser->id , $scheduledtime);

        }

    }

    /**
     * Calculate_user_course_session_time
     * @param int $courseid
     * @param int $userid
     * @param int $scheduledtime
     * @return mixed|void
     * @throws \dml_exception
     */
    public function calculate_user_course_session_time($courseid , $userid , $scheduledtime = null) {

        $dbquery = new dbquery();
        $sessiontimeout = get_config('' , 'sessiontimeout');

        // We need to find the contextlevels saved in logstore from the user.
        // When was in the course. The context levels that we need for course are :
        // CONTEXT_COURSE (50) and CONTEXT_MODULE (70).

        $coursesessions = $dbquery->db_course_sessions($courseid , $userid , $scheduledtime);

        $i = 0;
        $packages = [];
        foreach ($coursesessions as $key => $session) {

                $packages[$i] = $key;
                $i++;

        }
        // Below im grouping the consecutives ids from logstore.
        // And i assume its consecutive group is a course session.

        $consecutives = [];
        $previous = null;
        $i = 0;

        foreach ($packages as $value) {

            if ($previous !== null && $value == $previous - 1) {

                // If two consecutive rows are exactly the same case event action : viewed - target : course.
                // Then we have separate sessions.

                if (($coursesessions[$value]->action == 'viewed' && $coursesessions[$previous]->action == 'viewed')
                        && ($coursesessions[$value]->target == 'course' && $coursesessions[$previous]->target == 'course')) {

                    $i++;
                    $consecutives[$i][$value] = $value;
                } else if ($coursesessions[$previous]->action == 'viewed' && $coursesessions[$previous]->target == 'course') {

                    $i++;
                    $consecutives[$i][$value] = $value;
                } else {

                    $consecutives[$i][$value] = $value;

                }

            } else if ($previous == null) {

                $consecutives[$i][$value] = $value;

            } else if ($previous !== null && $value != $previous - 1) {

                if ($coursesessions[$previous]->action != 'viewed' && $coursesessions[$previous]->target != 'course') {
                    // In that case the consecutive ids where interrupted from a system entrance (-1).
                    // Most possible graded action or admin loggedin as.
                    $consecutives[$i][$value] = $value;

                } else {

                    $i++;
                    $consecutives[$i][$value] = $value;

                }

            } else {

                $i++;
            }

            $previous = $value;

        }
        // From the consecutive array i need to find the min and max value.
        // The min value is the start session and the max is NOT the end Session!
        // For the end session i need to find the next user action did outside the course.
        // After the MAX value of the group session!
        $totalsessiontime = [];
        $previous = null;

        foreach ($consecutives as $key => $consecutive) {

            $sessionpart['min'][$key] = min($consecutive);
            $sessionpart['max'][$key] = max($consecutive);

            // Insert session to plugin db table for backup.
            $insert = new \stdClass();
            // Find user next move after max session value! When user exit the session.
            $nextaction = $dbquery->db_user_exit_session($userid , $courseid ,
                    $coursesessions[$sessionpart['max'][$key]]->timecreated);

            if (!empty($nextaction)) {

                // Unless we have same rows! case event action : viewed - target : course.
                if ($previous !== null &&
                        ($coursesessions[$sessionpart['max'][$key]]->action == $coursesessions[$previous]->action) == 'viewed' &&
                        ($coursesessions[$sessionpart['max'][$key]]->target == $coursesessions[$previous]->target) == 'course' &&
                        $sessionpart['max'][$key] == $previous - 1) {

                    // Check if the diff between them is more than the sessiontimeout if yes then dont calculate.
                    // If it is then something went wrong and dont calculate.
                    // In this case the user might have forgotten the course opened or pc went in sleep mode.
                    // And the user enters again the other day this null/forgotten time will ot be calculated.
                    // OR the logstore failed to save the next action and the system fouls me and i get the next - next action!
                    // Thats why i made a check for the next action out of course to be in 2 hours period.

                    $checktime = (int)$coursesessions[$previous]->timecreated -
                            (int)$coursesessions[$sessionpart['max'][$key]]->timecreated;

                    if ($checktime < $sessiontimeout) {
                        $totalsessiontime[$key] = $checktime;

                            $insert->endsession = (int)$coursesessions[$previous]->timecreated;
                    }

                } else {
                    // Check if the diff between them is more than 8 hours (28800 secs) if yes then dont calculate.
                    // If it is then something went wrong and dont calculate.
                    // In this case the user might have forgotten the course opened or pc went in sleep mode.
                    // And the user enters again the other day this null/forgotten time will ot be calculated.
                    // OR the logstore failed to save the next action and the system fouls me and i get the next - next action!
                    // Thats why i made a check for the next action out of course to be in 2 hours period.

                    $checktime = (int)$nextaction->timecreated - (int)$coursesessions[$sessionpart['max'][$key]]->timecreated;

                    if ($checktime < $sessiontimeout) {

                        $totalsessiontime[$key] = $checktime;

                        $insert->endsession = (int)$nextaction->timecreated;

                    }

                }

            } else if ($previous == null && !empty($nextaction)) {

                $totalsessiontime[$key] = (int)$nextaction->timecreated -
                        (int)$coursesessions[$sessionpart['min'][$key]]->timecreated;

                    $insert->endsession = (int)$nextaction->timecreated;
            } else {

                // In case we don't have next action from the user.
                $totalsessiontime[$key] = (int) $coursesessions[$sessionpart['max'][$key]]->timecreated -
                        (int) $coursesessions[$sessionpart['min'][$key]]->timecreated;

                $insert->endsession = (int) $coursesessions[$sessionpart['max'][$key]]->timecreated;
            }

            $previous = $sessionpart['max'][$key];

            $insert->userid = $userid;
            $insert->courseid = $courseid;
            $insert->startsession = (int)$coursesessions[$sessionpart['min'][$key]]->timecreated;
            $insert->sessiontime = (isset($totalsessiontime[$key])) ? $totalsessiontime[$key] : 0;
            $insert->actions = count($consecutive);

            // Insert Session Info.
             $dbquery->db_insert_data('block_course_statistics_cses' , $insert);

        }

    }

    /**
     * Group all measures for courses.
     * @param int $courseid
     * @param bool $isteacher
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return array
     * @throws \dml_exception
     */
    public function group_courses_measures_data($courseid , $isteacher , $searchperiod = false , $from = null , $to = null) {

        $dbquery = new dbquery();

        $measures = [];
        $coursedata = [];

        $enrolledusers = get_enrolled_users(context_course::instance($courseid));
        $userdata = [];
        // User Data.
        foreach ($enrolledusers as $enrolleduser) {
            $info = $dbquery->db_user_course_data($enrolleduser->id , $courseid , $searchperiod , $from , $to);
            $udata = [
                    'userid' => $enrolleduser->id,
                    'lastname' => $enrolleduser->lastname,
                    'firstname' => $enrolleduser->firstname,
                    'coursetitle' => $this->course_title($courseid),
                    'totaltimeformated' => utils::format_activitytime($info->totaltime),
                    'totaltime' => $info->totaltime,
                    'totalsessions' => $info->sessions,
                    'avgtimesessionformated' => utils::format_activitytime($info->avgsessiontime),
                    'avgtimesession' => $info->avgsessiontime,
                    'numberofactions' => $info->totalactions,
                    'avgnumberofactions' => number_format($info->avgsessionactions , 1),

            ];

            $userdata[] = $udata;

        }

        $totalsessions = 0;
        $totaltime = 0;
        $totalactions = 0;

        foreach ($userdata as $user) {
            $totalsessions += $user["totalsessions"];
            $totaltime += $user["totaltime"];
            $totalactions += $user["numberofactions"];
        }
        $cdata = [
                'courseid' => $courseid,
                'coursetitle' => $this->course_title($courseid),
                'totaltime' => utils::format_activitytime($totaltime),
                'numtotaltime' => $totaltime,
                'totalsessions' => $totalsessions,
                'avgtimesession' => ($totaltime != 0 || $totalsessions != 0) ?
                        utils::format_activitytime($totaltime / $totalsessions) : 0,
                'numavgtimesession' => ($totaltime != 0 || $totalsessions != 0) ? $totaltime / $totalsessions : 0,
                'numberofactions' => $totalactions,
                'avgnumberofactions' => ($totalactions != 0 || $totalsessions != 0) ?
                        number_format($totalactions / $totalsessions , 1) : 0,
        ];
        $coursedata[] = $cdata;

        $measures['coursedata'] = $coursedata;

        return $measures;
    }


    /**
     * Group all info for template
     * @param int $courseid
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @return array
     * @throws \dml_exception
     */
    public function group_users_measures_data($courseid , $searchperiod = false , $from = null , $to = null) {

        $dbquery = new dbquery();

        $measures = [];

        $enrolledusers = get_enrolled_users(context_course::instance($courseid));
        $userdata = [];
        // User Data.
        foreach ($enrolledusers as $enrolleduser) {
            $info = $dbquery->db_user_course_data($enrolleduser->id , $courseid , $searchperiod , $from , $to);

            $udata = [
                    'userid' => $enrolleduser->id,
                    'lastname' => $enrolleduser->lastname,
                    'firstname' => $enrolleduser->firstname,
                    'coursetitle' => $this->course_title($courseid),
                    'totaltimeformated' => utils::format_activitytime($info->totaltime),
                    'numtotaltime' => $info->totaltime,
                    'totalsessions' => $info->sessions,
                    'avgtimesession' => utils::format_activitytime($info->avgsessiontime),
                    'numavgtimesession' => $info->avgsessiontime,
                    'numberofactions' => $info->totalactions,
                    'avgnumberofactions' => number_format($info->avgsessionactions , 1),

            ];

            $userdata[] = $udata;

        }

        $measures['userdata'] = $userdata;

        return $measures;
    }


}

