define(
    [
        'jquery',
        'core/templates',
        'core/notification',
        'block_course_statistics/jquery.dataTables-lazy',
        'block_course_statistics/dataTables.responsive-lazy',
        'block_course_statistics/responsive.bootstrap-lazy',
        'block_course_statistics/responsive.bootstrap4-lazy',
        'block_course_statistics/dataTables.bootstrap-lazy',
        'block_course_statistics/dataTables.buttons-lazy',
        'block_course_statistics/dataTables.bootstrap4-lazy',
        'block_course_statistics/dataTables.select-lazy',
        'block_course_statistics/buttons.bootstrap-lazy',
        'block_course_statistics/buttons.bootstrap4-lazy',
        'block_course_statistics/buttons.print-lazy',
        'block_course_statistics/buttons.colVis-lazy',
        'block_course_statistics/buttons.html5-lazy',
        'block_course_statistics/jszip-lazy',
        'block_course_statistics/pdfmake-lazy'
    ],
    function ($) {

        var init = function () {

            $('.loader').show();

            $('.statistics thead tr').clone(true).appendTo('.statistics thead');
            $('.statistics thead tr:eq(1) th').each(function (i) {
                var title = $(this).text();
                $(this).html('<input type="text" placeholder="Filter ' + title + '" />');
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


            $(document).ready(function () {

                var table = $('.statistics').DataTable({
                    orderCellsTop: true,
                    responsive: {
                        details: {
                            type: 'column',
                            target: 1
                        }
                    },
                    "lengthChange": true,
                    columnDefs: [
                        {
                            orderable: false,
                            className: 'select-checkbox select-all-rows-checkbox',
                            targets: 0
                        },
                        {
                            className: 'control',
                            orderable: false,
                            targets: 0
                        },
                    ],
                    select: {
                        style: 'multi',
                        selector: '.select-checkbox'
                    },
                    order: [
                        [2, 'desc']
                    ],

                    dom: '<B<lf><t>ip>',
                    buttons: [{

                        extend: 'collection',
                        className: 'exportButton',
                        text: 'Export',

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
                        'colvis']
                });//end DataTable

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

        };//end init

        return {
            init: init
        };
    });