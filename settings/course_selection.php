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
 * Course Selection configuration page
 *
 * @package    block_course_statistics
 * @copyright 2022 onwards WIDE Services  {@link https://www.wideservices.gr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use block_course_statistics\coursestatistics;

require_once('../../../config.php');

global $PAGE, $DB, $USER, $OUTPUT;

$context = context_system::instance();
require_login();
require_capability('block/course_statistics:admin', $context);

// Set URL.
$url = new moodle_url('/blocks/course_statistics/settings/course_selection.php');
$PAGE->set_url($url);
$PAGE->set_context($context);

// Set page title and header.

$PAGE->set_title(get_string('config_course_selection_title', 'block_course_statistics'));
$PAGE->set_heading(get_string('config_course_selection_header', 'block_course_statistics'));

$coursestatistics = new coursestatistics();
$coursestatistics->get_block_course_statistics_css($PAGE);

// This is not the best way to pass the strings.
$PAGE->requires->strings_for_js([
        'filter',
        'copy',
        'export',
        'pausemeasure',
        'startmeasure',
], 'block_course_statistics');

// Retrieve all courses from the database.
$selectedcourses = $DB->get_records_sql("
    SELECT
        c.id as courseid,
        c.fullname as coursename,
        COUNT(DISTINCT ue.id) as enrolled_user_count
    FROM {course} c
    LEFT JOIN {enrol} en ON en.courseid = c.id
    LEFT JOIN {user_enrolments} ue ON ue.enrolid = en.id
    WHERE c.id <> 1 GROUP BY c.id, c.fullname
");

// Add an index to each course.
$indexedcourses = [];
$index = 1;
foreach ($selectedcourses as $course) {

    // Check which ones are selected to be measured by the scheduled task.
    $checkmeasure = $DB->get_record('cs_course_measures' , ['courseid' => $course->courseid]);
    $course->measure = (!empty($checkmeasure)) ? $checkmeasure->measure : 0;
    $indexedcourses[] = $course;
}

// Render the Mustache template.
$data = [
        'courses' => array_values($indexedcourses),
        'current_lang' => current_language(),
];

// Render page.
echo $OUTPUT->header();
echo $OUTPUT->box_start();
echo $OUTPUT->render_from_template('block_course_statistics/settings', $data);
echo $OUTPUT->box_end();
echo $OUTPUT->footer();
