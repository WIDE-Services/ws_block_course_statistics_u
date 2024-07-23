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
 * Main page renderer for plugin block_course_statistics.
 *
 * @package    block_course_statistics
 * @copyright 2023 onwards WIDE Services  {@link https://www.wideservices.gr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_statistics\output;

use context_system;
use plugin_renderer_base;

/**
 * Class renderer
 */
class renderer extends plugin_renderer_base {

    /**
     * General measures main page.
     * @param generalmeasures $dashboard
     * @return bool|string|void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function render_generalmeasures(generalmeasures $dashboard) {

            return $this->render_from_template('block_course_statistics/admin/generalmeasures/main' ,
                    $dashboard->export_for_template($this));

    }

    /**
     * generalusersmeasures
     * @param generalusersmeasures $dashboard
     * @return bool|string
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function render_generalusersmeasures(generalusersmeasures $dashboard) {

            return $this->render_from_template('block_course_statistics/admin/generalmeasures/usersmain' ,
                    $dashboard->export_for_template($this));

    }

    /**
     * Measures per tool main page.
     * @param measurespertool $dashboard
     * @return bool|string|void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function render_measurespertool(measurespertool $dashboard) {

        return $this->render_from_template('block_course_statistics/admin/measurespertool/main' ,
                $dashboard->export_for_template($this));
    }


    /**
     * Measures per tool main page.
     * @param measurescoursetool $dashboard
     * @return bool|string|void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function render_measurescoursetool(measurescoursetool $dashboard) {

        return $this->render_from_template('block_course_statistics/admin/measurespertool/activitiesmain' ,
                $dashboard->export_for_template($this));
    }

    /**
     * Measures per tool main page.
     * @param measuresusertool $dashboard
     * @return bool|string|void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function render_measuresusertool(measuresusertool $dashboard) {

        return $this->render_from_template('block_course_statistics/admin/measurespertool/usersmain' ,
                $dashboard->export_for_template($this));

    }

    /**
     * Measures for the courses forums main page.
     * @param measuresfortheforum $dashboard
     * @return bool|string|void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function render_measuresfortheforum(measuresfortheforum $dashboard) {

        return $this->render_from_template('block_course_statistics/admin/measuresfortheforum/main' ,
                $dashboard->export_for_template($this));
    }

    /**
     * Measures for the topics (discussions) in a forun main page.
     * @param measuresforumtopics $dashboard
     * @return bool|string|void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function render_measuresforumtopics(measuresforumtopics $dashboard) {

        return $this->render_from_template('block_course_statistics/admin/measuresfortheforum/topicsmain' ,
                $dashboard->export_for_template($this));
    }

    /**
     * Measures for the users in a topic (discussions) main page.
     * @param measuresforumusers $dashboard
     * @return bool|string|void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function render_measuresforumusers(measuresforumusers $dashboard) {

        return $this->render_from_template('block_course_statistics/admin/measuresfortheforum/usersmain' ,
                $dashboard->export_for_template($this));
    }

    /**
     * Measures for the course forums main page.
     * @param measurescourseforums $dashboard
     * @return bool|string
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function render_measurescourseforums(measurescourseforums $dashboard) {

        return $this->render_from_template('block_course_statistics/admin/measuresfortheforum/forumsmain' ,
                $dashboard->export_for_template($this));
    }

    /**
     * Measures in Quizzes main page.
     * @param measuresinquizzes $dashboard
     * @return bool|string|void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function render_measuresinquizzes(measuresinquizzes $dashboard) {

        return $this->render_from_template('block_course_statistics/admin/measuresinquizzes/main',
                $dashboard->export_for_template($this));
    }


    /**
     * Measures of Quizzes in a Course.
     * @param measurescoursequizzes $dashboard
     * @return bool|string|void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function render_measurescoursequizzes(measurescoursequizzes $dashboard) {

        return $this->render_from_template('block_course_statistics/admin/measuresinquizzes/quizmain' ,
                $dashboard->export_for_template($this));
    }

    /**
     * Measures of users in a Quiz of a course.
     * @param measuresquizusers $dashboard
     * @return bool|string|void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function render_measuresquizusers(measuresquizusers $dashboard) {

        return $this->render_from_template('block_course_statistics/admin/measuresinquizzes/usersmain' ,
                $dashboard->export_for_template($this));
    }
}

