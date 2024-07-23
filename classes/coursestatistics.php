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
 * Course statistics general class
 *
 * @package    block_course_statistics
 * @copyright 2023 onwards WIDE Services  {@link https://www.wideservices.gr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_course_statistics;


/**
 * Class main
 */
class coursestatistics {

    /**
     * Construct
     */
    public function __construct() {
    }
    /**
     * CSS style for datatable
     * @param obj $PAGE
     * @return void
     */
    public function get_block_course_statistics_css($PAGE) {
        $PAGE->requires->css('/blocks/course_statistics/style/styles.css');
        $PAGE->requires->css('/blocks/course_statistics/style/theme.css');
        $PAGE->requires->css('/blocks/course_statistics/style/checkboxes.css');
        $PAGE->requires->css('/blocks/course_statistics/style/jquery.dataTables.css');
        $PAGE->requires->css('/blocks/course_statistics/style/dataTables.jqueryui.css');
        $PAGE->requires->css('/blocks/course_statistics/style/responsive.dataTables.css');
        $PAGE->requires->css('/blocks/course_statistics/style/responsive.jqueryui.css');
        $PAGE->requires->css('/blocks/course_statistics/style/responsive.bootstrap.css');
        $PAGE->requires->css('/blocks/course_statistics/style/responsive.bootstrap4.css');

    }

}

