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
 * Events external API
 *
 * @package    block_course_statistics
 * @category   external
 * @copyright   2023 onwards WIDE Services  {@link https://www.wideservices.gr}
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_statistics\external;

use context_module;
use context_system;
use dml_exception;
use Exception;
use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use external_warnings;
use moodle_exception;
use stdClass;

defined('MOODLE_INTERNAL') || die;

global $CFG;

require_once("$CFG->libdir/externallib.php");

/**
 * External function
 *
 * @package    block_course_statistics
 * @category   external
 * @copyright   2023 onwards WIDE Services  {@link https://www.wideservices.gr}
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class pause_measure extends external_api {

    /**
     * Constructor for the object.
     */
    public function __construct() {
        // Your constructor logic here.
    }

    /**
     * Parameters
     *
     * @return external_function_parameters
     */
    public static function pause_measure_parameters() {
        return new external_function_parameters(
                [
                        'courseid' => new external_value(PARAM_INT,
                                'The courseid from block_course_statistics_meas table', VALUE_REQUIRED),
                        'status' => new external_value(PARAM_INT,
                                'The status from block_course_statistics_meas table', VALUE_REQUIRED),
                ]
        );
    }

    /**
     * The function itself
     * @param int $courseid
     * @param int $status
     * @return mixed
     * @throws Exception
     */
    public static function pause_measure($courseid , $status) {
        global $DB;
        // Parameters validation.
        $params = self::validate_parameters(self::pause_measure_parameters(), ['courseid' => $courseid, 'status' => $status]);
        $courseid = $params['courseid'];
        $status = $params['status'];

        // Context validation.
        $context = context_system::instance();
        require_capability('block/course_statistics:admin', $context);

        // If not exists insert else update.
        $courseexist = $DB->record_exists('block_course_statistics_meas' , ['courseid' => $courseid]);

        if (!$courseexist) {

            // If this course doesnt exist, create it to table cm_course_measures/ block_course_statistics_meas.
            $newmeasure = new stdClass();
            $newmeasure->courseid = $courseid;
            $newmeasure->status = 0;

            $isinsert = $DB->insert_record('block_course_statistics_meas', $newmeasure);

            if (!$isinsert) {
                throw new Exception('We couldnt insert the course with id : '.$courseid .' in cm_course_measures table.');
            }

        } else {

            $courseexist = $DB->get_record('block_course_statistics_meas', ['courseid' => $courseid]);

            $changemeasure = new stdClass();
            $changemeasure->id = $courseexist->id;
            $changemeasure->measure = $status;

            $isupdated = $DB->update_record('block_course_statistics_meas', $changemeasure);

            if (!$isupdated ) {
                throw new Exception('We couldnt update the status of the course with id : '.$courseid .
                        ' in block_course_statistics_meas table.');
            }
        }

        return $status;
    }

    /**
     * Return
     * @return external_value
     */
    public static function pause_measure_returns() {
        return new external_value(PARAM_BOOL, 'Status paused for a course measurement');
    }
}

