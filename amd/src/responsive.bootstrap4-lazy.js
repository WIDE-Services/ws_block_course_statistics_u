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

/*! Bootstrap 4 integration for DataTables' Responsive
 * ©2016 SpryMedia Ltd - datatables.net/license
 */

(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD
        define(['jquery', 'block_course_statistics/dataTables.bootstrap4-lazy', 'block_course_statistics/dataTables.responsive-lazy'], function ($) {
            return factory($, window, document);
        });
    } else if (typeof exports === 'object') {
        // CommonJS
        module.exports = function (root, $) {
            if (!root) {
                root = window;
            }

            if (!$ || !$.fn.dataTable) {
                $ = require('datatables.net-bs4')(root, $).$;
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
    var _modal = $(
        '<div class="modal fade dtr-bs-modal" role="dialog">' +
        '<div class="modal-dialog" role="document">' +
        '<div class="modal-content">' +
        '<div class="modal-header">' +
        '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
        '</div>' +
        '<div class="modal-body"/>' +
        '</div>' +
        '</div>' +
        '</div>'
    );

    _display.modal = function (options) {
        return function (row, update, render) {
            if (!$.fn.modal) {
                _original(row, update, render);
            } else {
                if (!update) {
                    if (options && options.header) {
                        var header = _modal.find('div.modal-header');
                        var button = header.find('button').detach();

                        header
                            .empty()
                            .append('<h4 class="modal-title">' + options.header(row) + '</h4>')
                            .append(button);
                    }

                    _modal.find('div.modal-body')
                        .empty()
                        .append(render());

                    _modal
                        .appendTo('body')
                        .modal();
                }
            }
        };
    };


    return DataTable.Responsive;
}));
