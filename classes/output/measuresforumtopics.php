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
namespace block_course_statistics\output;

use moodle_url;
use renderable;
use renderer_base;
use templatable;

/**
 * Class main
 */
class measuresforumtopics implements renderable, templatable {

    /**
     * @var mixed|null
     */
    protected $courseid;
    /**
     * @var int|null
     */
    protected $instanceid;
    /**
     * @var array|null
     */
    protected $form;
    /**
     * @var array|null
     */
    protected $topicsdata;
    /**
     * @var bool
     */
    protected $searchperiod;
    /**
     * @var int|null
     */
    protected $from;
    /**
     * @var int|null
     */
    protected $to;
    /**
     * @var bool
     */
    protected $access;

    /**
     * Construct
     * @param array $params
     * @param array $form
     * @param array $topicsdata
     * @param bool $searchperiod
     * @param int $from
     * @param int $to
     * @param bool $access
     */
    public function __construct($params = null ,  $form = null , $topicsdata = null ,
            $searchperiod = false , $from = null , $to = null , $access = false) {

        $this->courseid = (isset($params['courseid'])) ? $params['courseid'] : null;
        $this->form = $form;
        $this->topicsdata = $topicsdata;
        $this->searchperiod = $searchperiod;
        $this->from = $from;
        $this->to = $to;
        $this->access = $access;

    }

    /**
     * Export for template
     *
     * @param renderer_base $output
     * @return array
     * @throws \coding_exception
     */
    public function export_for_template(renderer_base $output) {

        return $data = [

                'header' => get_string('capability_admin' , 'block_course_statistics'),
                'iconcourse' => new moodle_url('/blocks/course_statistics/assets/dashboard/course.png'),
                'urlcourse' => new moodle_url('/blocks/course_statistics/pages/admin/generalmeasures/index.php', [
                        'courseid' => $this->courseid,
                ]),
                'iconactivity' => new moodle_url('/blocks/course_statistics/assets/dashboard/activity.png'),
                'urlactivity' => new moodle_url('/blocks/course_statistics/pages/admin/measurespertool/index.php', [
                        'courseid' => $this->courseid,
                ]),
                'iconforum' => new moodle_url('/blocks/course_statistics/assets/dashboard/forum.png'),
                'urlforum' => new moodle_url('/blocks/course_statistics/pages/admin/measuresfortheforum/index.php', [
                        'courseid' => $this->courseid,
                ]),
                'iconquiz' => new moodle_url('/blocks/course_statistics/assets/dashboard/quiz.png'),
                'urlrquiz' => new moodle_url('/blocks/course_statistics/pages/admin/measuresfortheforum/index.php', [
                        'courseid' => $this->courseid,
                ]),

                'viewusers' =>
                        new moodle_url('/blocks/course_statistics/pages/admin/measuresfortheforum/index.php?viewusers=1'),

                'bcmbcourses' =>
                        new moodle_url(
                                '/blocks/course_statistics/pages/admin/measuresfortheforum/index.php?courseid='.
                                $this->courseid
                        ),

                'bcmbforums' =>
                        new moodle_url(
                                '/blocks/course_statistics/pages/admin/measuresfortheforum/index.php?viewforum=1'

                        ),

                'paramcourseid' => $this->courseid,

                'filterform' => $this->form,
                'current_lang' => current_language(),
                'topicsdata' => $this->topicsdata,

                'searchperiod' => $this->searchperiod,

                'from' => $this->from,

                'to' => $this->to,

                'access' => $this->access,

        ];
    }
}

