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
 * Quiz Measures main page
 *
 * @package    block_course_statistics
 * @copyright 2023 onwards WIDE Services  {@link https://www.wideservices.gr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_course_statistics\output\measuresinquizzes;
use block_course_statistics\output\measurescoursequizzes;
use block_course_statistics\output\measuresquizusers;
use block_course_statistics\local\coursestatistics;
use block_course_statistics\local\formfilter\filterform;
use block_course_statistics\local\measuresinquizzes\quizlogic;

require_once(__DIR__ . '/../../../../../config.php');

require_login();

global $CFG , $USER , $PAGE , $OUTPUT;
$courseid = optional_param('courseid', 0, PARAM_INT);
$quizid = optional_param('quizid', 0, PARAM_INT);
$viewquizzes = optional_param('viewquizzes', 0, PARAM_INT);
$viewusers = optional_param('viewusers', 0, PARAM_INT);
$from = optional_param('from', null, PARAM_INT);
$to = optional_param('to', null, PARAM_INT);
// The searchperiod optional param is a balander for the from, to params so i dont do many checks.
$searchperiod = optional_param('searchperiod', false, PARAM_BOOL);

$params = [
        'courseid' => $courseid ,
        'quizid' => $quizid ,
        'viewquizzes' => $viewquizzes ,
        'viewusers' => $viewusers,
];

$userid = $USER->id;  // Owner of the page.
$coursecontext = context_course::instance($courseid);
$header = fullname($USER);
$pagetitle = get_string('pluginname', 'block_course_statistics');

$pageurl = new moodle_url('/blocks/course_statistics/pages/admin/measuresinquizzes/index.php');
$pageurl->params($params);

$PAGE->set_context($coursecontext);
$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('report');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($header);

$coursestatistics = new coursestatistics();
$coursestatistics->get_block_course_statistics_css($PAGE);

$PAGE->requires->jquery();
$PAGE->requires->strings_for_js([
        'filter',
        'copy',
        'export',
        'pausemeasure',
        'startmeasure',
], 'block_course_statistics');
$mform = new filterform($pageurl, ['courseid' => $courseid ], 'get');

// Form logic through general measures results.
// The Form must set the dates that generalmeasures will produce results.

if ($mform->is_cancelled()) {
    // If the form was canceled, redirect to the same page or a specific URL.
    redirect($pageurl);

} else if ($fromform = $mform->get_data()) {
    // This branch is executed if the data is successfully validated.

    $from = $fromform->startperiod;
    $to = $fromform->endperiod;

    $searchperiod = true;

    // Store values in the session.
    $_SESSION['measure_period'] = [
            'from' => $from,
            'to' => $to,
    ];

    // Display the form.
    $data = new stdClass();
    $data->form = $mform->render();
    // Process the submitted data, e.g., perform some actions based on the submitted dates.
} else {
    // This branch is executed if the form is submitted but the data doesn't validate,
    // or on the initial display of the form.

    // If you have default values or want to pre-fill the form, set them here.
    if (isset($_SESSION['measure_period'])) {
        $defaultdata = new stdClass();
        $defaultdata->startperiod = $_SESSION['measure_period']['from'];
        $defaultdata->endperiod = $_SESSION['measure_period']['to'];
        $mform->set_data($defaultdata);
    } else {

        // If you have default values or want to pre-fill the form, set them here.
        $defaultdata = new stdClass();
        $defaultdata->startperiod = $from; // Set default start period value.
        $defaultdata->endperiod = $to; // Set default end period value.
        $mform->set_data($defaultdata);

    }

    // Display the form.
    $data = new stdClass();
    $data->form = $mform->render();
}

echo $OUTPUT->header();
echo $OUTPUT->heading($pagetitle);

$isteacher = false;
$access = false;

// Check if user is actually a teacher or editing teacher of this course and is not the admin.
if (!has_capability('block/course_statistics:admin', $coursecontext) &&
        has_capability('block/course_statistics:teacher', $coursecontext, $USER->id)) {

    $isteacher = true;

}

$logic = new quizlogic();

// Retrieve Data From the plugins table and not directly from logstore table!
// The data have been precalculated from the scheduled task.
if ($viewquizzes != 0) {

    // General Total Results for each module in course.

    // Retrieve the scheduled precalculated data.
    if ($isteacher) {

        $measures = $logic->group_viewquizzes_data($courseid , $searchperiod , $from , $to);

        $maindashboard = new measurescoursequizzes($params , $data->form , $measures["quizdata"] ,
                $searchperiod , $from , $to , $access = true);

    } else if (has_capability('block/course_statistics:admin', $coursecontext)) {

        $measures = $logic->group_viewquizzes_data($courseid , $searchperiod , $from , $to);

        $maindashboard = new measurescoursequizzes($params , $data->form , $measures["quizdata"] ,
                $searchperiod , $from , $to , $access = true);

    } else {

        $maindashboard = new measurescoursequizzes(null , null , null ,
                null , null , null , $access = false);
    }


} else if ($viewusers != 0) {

    // General Total Results for each module in course.

    // Retrieve the scheduled precalculated data.
    if ($isteacher) {

        $measures = $logic->group_viewusers_data($courseid , $quizid , $searchperiod , $from , $to);

        $maindashboard = new measuresquizusers($params , $data->form , $measures["usersdata"] ,
                $searchperiod , $from , $to , $access = true);

    } else if (has_capability('block/course_statistics:admin', $coursecontext)) {

        $measures = $logic->group_viewusers_data($courseid , $quizid , $searchperiod , $from , $to);

        $maindashboard = new measuresquizusers($params , $data->form , $measures["usersdata"] ,
                $searchperiod , $from , $to , $access = true);

    } else {

        $maindashboard = new measuresquizusers(null , null , null ,
                null , null , null , $access = false);
    }


} else {

    // Summarize General Total Result for all activities in a course.

    // Retrieve the scheduled precalculated data.

    if ($isteacher) {

        $measures = $logic->group_courses_quizzes_data($courseid , $isteacher , $searchperiod , $from , $to);

        $maindashboard = new measuresinquizzes($params , $data->form , $measures["generaldata"] ,
                $searchperiod , $from , $to , $access = true);

    } else if (has_capability('block/course_statistics:admin', $coursecontext)) {


        $measures = $logic->group_courses_quizzes_data($courseid , $isteacher , $searchperiod , $from , $to);

        $maindashboard = new measuresinquizzes($params , $data->form , $measures["generaldata"] ,
                $searchperiod , $from , $to , $access = true);

    } else {

        $maindashboard = new measuresinquizzes(null , null , null ,
                null , null , null , $access = false);
    }

}

$renderer = $PAGE->get_renderer('block_course_statistics');
echo $renderer->render($maindashboard);

echo $OUTPUT->footer();

