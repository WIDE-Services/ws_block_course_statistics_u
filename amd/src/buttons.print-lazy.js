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
        define(['jquery', 'block_course_statistics/jquery.dataTables-lazy', 'block_course_statistics/dataTables.buttons-lazy'], function ($) {
            return factory($, window, document);
        });
    } else if (typeof exports === 'object') {
        // CommonJS
        module.exports = function (root, $) {
            if (!root) {
                root = window;
            }

            if (!$ || !$.fn.dataTable) {
                $ = require('datatables.net')(root, $).$;
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


    var _link = document.createElement('a');

    /**
     * Clone link and style tags, taking into account the need to change the source
     * path.
     *
     * @param  {node}     el Element to convert
     */
    var _styleToAbs = function (el) {
        var url;
        var clone = $(el).clone()[0];
        var linkHost;

        if (clone.nodeName.toLowerCase() === 'link') {
            clone.href = _relToAbs(clone.href);
        }

        return clone.outerHTML;
    };

    /**
     * Convert a URL from a relative to an absolute address so it will work
     * correctly in the popup window which has no base URL.
     *
     * @param  {string} href URL
     */
    var _relToAbs = function (href) {
        // Assign to a link on the original page so the browser will do all the
        // hard work of figuring out where the file actually is
        _link.href = href;
        var linkHost = _link.host;

        // IE doesn't have a trailing slash on the host
        // Chrome has it on the pathname
        if (linkHost.indexOf('/') === -1 && _link.pathname.indexOf('/') !== 0) {
            linkHost += '/';
        }

        return _link.protocol + "//" + linkHost + _link.pathname + _link.search;
    };


    DataTable.ext.buttons.print = {
        className: 'buttons-print',

        text: function (dt) {
            return dt.i18n('buttons.print', 'Print');
        },

        action: function (e, dt, button, config) {
            var data = dt.buttons.exportData(
                $.extend({decodeEntities: false}, config.exportOptions) // XSS protection
            );
            var exportInfo = dt.buttons.exportInfo(config);
            var columnClasses = dt
                .columns(config.exportOptions.columns)
                .flatten()
                .map(function (idx) {
                    return dt.settings()[0].aoColumns[dt.column(idx).index()].sClass;
                })
                .toArray();

            var addRow = function (d, tag) {
                var str = '<tr>';

                for (var i = 0, ien = d.length; i < ien; i++) {
                    // null and undefined aren't useful in the print output
                    var dataOut = d[i] === null || d[i] === undefined ?
                        '' :
                        d[i];
                    var classAttr = columnClasses[i] ?
                        'class="' + columnClasses[i] + '"' :
                        '';

                    str += '<' + tag + ' ' + classAttr + '>' + dataOut + '</' + tag + '>';
                }

                return str + '</tr>';
            };

            // Construct a table for printing
            var html = '<table class="' + dt.table().node().className + '">';

            if (config.header) {
                html += '<thead>' + addRow(data.header, 'th') + '</thead>';
            }

            html += '<tbody>';
            for (var i = 0, ien = data.body.length; i < ien; i++) {
                html += addRow(data.body[i], 'td');
            }
            html += '</tbody>';

            if (config.footer && data.footer) {
                html += '<tfoot>' + addRow(data.footer, 'th') + '</tfoot>';
            }
            html += '</table>';

            // Open a new window for the printable table
            var win = window.open('', '');
            win.document.close();

            // Inject the title and also a copy of the style and link tags from this
            // document so the table can retain its base styling. Note that we have
            // to use string manipulation as IE won't allow elements to be created
            // in the host document and then appended to the new window.
            var head = '<title>' + exportInfo.title + '</title>';
            $('style, link').each(function () {
                head += _styleToAbs(this);
            });

            try {
                win.document.head.innerHTML = head; // Work around for Edge
            } catch (e) {
                $(win.document.head).html(head); // Old IE
            }

            // Inject the table and other surrounding information
            win.document.body.innerHTML =
                '<h1>' + exportInfo.title + '</h1>' +
                '<div>' + (exportInfo.messageTop || '') + '</div>' +
                html +
                '<div>' + (exportInfo.messageBottom || '') + '</div>';

            $(win.document.body).addClass('dt-print-view');

            $('img', win.document.body).each(function (i, img) {
                img.setAttribute('src', _relToAbs(img.getAttribute('src')));
            });

            if (config.customize) {
                config.customize(win, config, dt);
            }

            // Allow stylesheets time to load
            var autoPrint = function () {
                if (config.autoPrint) {
                    win.print(); // blocking - so close will not
                    win.close(); // execute until this is done
                }
            };

            if (navigator.userAgent.match(/Trident\/\d.\d/)) { // IE needs to call this without a setTimeout
                autoPrint();
            } else {
                win.setTimeout(autoPrint, 1000);
            }
        },

        title: '*',

        messageTop: '*',

        messageBottom: '*',

        exportOptions: {},

        header: true,

        footer: false,

        autoPrint: true,

        customize: null
    };


    return DataTable.Buttons;
}));
