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

namespace block_course_statistics\formfilter;

defined('MOODLE_INTERNAL') || die();

use moodleform;

global $CFG;

require_once($CFG->libdir . '/formslib.php');

/**
 * User filtering form wrapper class.
 *
 * @package   block_course_statistics
 * @copyright 2023 onwards WIDE Services  {@link https://www.wideservices.gr}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class filterform extends moodleform {

    /**
     * Definition
     * @return void
     * @throws \coding_exception
     */
    public function definition() {

        $mform = & $this->_form;
        $mform->addElement('date_selector', 'startperiod',
        get_string('start_period', 'block_course_statistics'));
        $mform->addElement('date_selector', 'endperiod',
         get_string('end_period', 'block_course_statistics'));

        // Buttons.
        $this->add_action_buttons(true,
                get_string('search', 'block_course_statistics'));

    }

    /**
     * Validate dates
     * @param array $data
     * @param array $files
     * @return array|void
     * @throws \coding_exception
     */
    public function validation($data, $files) {

        $errors = parent::validation($data, $files);

        if ($data['startperiod'] > $data['endperiod'] ) {
            $errors['endperiod'] = get_string('invaliddates', 'block_course_statistics');
        }

        return $errors;

    }

}

