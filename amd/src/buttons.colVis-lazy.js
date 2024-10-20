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


    $.extend(DataTable.ext.buttons, {
        // A collection of column visibility buttons
        colvis: function (dt, conf) {
            return {
                extend: 'collection',
                text: function (dt) {
                    return dt.i18n('buttons.colvis', 'Column visibility');
                },
                className: 'buttons-colvis',
                buttons: [{
                    extend: 'columnsToggle',
                    columns: conf.columns,
                    columnText: conf.columnText
                }]
            };
        },

        // Selected columns with individual buttons - toggle column visibility
        columnsToggle: function (dt, conf) {
            var columns = dt.columns(conf.columns).indexes().map(function (idx) {
                return {
                    extend: 'columnToggle',
                    columns: idx,
                    columnText: conf.columnText
                };
            }).toArray();

            return columns;
        },

        // Single button to toggle column visibility
        columnToggle: function (dt, conf) {
            return {
                extend: 'columnVisibility',
                columns: conf.columns,
                columnText: conf.columnText
            };
        },

        // Selected columns with individual buttons - set column visibility
        columnsVisibility: function (dt, conf) {
            var columns = dt.columns(conf.columns).indexes().map(function (idx) {
                return {
                    extend: 'columnVisibility',
                    columns: idx,
                    visibility: conf.visibility,
                    columnText: conf.columnText
                };
            }).toArray();

            return columns;
        },

        // Single button to set column visibility
        columnVisibility: {
            columns: undefined, // column selector
            text: function (dt, button, conf) {
                return conf._columnText(dt, conf);
            },
            className: 'buttons-columnVisibility',
            action: function (e, dt, button, conf) {
                var col = dt.columns(conf.columns);
                var curr = col.visible();

                col.visible(conf.visibility !== undefined ?
                    conf.visibility :
                    !(curr.length ? curr[0] : false)
                );
            },
            init: function (dt, button, conf) {
                var that = this;
                button.attr('data-cv-idx', conf.columns);

                dt
                    .on('column-visibility.dt' + conf.namespace, function (e, settings) {
                        if (!settings.bDestroying && settings.nTable == dt.settings()[0].nTable) {
                            that.active(dt.column(conf.columns).visible());
                        }
                    })
                    .on('column-reorder.dt' + conf.namespace, function (e, settings, details) {
                        // Don't rename buttons based on column name if the button
                        // controls more than one column!
                        if (dt.columns(conf.columns).count() !== 1) {
                            return;
                        }

                        conf.columns = $.inArray(conf.columns, details.mapping);
                        button.attr('data-cv-idx', conf.columns);

                        // Reorder buttons for new table order
                        button
                            .parent()
                            .children('[data-cv-idx]')
                            .sort(function (a, b) {
                                return (a.getAttribute('data-cv-idx') * 1) - (b.getAttribute('data-cv-idx') * 1);
                            })
                            .appendTo(button.parent());
                    });

                this.active(dt.column(conf.columns).visible());
            },
            destroy: function (dt, button, conf) {
                dt
                    .off('column-visibility.dt' + conf.namespace)
                    .off('column-reorder.dt' + conf.namespace);
            },

            _columnText: function (dt, conf) {
                // Use DataTables' internal data structure until this is presented
                // is a public API. The other option is to use
                // `$( column(col).node() ).text()` but the node might not have been
                // populated when Buttons is constructed.
                var idx = dt.column(conf.columns).index();
                var title = dt.settings()[0].aoColumns[idx].sTitle
                    .replace(/\n/g, " ")        // remove new lines
                    .replace(/<br\s*\/?>/gi, " ")  // replace line breaks with spaces
                    .replace(/<select(.*?)<\/select>/g, "") // remove select tags, including options text
                    .replace(/<!\-\-.*?\-\->/g, "") // strip HTML comments
                    .replace(/<.*?>/g, "")   // strip HTML
                    .replace(/^\s+|\s+$/g, ""); // trim

                return conf.columnText ?
                    conf.columnText(dt, idx, title) :
                    title;
            }
        },


        colvisRestore: {
            className: 'buttons-colvisRestore',

            text: function (dt) {
                return dt.i18n('buttons.colvisRestore', 'Restore visibility');
            },

            init: function (dt, button, conf) {
                conf._visOriginal = dt.columns().indexes().map(function (idx) {
                    return dt.column(idx).visible();
                }).toArray();
            },

            action: function (e, dt, button, conf) {
                dt.columns().every(function (i) {
                    // Take into account that ColReorder might have disrupted our
                    // indexes
                    var idx = dt.colReorder && dt.colReorder.transpose ?
                        dt.colReorder.transpose(i, 'toOriginal') :
                        i;

                    this.visible(conf._visOriginal[idx]);
                });
            }
        },


        colvisGroup: {
            className: 'buttons-colvisGroup',

            action: function (e, dt, button, conf) {
                dt.columns(conf.show).visible(true, false);
                dt.columns(conf.hide).visible(false, false);

                dt.columns.adjust();
            },

            show: [],

            hide: []
        }
    });


    return DataTable.Buttons;
}));
