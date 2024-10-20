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
 */

define([
    "jquery",
    'block_course_statistics/jquery.dataTables-lazy',
    'block_course_statistics/dataTables.buttons-lazy'
], function (jQuery) {
    (function ($, DataTables) {

        $.extend(true, DataTables.Buttons.defaults, {
            dom: {
                container: {
                    className: "dt-buttons btn-group"
                },
                button: {
                    className: "btn btn-default"
                },
                collection: {
                    tag: "ul",
                    className: "dt-button-collection dropdown-menu",
                    button: {
                        tag: "li",
                        className: "dt-button"
                    },
                    buttonLiner: {
                        tag: "a",
                        className: ""
                    }
                }
            }
        });

        DataTables.ext.buttons.collection.text = function (dt) {
            return dt.i18n("buttons.collection", 'Collection <span class="caret"/>');
        };


    })(jQuery, jQuery.fn.dataTable);
});
