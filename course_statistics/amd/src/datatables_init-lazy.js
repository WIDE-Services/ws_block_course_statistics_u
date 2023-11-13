define(
    [
        'jquery',
        'core/templates',
        'core/notification',
        'block_course_statistics/jquery.dataTables-lazy',
        'block_course_statistics/dataTables.responsive-lazy',
        'block_course_statistics/responsive.bootstrap4-lazy',
        'block_course_statistics/dataTables.buttons-lazy',
        'block_course_statistics/dataTables.bootstrap4-lazy',
        'block_course_statistics/dataTables.select-lazy',
        'block_course_statistics/buttons.bootstrap4-lazy',
        'block_course_statistics/buttons.print-lazy',
        'block_course_statistics/buttons.colVis-lazy',
        'block_course_statistics/buttons.html5-lazy',
        'block_course_statistics/jszip-lazy',
        'block_course_statistics/pdfmake-lazy'
    ],
    function ($) {

        function initializeDataTable(config) {

            var defaultConfig = {
                orderCellsTop: true,
                responsive: {
                    details: {
                        type: 'column',
                        target: 2
                    }
                },
                "lengthChange": true,
                columnDefs: [
                    {
                        orderable: false,
                        className: 'select-checkbox select-all-rows-checkbox noVis',
                        targets: 0
                    },
                    {
                        className: 'control noVis',
                        orderable: false,
                        targets: 0
                    },
                ],
                select: {
                    style: 'multi',
                    selector: '.select-checkbox'
                },
                dom: '<B<lf><t>ip>',
                buttons: [{

                    extend: 'collection',
                    className: 'exportButton',
                    text: M.util.get_string('export', 'block_course_statistics'),

                    buttons: [
                        {
                            extend: 'copy',
                            exportOptions: {
                                columns: ':visible',
                                //columns: [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
                                //rows: ':visible'
                            }
                        },
                        {
                            extend: 'print',
                            exportOptions: {
                                columns: ':visible',
                                //columns: [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
                                //rows: ':visible'
                            }
                        },
                        {
                            extend: 'excel',
                            exportOptions: {
                                columns: ':visible',
                                // columns: [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
                                //rows: ':visible'
                            }
                        },
                        {
                            extend: 'csv',
                            exportOptions: {
                                columns: ':visible',
                                //columns: [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
                                //rows: ':visible'
                            }
                        },
                        {
                            extend: 'pdf',
                            exportOptions: {
                                columns: ':visible',
                                rows: ':visible'
                            }
                        }

                    ]

                },
                    {
                        extend: 'colvis',
                        columns: ':not(.noVis)'
                    }
                ],
                ...config
            };

            $(document).ready(function () {

                $('.loader').show();

                $('.statistics thead tr').clone(true).appendTo('.statistics thead');
                $('.statistics thead tr:eq(1) th').each(function (i) {
                    var filter = M.util.get_string('filter' , 'block_course_statistics');

                    var title = filter + " " +$(this).text();

                    $(this).html('<input type="text" placeholder="' + title + '" />');
                    $('input', this).on('keyup change', function () {

                        if ($('.statistics').DataTable().column(i).search() !== this.value) {
                            $('.statistics').DataTable()
                                .column(i)
                                /*.search(exact_search, true, false)*/
                                .search(this.value)
                                .draw();
                        }
                    });
                });
                $('.statistics thead tr:eq(1) th:eq(0) input').remove();

                var table = $('.statistics').DataTable(defaultConfig);

                table.on('buttons-action', function(e, buttonApi) {
                    var copybtn = M.util.get_string('copy', 'block_course_statistics');
                    if (buttonApi.text() == copybtn) {
                        alert('Copy option selected!');
                    }
                });

                table.columns().every(function () {

                    var that = this;
                    $('input', this.header()).on('keyup change', function () {
                        if (that.search() !== this.value) {
                            that
                                .search(this.value)
                                .draw();
                        }
                    });

                    let n = 0;
                    $(".number").each(function () {
                        $(this).html(++n);
                    });

                });

                $('.statistics thead tr:eq(0) th').each(function () {
                    var display = $(this).css('display');
                    var position = $(this).index();

                    if (display == 'none') {
                        $('thead tr:eq(1)').find('th:eq(' + position + ') ').addClass('hidden');

                    } else {
                        $('thead tr:eq(1)').find('th:eq(' + position + ') ').removeClass('hidden');

                    }
                });

                $(window).resize(function () {
                    $('.statistics thead tr:eq(0) th').each(function () {
                        var display = $(this).css('display');
                        var position = $(this).index();

                        if (display == 'none') {
                            $('thead tr:eq(1)').find('th:eq(' + position + ') ').addClass('hidden');

                        } else {
                            $('thead tr:eq(1)').find('th:eq(' + position + ') ').removeClass('hidden');

                        }
                    });
                });

                table.on("click", "th.select-all-rows-checkbox", function () {
                    if ($("th.select-all-rows-checkbox").hasClass("selected")) {
                        table.rows().deselect();
                        $("th.select-all-rows-checkbox").removeClass("selected");
                    } else {
                        table.rows().select();
                        $("th.select-all-rows-checkbox").addClass("selected");
                    }
                }).on("select deselect", function () {
                    ("Some selection or deselection going on")
                    if (table.rows({
                        selected: true
                    }).count() !== table.rows().count()) {
                        $("th.select-all-rows-checkbox").removeClass("selected");
                    } else {
                        $("th.select-all-rows-checkbox").addClass("selected");
                    }
                });

                if ($('.statistics').length) {
                    $('.loader').hide();
                }


                $('thead tr th input').click(function (e) {
                    e.stopPropagation();
                });

            });//end document.ready

        }

        return {
            initializeDataTable: initializeDataTable
        };
    });