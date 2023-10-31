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
namespace block_course_statistics\settings;

defined('MOODLE_INTERNAL') || die();

// Define the form class.
use moodleform;
use stdClass;

global $CFG;

require_once($CFG->libdir . '/formslib.php');

/**
 * Form to select courses for measurement if needed.
 */
class course_selection_form extends moodleform {

    /**
     * Definition
     *
     * @return void
     */
    public function definition() {
        global $DB;
        $mform = $this->_form;

        // Add a select element to the form.
        $courses = $DB->get_records_menu('course', null, 'id', 'id,fullname');
        $mform->addElement('select', 'selectedcourses',
                get_string('select_courses', 'block_course_statistics'),
                $courses, array('multiple' => 'multiple', 'size' => 10));
        $mform->setType('selectedcourses', PARAM_INT);

        // Add a submit button.
        $mform->addElement('submit', 'submitbutton', get_string('savechanges'));

    }

    /**
     * Validation
     * @param obj $data
     * @param obj $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = array();
        // Add any validation rules here.
        return $errors;
    }

    /**
     * Submit form.
     * @param obj $data
     * @return void
     */
    public function submit_form($data) {

        global $DB;

        // Handle form submission.
        if (isset($data->selectedcourses) && is_array($data->selectedcourses)) {
            foreach ($data->selectedcourses as $courseid) {

                // Check if the courseid already exists in cs_course_measures.

                $existingrecord = $DB->record_exists('cs_course_measures', array('courseid' => $courseid));

                if (!$existingrecord) {
                    // Save selected courses to the database.
                    $record = new stdClass();
                    $record->courseid = $courseid;
                    $DB->insert_record('cs_course_measures', $record);
                }
            }
        }

    }
}

