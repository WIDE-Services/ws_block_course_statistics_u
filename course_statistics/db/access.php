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
 * Access file
 * @package    block_course_statistics
 * @copyright  2023 onwards WIDE Services  {@link https://www.wideservices.gr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(
        'block/course_statistics:addinstance' => array(
                'riskbitmask' => RISK_SPAM | RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_BLOCK,
                'archetypes' => array(
                        'editingteacher' => CAP_ALLOW,
                        'manager' => CAP_ALLOW
                ), 'clonepermissionsfrom' => 'moodle/site:manageblocks'
        ),

        'block/course_statistics:myaddinstance' => array(
                'riskbitmask' => RISK_SPAM | RISK_XSS,
                'captype' => 'write',
                'contextlevel' => CONTEXT_BLOCK,
                'archetypes' => array(
                        'teacher' => CAP_ALLOW,
                        'editingteacher' => CAP_ALLOW,
                        'manager' => CAP_ALLOW
                )
        ),

        'block/course_statistics:admin' => array(
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(

                        'manager' => CAP_ALLOW
                )
        ),

        'block/course_statistics:teacher' => array(
                'captype' => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes' => array(

                        'teacher' => CAP_ALLOW,
                        'editingteacher' => CAP_ALLOW,
                )
        )

);
