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
 * Activity time block definition.
 *
 * @package    block_course_statistics
 * @copyright  2022 onwards WIDE Services  {@link https://www.wideservices.gr}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_course_statistics extends block_base {

    /**
     * Applicable formats.
     *
     * @return array
     */
    public function applicable_formats() {
        return [
                'admin' => false,
                'site-index' => false,
                'course-view' => true,
                'mod' => false,
                'my' => false,
        ];
    }

    /**
     * init method
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_course_statistics');
    }

    /**
     * If block has settings enabled.
     * @return bool
     */
    public function has_config() {
        return true;
    }


    /**
     * specialization method
     */
    public function specialization() {
        // Previous block versions didn't have config settings.
        if ($this->config === null) {
            $this->config = new stdClass();
        }
        // Set always show_activitytime config settings to avoid errors.
        if (!isset($this->config->show_activitytime)) {
            $this->config->show_activitytime = 0;
        }
    }


    /**
     * Controls whether multiple block instances are allowed.
     *
     * @return bool
     */
    public function instance_allow_multiple(): bool {
        return false;
    }

    /**
     * Main Content of block
     * @return stdClass|stdObject|string|null
     * @throws coding_exception
     * @throws moodle_exception
     * @throws require_login_exception
     */
    public function get_content() {
        global $OUTPUT , $USER;

        require_login();

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        // The content can be viewed only by the manager, teacher or editing teacher.
        if (has_capability('block/course_statistics:admin', context_course::instance($this->page->course->id))
                ||  has_capability('block/course_statistics:teacher',
                        context_course::instance($this->page->course->id), $USER->id)
        ) {

            $this->content->footer .= html_writer::tag('hr', null);
            $this->content->footer .= html_writer::tag('p',
                    get_string('access_info', 'block_course_statistics'));

            $url = new moodle_url('/blocks/course_statistics/pages/admin/generalmeasures/index.php', [
                    'courseid' => $this->page->course->id,
            ]);

            $this->content->footer .= $OUTPUT->single_button($url,
                    get_string('access_button', 'block_course_statistics'), 'get');

        }

        return $this->content;
    }

}

