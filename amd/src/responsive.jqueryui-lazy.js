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

/*! jQuery UI integration for DataTables' Responsive
 * ©2015 SpryMedia Ltd - datatables.net/license
 */

(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD
        define(['jquery', 'block_course_statistics/jquery.dataTables-lazy', 'block_course_statistics/dataTables.responsive-lazy'], function ($) {
            return factory($, window, document);
        });
    } else if (typeof exports === 'object') {
        // CommonJS
        module.exports = function (root, $) {
            if (!root) {
                root = window;
            }

            if (!$ || !$.fn.dataTable) {
                $ = require('datatables.net-jqui')(root, $).$;
            }

            if (!$.fn.dataTable.Responsive) {
                require('datatables.net-responsive')(root, $);
            }

            return factory($, root, root.document);
        };
    } else {
        // Browser
        factory(jQuery, window, document);
    }
}(function ($, window, document, undefined) {
    'use strict';
    var DataTable = $.fn.dataTable;


    var _display = DataTable.Responsive.display;
    var _original = _display.modal;

    _display.modal = function (options) {
        return function (row, update, render) {
            if (!$.fn.dialog) {
                _original(row, update, render);
            } else {
                if (!update) {
                    $('<div/>')
                        .append(render())
                        .appendTo('body')
                        .dialog($.extend(true, {
                            title: options && options.header ? options.header(row) : '',
                            width: 500
                        }, options.dialog));
                }
            }
        };
    };


    return DataTable.Responsive;
}));
