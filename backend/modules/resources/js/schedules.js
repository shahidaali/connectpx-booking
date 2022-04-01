jQuery(function($) {
    'use strict';
    let
        $schedulesList   = $('#connectpx_booking-schedules-list'),
        $checkAllButton     = $('#connectpx_booking-check-all'),
        $idFilter           = $('#connectpx_booking-filter-id'),
        $scheduleDateFilter = $('#connectpx_booking-filter-date'),
        $creationDateFilter = $('#connectpx_booking-filter-creation-date'),
        $customerFilter     = $('#connectpx_booking-filter-customer'),
        $serviceFilter      = $('#connectpx_booking-filter-service'),
        $statusFilter       = $('#connectpx_booking-filter-status'),
        $searchQuery        = $('#connectpx_booking-search-query'),
        $newScheduleBtn  = $('#connectpx_booking-new-schedule'),
        $printDialog        = $('#connectpx_booking-print-dialog'),
        $printSelectAll     = $('#connectpx_booking-js-print-select-all', $printDialog),
        $printButton        = $(':submit',$printDialog),
        $exportDialog       = $('#connectpx_booking-export-dialog'),
        $exportSelectAll    = $('#connectpx_booking-js-export-select-all', $exportDialog),
        $exportForm         = $('form', $exportDialog),
        $showDeleteConfirmation = $('#connectpx_booking-js-show-confirm-deletion'),
        isMobile            = false,
        urlParts            = document.URL.split('#'),
        columns             = [],
        order               = [],
        pickers = {
            dateFormat:       'YYYY-MM-DD',
            scheduleDate: {
                startDate: moment().startOf('month'),
                endDate  : moment().endOf('month'),
            },
            creationDate: {
                startDate: moment(),
                endDate  : moment().add(100, 'years'),
            },
        }
    ;

    try {
        document.createEvent("TouchEvent");
        isMobile = true;
    } catch (e) {

    }

    $('.connectpx_booking-js-select').val(null);

    // Apply filter from anchor
    if (urlParts.length > 1) {
        urlParts[1].split('&').forEach(function (part) {
            var params = part.split('=');
            if (params[0] == 'schedule-date') {
                if (params['1'] == 'any') {
                    $scheduleDateFilter
                        .data('date', 'any').find('span')
                        .html(ConnectpxBookingL10n.dateRange.anyTime);
                } else {
                    pickers.scheduleDate.startDate = moment(params['1'].substring(0, 10));
                    pickers.scheduleDate.endDate = moment(params['1'].substring(11));
                    $scheduleDateFilter
                        .data('date', pickers.scheduleDate.startDate.format(pickers.dateFormat) + ' - ' + pickers.scheduleDate.endDate.format(pickers.dateFormat))
                        .find('span')
                        .html(pickers.scheduleDate.startDate.format(ConnectpxBookingL10n.dateRange.format) + ' - ' + pickers.scheduleDate.endDate.format(ConnectpxBookingL10n.dateRange.format));
                }
            } else if (params[0] == 'created-date') {
                pickers.creationDate.startDate = moment(params['1'].substring(0, 10));
                pickers.creationDate.endDate = moment(params['1'].substring(11));
                $creationDateFilter
                    .data('date', pickers.creationDate.startDate.format(pickers.dateFormat) + ' - ' + pickers.creationDate.endDate.format(pickers.dateFormat))
                    .find('span')
                    .html(pickers.creationDate.startDate.format(ConnectpxBookingL10n.dateRange.format) + ' - ' + pickers.creationDate.endDate.format(ConnectpxBookingL10n.dateRange.format));
            } else {
                $('#connectpx_booking-filter-' + params[0]).val(params[1]);
            }
        });
    } else {
        $.each(ConnectpxBookingL10n.datatables.schedules.settings.filter, function (field, value) {
            if (value != '') {
                $('#connectpx_booking-filter-' + field).val(value);
            }
            // check if select has correct values
            if ($('#connectpx_booking-filter-' + field).prop('type') == 'select-one') {
                if ($('#connectpx_booking-filter-' + field + ' option[value="' + value + '"]').length == 0) {
                    $('#connectpx_booking-filter-' + field).val(null);
                }
            }
        });
    }

    // Ladda.bind($('button[type=submit]', $exportForm).get(0), {timeout: 2000});

    /**
     * Init table columns.
     */
    $.each(ConnectpxBookingL10n.datatables.schedules.settings.columns, function (column, show) {
        if (show) {
            switch (column) {
                case 'customer_detail':
                    columns.push({
                        data: 'customer_detail',
                        render: function ( data, type, row, meta ) {
                            if (row.customer_detail) {
                                return data;
                            }
                            return '';
                        }
                    });
                    break;
                case 'schedule_detail':
                    columns.push({
                        data: 'schedule_detail',
                        render: function ( data, type, row, meta ) {
                            if (row.schedule_detail) {
                                return data;
                            }
                            return '';
                        }
                    });
                    break;
                case 'total_appointments':
                    columns.push({
                        data: 'total_appointments',
                        render: function ( data, type, row, meta ) {
                            if (row.total_appointments) {
                                return data;
                            }
                            return '';
                        }
                    });
                    break;
                case 'service_title':
                    columns.push({
                        data: 'service.title',
                        render: function ( data, type, row, meta ) {
                            data = $.fn.dataTable.render.text().display(data);
                            return data;
                        }
                    });
                    break;
                default:
                    columns.push({data: column, render: $.fn.dataTable.render.text()});
                    break;
            }
        }
    });
    columns.push({
        responsivePriority: 1,
        orderable : false,
        width     : 120,
        render    : function (data, type, row, meta) {
            return '<button type="button" class="btn btn-default" data-action="edit"><i class="far fa-fw fa-edit mr-lg-1"></i><span class="d-none d-lg-inline">' + ConnectpxBookingL10n.edit + '…</span></button>';
        }
    });
    columns.push({
        responsivePriority: 1,
        orderable         : false,
        render            : function (data, type, row, meta) {
            const cb_id = 'connectpx_booking-dt-a-' + row.id;
            return '<div class="custom-control custom-checkbox">' +
                '<input value="' + row.id + '" data-schedule="' + row.id + '" id="' + cb_id + '" type="checkbox" class="custom-control-input">' +
                '<label for="' + cb_id + '" class="custom-control-label"></label>' +
                '</div>';
        }
    });

    columns[0].responsivePriority = 0;

    $.each(ConnectpxBookingL10n.datatables.schedules.settings.order, function (_, value) {
        const index = columns.findIndex(function (c) { return c.data === value.column; });
        if (index !== -1) {
            order.push([index, value.order]);
        }
    });
    /**
     * Init DataTables.
     */
    var dt = $schedulesList.DataTable({
        order       : order,
        info        : false,
        searching   : false,
        lengthChange: false,
        processing  : true,
        responsive  : true,
        pageLength  : 25,
        pagingType  : 'numbers',
        serverSide  : true,
        drawCallback: function( settings ) {
            // $('[data-toggle="connectpx_booking-popover"]', $schedulesList).on('click', function (e) {
            //     e.preventDefault();
            // }).connectpx_bookingPopover();
            dt.responsive.recalc();
        },
        ajax: {
            url : ajaxurl,
            type: 'POST',
            data: function (d) {
                return $.extend({action: 'connectpx_booking_get_schedules', csrf_token: ConnectpxBookingL10nGlobal.csrf_token}, {
                    filter: {
                        id: $idFilter.val(),
                        date: $scheduleDateFilter.data('date'),
                        created_date: $creationDateFilter.data('date'),
                        customer: $customerFilter.val(),
                        service: $serviceFilter.val(),
                        status: $statusFilter.val(),
                        search_query: $searchQuery.val(),
                    }
                }, d);
            }
        },
        columns: columns,
        dom       : "<'row'<'col-sm-12'tr>><'row float-left mt-3'<'col-sm-12'p>>",
        language: {
            zeroRecords: ConnectpxBookingL10n.zeroRecords,
            processing:  ConnectpxBookingL10n.processing
        }
    });

    // Show ratings in expanded rows.
    dt.on( 'responsive-display', function (e, datatable, row, showHide, update) {
        if (showHide) {
            // $('[data-toggle="connectpx_booking-popover"]', row.child()).on('click', function (e) {
            //     e.preventDefault();
            // }).connectpx_bookingPopover();
        }
    });

    /**
     * Add schedule.
     */
    $newScheduleBtn.on('click', function () {
        ConnectpxBookingScheduleDialog.showDialog(
            null,
            function(event) {
                dt.ajax.reload();
            }
        )
    });

    /**
     * Export.
     */
    $exportForm.on('submit', function () {
        $('[name="filter"]', $exportDialog).val(JSON.stringify({
            id          : $idFilter.val(),
            date        : $scheduleDateFilter.data('date'),
            created_date: $creationDateFilter.data('date'),
            customer    : $customerFilter.val(),
            service     : $serviceFilter.val(),
            status      : $statusFilter.val(),
        }));
        $exportDialog.connectpx_bookingModal('hide');

        return true;
    });

    $exportSelectAll
        .on('click', function () {
            let checked = this.checked;
            $('.connectpx_booking-js-columns input', $exportDialog).each(function () {
                $(this).prop('checked', checked);
            });
        });

    $('.connectpx_booking-js-columns input', $exportDialog)
        .on('change', function () {
            $exportSelectAll.prop('checked', $('.connectpx_booking-js-columns input:checked', $exportDialog).length == $('.connectpx_booking-js-columns input', $exportDialog).length);
        });

    /**
     * Print.
     */
    $printButton.on('click', function () {
        let columns = [];
        $('input:checked', $printDialog).each(function () {
            columns.push(this.value);
        });
        let config = {
            title: '&nbsp;',
            exportOptions: {
                columns: columns
            },
            customize: function (win) {
                win.document.firstChild.style.backgroundColor = '#fff';
                win.document.body.id = 'connectpx_booking_tbs';
                $(win.document.body).find('table').removeClass('collapsed');
            }
        };
        $.fn.dataTable.ext.buttons.print.action(null, dt, null, $.extend({}, $.fn.dataTable.ext.buttons.print, config));
    });

    $printSelectAll
        .on('click', function () {
            let checked = this.checked;
            $('.connectpx_booking-js-columns input', $printDialog).each(function () {
                $(this).prop('checked', checked);
            });
        });

    $('.connectpx_booking-js-columns input', $printDialog)
        .on('change', function () {
            $printSelectAll.prop('checked', $('.connectpx_booking-js-columns input:checked', $printDialog).length == $('.connectpx_booking-js-columns input', $printDialog).length);
        });

    /**
     * Select all schedules.
     */
    $checkAllButton.on('change', function () {
        $schedulesList.find('tbody input:checkbox').prop('checked', this.checked);
    });

    $schedulesList
        // On schedule select.
        .on('change', 'tbody input:checkbox', function () {
            $checkAllButton.prop('checked', $schedulesList.find('tbody input:not(:checked)').length == 0);
        })
        // Edit schedule.
        .on('click', '[data-action=edit]', function (e) {
            e.preventDefault();
            ConnectpxBookingScheduleDialog.showDialog(
                getDTRowData(this).id,
                function (event) {
                    dt.ajax.reload();
                }
            )
        });

    $showDeleteConfirmation.on('click', function () {
        let data = [],
            $checkboxes = $schedulesList.find('tbody input:checked');

        $checkboxes.each(function () {
            data.push({ca_id: this.value, id: $(this).data('schedule')});
        });

        new ConnectpxBookingConfirmDeletingSchedule({
                action: 'connectpx_booking_delete_customer_schedules',
                data: data,
                csrf_token: ConnectpxBookingL10nGlobal.csrf_token,
            },
            function(response) {dt.draw(false);}
        );
    });

    /**
     * Init date range pickers.
     */

    let
        pickerRanges1 = {},
        pickerRanges2 = {}
    ;
    pickerRanges1[ConnectpxBookingL10n.dateRange.anyTime]   = [moment(), moment().add(100, 'years')];
    pickerRanges1[ConnectpxBookingL10n.dateRange.yesterday] = [moment().subtract(1, 'days'), moment().subtract(1, 'days')];
    pickerRanges1[ConnectpxBookingL10n.dateRange.today]     = [moment(), moment()];
    pickerRanges1[ConnectpxBookingL10n.dateRange.tomorrow]  = [moment().add(1, 'days'), moment().add(1, 'days')];
    pickerRanges1[ConnectpxBookingL10n.dateRange.last_7]    = [moment().subtract(7, 'days'), moment()];
    pickerRanges1[ConnectpxBookingL10n.dateRange.last_30]   = [moment().subtract(30, 'days'), moment()];
    pickerRanges1[ConnectpxBookingL10n.dateRange.thisMonth] = [moment().startOf('month'), moment().endOf('month')];
    pickerRanges1[ConnectpxBookingL10n.dateRange.nextMonth] = [moment().add(1, 'month').startOf('month'), moment().add(1, 'month').endOf('month')];
    $.extend(pickerRanges2, pickerRanges1);


    $scheduleDateFilter.daterangepicker(
        {
            parentEl : $scheduleDateFilter.parent(),
            startDate: pickers.scheduleDate.startDate,
            endDate  : pickers.scheduleDate.endDate,
            ranges   : pickerRanges1,
            showDropdowns  : true,
            linkedCalendars: false,
            autoUpdateInput: false,
            locale: $.extend({},ConnectpxBookingL10n.dateRange, ConnectpxBookingL10n.datePicker)
        },
        function(start, end, label) {
            switch (label) {
                case ConnectpxBookingL10n.dateRange.anyTime:
                    $scheduleDateFilter
                        .data('date', 'any')
                        .find('span')
                        .html(ConnectpxBookingL10n.dateRange.anyTime);
                    break;
                default:
                    $scheduleDateFilter
                        .data('date', start.format(pickers.dateFormat) + ' - ' + end.format(pickers.dateFormat))
                        .find('span')
                        .html(start.format(ConnectpxBookingL10n.dateRange.format) + ' - ' + end.format(ConnectpxBookingL10n.dateRange.format));
            }
        }
    );

    $creationDateFilter.daterangepicker(
        {
            parentEl : $creationDateFilter.parent(),
            startDate: pickers.creationDate.startDate,
            endDate  : pickers.creationDate.endDate,
            ranges: pickerRanges2,
            showDropdowns  : true,
            linkedCalendars: false,
            autoUpdateInput: false,
            locale: $.extend(ConnectpxBookingL10n.dateRange, ConnectpxBookingL10n.datePicker)
        },
        function(start, end, label) {
            switch (label) {
                case ConnectpxBookingL10n.dateRange.anyTime:
                    $creationDateFilter
                        .data('date', 'any')
                        .find('span')
                        .html(ConnectpxBookingL10n.dateRange.createdAtAnyTime);
                    break;
                default:
                    $creationDateFilter
                        .data('date', start.format(pickers.dateFormat) + ' - ' + end.format(pickers.dateFormat))
                        .find('span')
                        .html(start.format(ConnectpxBookingL10n.dateRange.format) + ' - ' + end.format(ConnectpxBookingL10n.dateRange.format));
            }
        }
    );

    /**
     * On filters change.
     */
    $('.connectpx_booking-js-select')
        .select2({
            width: '100%',
            theme: 'bootstrap4',
            dropdownParent: '#connectpx_booking_tbs',
            allowClear: true,
            placeholder: '',
            language: {
                noResults: function() { return ConnectpxBookingL10n.no_result_found; }
            },
            matcher: function (params, data) {
                const term = $.trim(params.term).toLowerCase();
                if (term === '' || data.text.toLowerCase().indexOf(term) !== -1) {
                    return data;
                }

                let result = null;
                const search = $(data.element).data('search');
                search &&
                search.find(function (text) {
                    if (result === null && text.toLowerCase().indexOf(term) !== -1) {
                        result = data;
                    }
                });

                return result;
            }
        });

    $('.connectpx_booking-js-select-ajax')
        .select2({
            width: '100%',
            theme: 'bootstrap4',
            dropdownParent: '#connectpx_booking_tbs',
            allowClear: true,
            placeholder: '',
            language  : {
                noResults: function () { return ConnectpxBookingL10n.no_result_found; },
                searching: function () { return ConnectpxBookingL10n.searching; }
            },
            ajax: {
                url: ajaxurl,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    params.page = params.page || 1;
                    return {
                        action: this.action === undefined ? $(this).data('ajax--action') : this.action,
                        filter: params.term,
                        page: params.page,
                        csrf_token: ConnectpxBookingL10nGlobal.csrf_token
                    };
                }
            },
        });

    function getDTRowData(element) {
        let $el = $(element).closest('td');
        if ($el.hasClass('child')) {
            $el = $el.closest('tr').prev();
        }
        return dt.row($el).data();
    }

    $idFilter.on('keyup', function () { dt.ajax.reload(); });
    $scheduleDateFilter.on('apply.daterangepicker', function () { dt.ajax.reload(); });
    $creationDateFilter.on('apply.daterangepicker', function () { dt.ajax.reload(); });
    $customerFilter.on('change', function () { dt.ajax.reload(); });
    $serviceFilter.on('change', function () { dt.ajax.reload(); });
    $statusFilter.on('change', function () { dt.ajax.reload(); });

    var timeout = null;
    $searchQuery.on('keyup', function () {
        var value = $(this).val();
        clearTimeout(timeout);
        timeout = setTimeout(function(){
            if ( value.length >= 3 ) {
                dt.ajax.reload();
            }
            else if( value == '' ) {
                dt.ajax.reload();
            }
        }, 500);
    });
});