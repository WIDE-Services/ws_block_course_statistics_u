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
 * Configuration Settings for the course_statistics
 *
 * @package    block_course_statistics
 * @copyright 2022 onwards WIDE Services  {@link https://www.wideservices.gr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Configuration setting of the block.
 */
class block_course_statistics_edit_form extends block_edit_form {

    /**
     * Settings Specification.
     * @param obj $mform
     * @return void
     */
    protected function specific_definition($mform) {

        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $pluginsettings = new moodle_url('/blocks/course_statistics/settings/course_selection.php');
        $mform->addElement('html', '<h3 style="width: 100%;" class="main"><a href="' . $pluginsettings . '">' .
                get_string('config_course_selection', 'block_course_statistics') . '</a></h3><hr>');
    }
}
