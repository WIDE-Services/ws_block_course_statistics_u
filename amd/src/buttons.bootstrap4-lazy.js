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

(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD
        define(['jquery', 'block_course_statistics/dataTables.bootstrap4-lazy', "block_course_statistics/dataTables.buttons-lazy"], function ($) {
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

            if (!$.fn.dataTable.Buttons) {
                require('datatables.net-buttons')(root, $);
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

    $.extend(true, DataTable.Buttons.defaults, {
        dom: {
            container: {
                className: 'dt-buttons btn-group'
            },
            button: {
                className: 'btn btn-secondary'
            },
            collection: {
                tag: 'div',
                className: 'dt-button-collection dropdown-menu',
                button: {
                    tag: 'a',
                    className: 'dt-button dropdown-item',
                    active: 'active',
                    disabled: 'disabled'
                }
            }
        },
        buttonCreated: function (config, button) {
            return config.buttons ?
                $('<div class="btn-group"/>').append(button) :
                button;
        }
    });

    DataTable.ext.buttons.collection.className += ' dropdown-toggle';
    DataTable.ext.buttons.collection.rightAlignClassName = 'dropdown-menu-right';

    return DataTable.Buttons;
}));
