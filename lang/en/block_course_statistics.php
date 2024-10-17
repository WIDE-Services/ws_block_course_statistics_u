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
 * Translate file
 * @package    block_course_statistics
 * @copyright  2023 onwards WIDE Services  {@link https://www.wideservices.gr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Basic plugin strings.
$string['pluginname'] = 'Course statistics';

// Capabilites.
$string['course_statistics:addinstance'] = 'Allow to add Course statistics plugin';
$string['course_statistics:myaddinstance'] = 'Allow to add Course statistics plugin to Dashboard';
$string['course_statistics:admin'] = 'Allow admin use Course statistics plugin';
$string['course_statistics:teacher'] = 'Allow teacher to use Course statistics plugin';
$string['course_statistics:student'] = 'Allow student to use Course statistics plugin';

// Privacy.
$string['privacy:metadata'] = 'The Course Statistics block only shows information about courses and does not store data itself.';

// Block content.
$string['access_button'] = 'Statistics';
$string['access_info'] = 'Admin / Teacher:';
$string['user_access_info'] = 'Student Course Statistics:';

// Main Dashboard.
$string['capability_admin'] = 'Course Statistics , Admin Dashboard.';

// Filters period form.
$string['measures_per_period'] = 'Measures per period ';
$string['select_course'] = 'Select course : ';
$string['start_period'] = 'Start period : ';
$string['end_period'] = 'End period : ';
$string['search'] = 'Search';

// Form validation error.
$string['invaliddates'] = 'The provided dates are not valid . The end date is less than the start date.';

// Data tables labels.
$string['sessions'] = 'Sessions';
$string['totaltimeinsubject'] = 'Total Time In The Subject';
$string['numberofactions'] = 'Number Of Actions';
$string['avgtimesession'] = 'Average Time Per Session';
$string['avgnumberactionsessions'] = 'Average Number Of Actions Per Session';
$string['numberofactions'] = 'Number Of Actions';
$string['avgtimesession'] = 'Average Time Per Session';
$string['totaltimededicated'] = 'Total Time Dedicated';
$string['numberofsessions'] = 'Number of Sessions';
$string['averageusedinsessions'] = 'Average Use in Sessions';
$string['coursetotalsessions'] = 'Course Total Sessions';
$string['activitysessions'] = 'Activity Sessions';
$string['avgactivitytime'] = 'Average Time in Sessions';
$string['forum'] = 'Forum';
$string['postreads'] = 'Post Reads';
$string['topicinitialized'] = 'Topics Initialized';
$string['topic'] = 'Topic';
$string['posts'] = 'Posts';
$string['postanswers'] = 'Post Answers';
$string['courseactivity'] = 'Course Activity';
$string['totaltime'] = 'Total time';
$string['averagetime'] = 'Average time';
$string['quiz'] = 'Quiz';
$string['attempts'] = 'Attempts';
$string['avgscore'] = 'Average score';
$string['skip'] = 'Skipped Questions';
$string['answers'] = 'Answers';

// Navigation.
$string['generalmeasures'] = 'General measures';
$string['measurespertool'] = 'Measures per tool';
$string['measuresfortheforum'] = 'Measures for the forum';
$string['measuresinquizzes'] = 'Measures in Quizzes';

$string['attempt'] = 'Attempt';
$string['question'] = 'Question';
$string['questiontime'] = 'Question time';
$string['action'] = 'Action';
$string['viewattempt'] = 'View';

// Settings.
$string['coursestatistics_settings'] = ' Course Statistics Settings';
$string['check_schedule_task'] = 'Retrieve data from plugin tables and not directly from Moodle logs';
$string['scheduletask_title'] = 'Enable Schedule task';
$string['scheduletask_description'] = 'Enabling Schedule task will pre calculate the statistics from Moodle logs.';

// Configuration Settings.
$string['config_course_selection'] = 'Statistics measurement course selection.';
$string['config_course_selection_title'] = 'Course Selection';
$string['config_course_selection_header'] = 'Course selection for statistic measurements';
$string['select_courses'] = 'Select courses : ';
$string['coursetitle'] = 'Course Title';
$string['courseid'] = 'Course ID';
$string['activities'] = 'Activities';
$string['enrolments'] = 'Enrolments';
$string['action'] = 'Action';
$string['pausemeasure'] = 'Pause Measure';
$string['startmeasure'] = 'Start Measure';
$string['resetmeasure'] = 'Reset Measure';
// Access.
$string['accessdenied'] = ' Access Denied';

// Breadcrumb.
$string['courses'] = 'Course';
$string['activities'] = 'Activities';
$string['quizzes'] = 'Quizzes';
$string['forums'] = 'Forums';
$string['topics'] = 'Topics';
$string['users'] = 'Users';

// Datatable.
$string['filter'] = 'Filter';
$string['copy'] = 'Copy';
$string['export'] = 'Export';

// Privacy.
$string['privacy:metadata:block_course_statistics_cses'] = 'Table for course sessions statistics';
$string['privacy:metadata:block_course_statistics_cses:courseid'] = 'The Course id';
$string['privacy:metadata:block_course_statistics_cses:userid'] = 'The user id';
$string['privacy:metadata:block_course_statistics_cses:startsession'] = 'Start of course session time';
$string['privacy:metadata:block_course_statistics_cses:endsession'] = 'End of course session time';
$string['privacy:metadata:block_course_statistics_cses:sessiontime'] = 'Total session time';
$string['privacy:metadata:block_course_statistics_cses:actions'] = 'Session Action';

$string['privacy:metadata:block_course_statistics_ases'] = 'Table for activity sessions statisitcs.';
$string['privacy:metadata:block_course_statistics_ases:courseid'] = 'The Course id';
$string['privacy:metadata:block_course_statistics_ases:userid'] = 'The user id';
$string['privacy:metadata:block_course_statistics_ases:activity'] = 'The Activity in the course';
$string['privacy:metadata:block_course_statistics_ases:cminstance'] = 'The course module instance';
$string['privacy:metadata:block_course_statistics_ases:activitytitle'] = 'The Activity title';
$string['privacy:metadata:block_course_statistics_ases:activitytime'] = 'The Activity time';
$string['privacy:metadata:block_course_statistics_ases:activitysessions'] = 'Total Activity Sessions';
