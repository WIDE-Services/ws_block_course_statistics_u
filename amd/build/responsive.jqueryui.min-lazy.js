/**
 * This file is part of Moodle - http://moodle.org/
 *
 * Moodle is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Moodle is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    block_course_statistics
 * @copyright  2023 onwards WIDE Services {@link https://www.wideservices.gr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */ /*! jQuery UI integration for DataTables' Responsive
 * ©2015 SpryMedia Ltd - datatables.net/license
 */ !function(e){"function"==typeof define&&define.amd?define(["jquery","block_course_statistics/jquery.dataTables-lazy","block_course_statistics/dataTables.responsive-lazy"],function(t){return e(t,window,document)}):"object"==typeof exports?module.exports=function(t,a){return t||(t=window),a&&a.fn.dataTable||(a=require("datatables.net-jqui")(t,a).$),a.fn.dataTable.Responsive||require("datatables.net-responsive")(t,a),e(a,t,t.document)}:e(jQuery,window,document)}(function(e,t,a,n){"use strict";var o=e.fn.dataTable,s=o.Responsive.display,i=s.modal;return s.modal=function(t){return function(a,n,o){e.fn.dialog?n||e("<div/>").append(o()).appendTo("body").dialog(e.extend(!0,{title:t&&t.header?t.header(a):"",width:500},t.dialog)):i(a,n,o)}},o.Responsive});