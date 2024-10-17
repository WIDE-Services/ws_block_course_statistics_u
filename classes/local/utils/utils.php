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

namespace block_course_statistics\local\utils;

use block_course_statistics\local\dbquery;
use html_writer;
use MoodleExcelWorkbook;
use stdClass;

/**
 * Class block_activitytime_utils
 *
 * @package    block_course_statistics
 * @copyright 2023 onwards WIDE Services  {@link https://www.wideservices.gr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utils {

    /**
     * @var string[] $logstores
     */
    public static $logstores = ['logstore_standard', 'logstore_legacy'];

    /**
     * Return formatted events from logstores.
     * @param string $selectwhere
     * @param array $params
     * @return array
     */
    public static function get_activity_events_select($selectwhere, array $params): array {

        $return = [];

        static $allreaders = null;

        if (is_null($allreaders)) {
            $allreaders = get_log_manager()->get_readers();
        }

        $processedreaders = 0;

        foreach (self::$logstores as $name) {
            if (isset($allreaders[$name])) {
                $reader = $allreaders[$name];
                $events = $reader->get_events_select($selectwhere, $params, 'timecreated ASC', 0, 0);

                foreach ($events as $key => $event) {

                    $obj = new stdClass();
                    $obj->id = $key;
                    $obj->time = $event->timecreated;
                    $obj->ip = $event->get_logextra()['ip'];
                    $obj->contextid = $event->contextid;
                    $obj->level = $event->contextlevel;
                    $obj->instanceid = $event->contextinstanceid;
                    $obj->userid = $event->userid;
                    $obj->courseid = $event->courseid;
                    $return[] = $obj;

                }
                if (!empty($events)) {

                    $processedreaders++;

                }
            }
        }
        // Sort mixed array by time ascending again only when more of a reader has added events to return array.
        if ($processedreaders > 1) {

            usort($return, function($a, $b) {
                return $a->time > $b->time;
            });

        }

        return $return;
    }

    /**
     * Return the users activity time of a module in a course
     * @param string $modulename
     * @param string $activitytitle
     * @param array $package
     * @param int $user
     * @param int $course
     * @param string $chartcolour
     * @return object
     */
    public static function get_module_activity_time(
            $modulename ,
            $activitytitle ,
            $package ,
            $user ,
            $course ,
            $chartcolour ): object {

        $dbquery = new dbquery();
        $sessiontimeout = get_config('' , 'sessiontimeout');

        $slice = 0;
        $activitypart = [];
        $activitysessiontime = [];
        $activitytime = 0;
        $activitysametime = 0;
        $activityfinishtime = 0;
        $consecutives = [];
        $i = 0;

        // Lets create the consecutive parts based on the array.
        foreach ($package as $key => $pkg) {

            if ($i == 0) {

                $package[$key] = $pkg;
                $consecutives[$slice][$key] = $pkg;

            } else {

                $isconsecutive = $pkg - $package[$key - 1];

                if ($isconsecutive == 1) {

                    $consecutives[$slice][$key] = $pkg;

                } else {

                    $slice++;
                    $consecutives[$slice][$key] = $pkg;

                }
            }

            $i++;
        }
        foreach ($consecutives as $key => $consecutive) {

            // Startsession of activity.
            $activitypart['min'][$key] = min($consecutive);

            // The next user action after max is the exit of the activity.
            $activitypart['max'][$key] = max($consecutive);

        }

        foreach ($activitypart['min'] as $key => $part) {
            // Find the min and max id of the activity session.
            $datamin = $dbquery->db_activity_session_min($activitypart['min'][$key]);

            $datamax = $dbquery->db_activity_session_max($activitypart['max'][$key]);

            $diff = $datamax->timecreated - $datamin->timecreated;

            // Endsession find when user exit the activity session.
            $module = $dbquery->db_user_exit_activity_session($user , $datamax->timecreated);

            // Capture for this activity session when it starts and when ended.
            $activitysessiontime[$key]['startactivitysession'] = ($datamin) ? $datamin->timecreated : 0;
            if ($datamin->timecreated === $datamax->timecreated || $diff <= 2) {
                if ($datamax && !empty($datamax)) {

                    $forgottenopenmodule = (isset($module->timecreated) && isset($datamax->timecreated)) ?
                            ($module->timecreated - $datamax->timecreated) : 0;

                    if ($module) {

                        // Check if the diff between them is more than the config sessiontimeout if yes then dont calculate.
                        // If it is then something went wrong and dont calculate the last forgotten opened module.
                        if ($forgottenopenmodule < $sessiontimeout) {

                            if ($module->contextlevel = 70) {

                                $activityfinishtime = $module->timecreated;
                                $activitysessiontime[$key]['endactivitysession'] = $module->timecreated;

                            }
                            $activitysametime += $activityfinishtime - $datamax->timecreated;
                            $activitysessiontime[$key]['activitysessiontime'] = $activityfinishtime - $datamax->timecreated;

                        }
                    }
                }

            } else {

                if ($datamin && !empty($datamin)) {

                    $forgottenopenmodule = (isset($module->timecreated) && isset($datamax->timecreated)) ?
                            ($module->timecreated - $datamax->timecreated) : 0;

                    // Check if the diff between them is more than the config sessiontimeout if yes then dont calculate.
                    // If it is then something went wrong and dont calculate the last forgotten opened module.
                    if ($forgottenopenmodule < $sessiontimeout) {
                        $activitytime += (isset($module->timecreated) && isset($datamin->timecreated)) ?
                                ($module->timecreated - $datamin->timecreated) : 0;

                        $activitysessiontime[$key]['endactivitysession'] = (isset($module->timecreated)) ? $module->timecreated : 0;
                        $activitysessiontime[$key]['activitysessiontime'] = (isset($module->timecreated) &&
                                isset($datamin->timecreated)) ? ($module->timecreated - $datamin->timecreated) : 0;
                    }
                }
            }
        }

        // Check it why i put it out of the loop? Mistake? Onpurpose? Dont remember...
        if ($activitysametime > 0) {

            $activitytime += $activitysametime;

        }

        $firstpackages = $consecutives[array_key_first($consecutives)];

        foreach ($firstpackages as $first) {

            $firstactivitytime = $first;
            break;

        }

        $lastpackages = $consecutives[array_key_last($consecutives)];

        foreach ($lastpackages as $last) {

            $lastactivitytime = $last;

        }

        $firstactivityaccess = $dbquery->db_first_activity_access($firstactivitytime);

        $lastactivityaccess = $dbquery->db_last_activity_access($lastactivitytime);

        return (object) [
                'module' => strtoupper($modulename),
                'activitytitle' => $activitytitle,
                'activitysessions' => count($consecutives),
                'userid' => $user,
                'courseid' => $course,
                'activitytime' => ($activitytime >= 0) ? $activitytime : 0,
                'firstaccess' => date('m/d/Y H:i:s', $firstactivityaccess->timecreated),
                'lastaccess' => date('m/d/Y H:i:s', $lastactivityaccess->timecreated),
                'colour' => $chartcolour,
                'activitysessiontime' => $activitysessiontime,
        ];
    }

    /**
     * Return activity title
     * @param string $modulename
     * @param array $package
     * @param int $user
     * @param int $course
     * @return string
     */
    public static function get_module_activity_title($modulename , $package , $user , $course): string {

        global $CFG;

        $dbquery = new dbquery();

        $title = '';
        $standardlogid = array_values($package)[0];

        $dbtable = $CFG->prefix . $modulename;

        $sqllog = $dbquery->db_get_logstore($standardlogid);

        if ($sqllog) {

            $moduletitle = $dbquery->db_activity_title($dbtable, $user, $course, $sqllog->contextinstanceid);

            if ($moduletitle) {

                $title = $moduletitle->activitytitle;
            }
        }

        return $title;

    }

    /**
     * Return Quiz total time
     * @param string $modulename
     * @param string $activitytitle
     * @param array $package
     * @param int $user
     * @param int $course
     * @param string $chartcolour
     * @return object
     * @throws \dml_exception
     */
    public static function get_module_quiz_time(
            $modulename,
            $activitytitle,
            $package,
            $user,
            $course,
            $chartcolour): object {
        $dbquery = new dbquery();

        $quiztotaltime = 0;

        $standardlogid = array_values($package)[0];

        $sqllog = $dbquery->db_get_logstore($standardlogid);

        if ($sqllog) {
            $quiztimes = $dbquery->db_quiz_title_times($user , $course , $sqllog->contextinstanceid);

            if ($quiztimes) {

                foreach ($quiztimes as $quiz) {

                    $quiztotaltime += $quiz->timefinish - $quiz->timestart;

                    $activitytitle = $quiz->activitytitle;

                }

            }

        }

        return (object) [
                'module' => strtoupper($modulename) ,
                'activitytitle' => $activitytitle ,
                'userid' => $user ,
                'courseid' => $course ,
                'activitytime' => ($quiztotaltime >= 0) ? $quiztotaltime : 0 ,
                'colour' => $chartcolour,
                'activitysessiontime' => null,
        ];

    }

    /**
     * Return Scorm total time
     * @param string $modulename
     * @param string $activitytitle
     * @param array $package
     * @param int $user
     * @param int $course
     * @param string $chartcolour
     * @return object
     * @throws \dml_exception
     */
    public static function get_module_scorm_time(
            $modulename,
            $activitytitle,
            $package,
            $user,
            $course,
            $chartcolour): object {

        $dbquery = new dbquery();

        $scormtotaltime = 0;

        $standardlogid = array_values($package)[0];

        $sqllog = $dbquery->db_get_logstore($standardlogid);

        if ($sqllog) {

            $scormtracks = $dbquery->db_get_scorm_track($user , $course , $sqllog->contextinstanceid);
            if ($scormtracks) {

                foreach ($scormtracks as $track) {

                    $strtime = $track->value;
                    sscanf($strtime, "%d:%d:%d", $hours, $minutes, $seconds);
                    $timeseconds = isset($seconds) ? $hours * 3600 + $minutes * 60 + $seconds : $hours * 60 + $minutes;
                    $scormtotaltime += $timeseconds;
                    $activitytitle = $track->activitytitle;
                }

            }

        }

        return (object) [
                'module' => strtoupper($modulename),
                'activitytitle' => $activitytitle,
                'userid' => $user,
                'courseid' => $course,
                'activitytime' => ($scormtotaltime >= 0) ? $scormtotaltime : 0 ,
                'colour' => $chartcolour,

        ];
    }

    /**
     * Return bigbluebutton total time
     * @param string $modulename
     * @param string $activitytitle
     * @param int $user
     * @param int $course
     * @param string $chartcolour
     * @return object
     * @throws \dml_exception
     */
    public static function get_module_bigbluebuttonbn_time(
            $modulename,
            $activitytitle,
            $user,
            $course,
            $chartcolour): object {

        $dbquery = new dbquery();

        $sessions = [];
        $currentsession = null;

        $duration = 0;
        $bbbresults = $dbquery->db_bbb_action($user , $course , $modulename );

        if ($bbbresults && !empty($bbbresults)) {

            foreach ($bbbresults as $key => $result) {

                if ($result->action === 'joined') {
                    // Start a new session.
                    $currentsession = [
                            'startactivitysession' => $result->timecreated,
                            'endactivitysession' => null,
                    ];
                } else if ($result->action === 'left' && $currentsession !== null) {
                    // End the current session.
                    $currentsession['endactivitysession'] = $result->timecreated;
                    $sessionduration = $currentsession['endactivitysession'] - $currentsession['startactivitysession'];
                    $sessions[] = [
                            'startactivitysession' => $currentsession['startactivitysession'],
                            'endactivitysession' => $currentsession['endactivitysession'],
                            'activitysessiontime' => $sessionduration,
                    ];
                    $currentsession = null;
                }
            }

            // If a session is still open at the end, consider it as incomplete.
            if ($currentsession !== null) {
                $sessions[] = [
                        'startactivitysession' => $currentsession['startactivitysession'],
                        'endactivitysession' => null,
                        'activitysessiontime' => null, // Indicate that the session is incomplete.
                ];
            }

            foreach ($sessions as $session) {
                if ($session['activitysessiontime'] !== null) {
                    $duration += $session['activitysessiontime'];
                }
            }

        }

        return (object) [
                'module' => strtoupper($modulename) ,
                'activitytitle' => $activitytitle ,
                'activitysessions' => count($sessions),
                'userid' => $user ,
                'courseid' => $course ,
                'activitytime' => ($duration >= 0 ) ? $duration : 0 ,
                'firstaccess' => 0,
                'lastaccess' => 0,
                'colour' => $chartcolour ,
                'activitysessiontime' => $sessions,

        ];

    }

    /**
     * Formats time based in Moodle function format_time($totalsecs).
     * @param int $totalsecs
     * @return string
     */
    public static function format_activitytime($totalsecs): string {

        $totalsecs = abs($totalsecs);

        $str = new stdClass();
        $str->hour = get_string('hour');
        $str->hours = get_string('hours');
        $str->min = get_string('min');
        $str->mins = get_string('mins');
        $str->sec = get_string('sec');
        $str->secs = get_string('secs');

        $hours = floor($totalsecs / HOURSECS);
        $remainder = $totalsecs - ($hours * HOURSECS);
        $mins = floor($remainder / MINSECS);
        $secs = round($remainder - ($mins * MINSECS), 2);

        $ss = ($secs == 1) ? $str->sec : $str->secs;
        $sm = ($mins == 1) ? $str->min : $str->mins;
        $sh = ($hours == 1) ? $str->hour : $str->hours;

        $ohours = '';
        $omins = '';
        $osecs = '';

        if ($hours) {

            $ohours = $hours . ' ' . $sh;

        }

        if ($mins) {

            $omins = $mins . ' ' . $sm;

        }

        if ($secs) {

            $osecs = $secs . ' ' . $ss;

        }

        if ($hours) {

            return trim($ohours . ' ' . $omins);

        }
        if ($mins) {

            return trim($omins . ' ' . $osecs);

        }
        if ($secs) {

            return $osecs;

        }

        return 0;

    }

}

