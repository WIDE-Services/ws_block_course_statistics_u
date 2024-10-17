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
        define(['jquery', 'block_course_statistics/dataTables.jqueryui-lazy', 'block_course_statistics/dataTables.buttons-lazy'], function ($) {
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
                className: 'dt-buttons ui-buttonset'
            },
            button: {
                className: 'dt-button ui-button ui-state-default ui-button-text-only',
                disabled: 'ui-state-disabled',
                active: 'ui-state-active'
            },
            buttonLiner: {
                tag: 'span',
                className: 'ui-button-text'
            }
        }
    });

    DataTable.ext.buttons.collection.text = function (dt) {
        return dt.i18n('buttons.collection', 'Collection <span class="ui-button-icon-primary ui-icon ui-icon-triangle-1-s"/>');
    };


    return DataTable.Buttons;
}));
