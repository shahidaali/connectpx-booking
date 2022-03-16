(function ($) {
    'use strict';
    window.connectpx_bookingCustomerInvoices = function () {
        let $container = $('.connectpx_booking-customer-invoices');
        console.log($container)
        if (!$container.length) {
            return;
        }

        // Invoices section
        function initInvoices($container) {
            var $invoices_table = $('.connectpx_booking-invoices-list', $container),
                invoice_columns = [],
                $invoiceDateFilter = $('#connectpx_booking-filter-date'),
                $invoiceDueDateFilter = $('#connectpx_booking-filter-due-date'),
                $invoiceStatusFilter = $('#connectpx_booking-filter-status'),
                row;
            console.log(ConnectpxBookingCustomerInvoicesL10n);
            Object.keys(ConnectpxBookingCustomerInvoicesL10n.invoice_columns).map(function(objectKey) {
                let column = ConnectpxBookingCustomerInvoicesL10n.invoice_columns[objectKey];
                switch (column) {
                    case 'actions':
                        invoice_columns.push({
                            data: 'actions',
                            responsivePriority: 3,
                            render: function ( data, type, row, meta ) {
                                return '<button type="button" class="btn btn-sm btn-default" data-action="show-invoice" data-id="' + row.id + '"><i class="far fa-fw fa-eye mr-lg-1"></i></button> <a type="button" class="btn btn-default" href="'+row.download_link+'" target="_blank"><i class="fa fa-download mr-lg-1"></i></a>';

                                return data;
                            }
                        });
                        break;
                    default:
                        invoice_columns.push({data: column, render: $.fn.dataTable.render.text()});
                    break;
                }
            });
            // Date range filter
            let pickerRanges = {};

            pickerRanges[ConnectpxBookingCustomerInvoicesL10n.dateRange.anyTime]   = [moment(), moment().add(100, 'years')];
            pickerRanges[ConnectpxBookingCustomerInvoicesL10n.dateRange.yesterday] = [moment().subtract(1, 'days'), moment().subtract(1, 'days')];
            pickerRanges[ConnectpxBookingCustomerInvoicesL10n.dateRange.today]     = [moment(), moment()];
            pickerRanges[ConnectpxBookingCustomerInvoicesL10n.dateRange.tomorrow]  = [moment().add(1, 'days'), moment().add(1, 'days')];
            pickerRanges[ConnectpxBookingCustomerInvoicesL10n.dateRange.last_7]    = [moment().subtract(7, 'days'), moment()];
            pickerRanges[ConnectpxBookingCustomerInvoicesL10n.dateRange.last_30]   = [moment().subtract(30, 'days'), moment()];
            pickerRanges[ConnectpxBookingCustomerInvoicesL10n.dateRange.thisMonth] = [moment().startOf('month'), moment().endOf('month')];
            pickerRanges[ConnectpxBookingCustomerInvoicesL10n.dateRange.nextMonth] = [moment().add(1, 'month').startOf('month'), moment().add(1, 'month').endOf('month')];
            $invoiceDateFilter.daterangepicker(
                {
                    parentEl : $invoiceDateFilter.closest('div'),
                    startDate: moment(),
                    endDate  : moment().add(100, 'years'),
                    ranges   : pickerRanges,
                    showDropdowns  : true,
                    linkedCalendars: false,
                    autoUpdateInput: false,
                    locale: $.extend({},ConnectpxBookingCustomerInvoicesL10n.dateRange, ConnectpxBookingCustomerInvoicesL10n.datePicker)
                },
                function(start, end, label) {
                    switch (label) {
                        case ConnectpxBookingCustomerInvoicesL10n.dateRange.anyTime:
                            $invoiceDateFilter
                            .data('date', 'any')
                            .find('span')
                            .html(ConnectpxBookingCustomerInvoicesL10n.dateRange.anyTime);
                            break;
                        default:
                            $invoiceDateFilter
                            .data('date', start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'))
                            .find('span')
                            .html(start.format(ConnectpxBookingCustomerInvoicesL10n.dateRange.format) + ' - ' + end.format(ConnectpxBookingCustomerInvoicesL10n.dateRange.format));
                    }
                }
            ).data('date', 'any').find('span')
            .html(ConnectpxBookingCustomerInvoicesL10n.dateRange.anyTime);

            $invoiceDateFilter.on('apply.daterangepicker', function () {
                invoices_datatable.ajax.reload();
            });

            // Date range filter
            let pickerRanges2 = {};

            pickerRanges2[ConnectpxBookingCustomerInvoicesL10n.dateRange.dueAnyTime]   = [moment(), moment().add(100, 'years')];
            pickerRanges2[ConnectpxBookingCustomerInvoicesL10n.dateRange.yesterday] = [moment().subtract(1, 'days'), moment().subtract(1, 'days')];
            pickerRanges2[ConnectpxBookingCustomerInvoicesL10n.dateRange.today]     = [moment(), moment()];
            pickerRanges2[ConnectpxBookingCustomerInvoicesL10n.dateRange.tomorrow]  = [moment().add(1, 'days'), moment().add(1, 'days')];
            pickerRanges2[ConnectpxBookingCustomerInvoicesL10n.dateRange.last_7]    = [moment().subtract(7, 'days'), moment()];
            pickerRanges2[ConnectpxBookingCustomerInvoicesL10n.dateRange.last_30]   = [moment().subtract(30, 'days'), moment()];
            pickerRanges2[ConnectpxBookingCustomerInvoicesL10n.dateRange.thisMonth] = [moment().startOf('month'), moment().endOf('month')];
            pickerRanges2[ConnectpxBookingCustomerInvoicesL10n.dateRange.nextMonth] = [moment().add(1, 'month').startOf('month'), moment().add(1, 'month').endOf('month')];
            $invoiceDueDateFilter.daterangepicker(
                {
                    parentEl : $invoiceDueDateFilter.closest('div'),
                    startDate: moment(),
                    endDate  : moment().add(100, 'years'),
                    ranges   : pickerRanges2,
                    showDropdowns  : true,
                    linkedCalendars: false,
                    autoUpdateInput: false,
                    locale: $.extend({},ConnectpxBookingCustomerInvoicesL10n.dateRange, ConnectpxBookingCustomerInvoicesL10n.datePicker)
                },
                function(start, end, label) {
                    switch (label) {
                        case ConnectpxBookingCustomerInvoicesL10n.dateRange.dueAnyTime:
                            $invoiceDueDateFilter
                            .data('date', 'any')
                            .find('span')
                            .html(ConnectpxBookingCustomerInvoicesL10n.dateRange.dueAnyTime);
                            break;
                        default:
                            $invoiceDueDateFilter
                            .data('date', start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'))
                            .find('span')
                            .html(start.format(ConnectpxBookingCustomerInvoicesL10n.dateRange.format) + ' - ' + end.format(ConnectpxBookingCustomerInvoicesL10n.dateRange.format));
                    }
                }
            ).data('date', 'any').find('span')
            .html(ConnectpxBookingCustomerInvoicesL10n.dateRange.dueAnyTime);

            $invoiceDueDateFilter.on('apply.daterangepicker', function () {
                invoices_datatable.ajax.reload();
            });

            $invoiceStatusFilter.on('change', function () {
                invoices_datatable.ajax.reload();
            });

            $('.connectpx_booking-js-select')
            .val(null)
            .select2({
                width: '100%',
                theme: 'bootstrap4',
                dropdownParent: '#connectpx_booking_tbs',
                allowClear: true,
                placeholder: '',
                language: {
                    noResults: function() { return ConnectpxBookingCustomerInvoicesL10n.no_result_found; }
                },
            });

            /**
             * Init DataTables.
             */
            var invoices_datatable = $invoices_table.DataTable({
                order: [[0, 'desc']],
                info: false,
                lengthChange: false,
                pageLength: 10,
                pagingType: 'numbers',
                searching: false,
                processing: true,
                responsive: true,
                serverSide: true,
                ajax: {
                    url: ConnectpxBookingCustomerInvoicesL10n.ajax_url,
                    type: 'POST',
                    data: function (d) {
                        return $.extend({
                            action: 'connectpx_booking_get_customer_invoices',
                            invoice_columns: ConnectpxBookingCustomerInvoicesL10n.invoice_columns,
                            csrf_token: ConnectpxBookingL10nGlobal.csrf_token,
                            date: $invoiceDateFilter.data('date'),
                            due_date: $invoiceDueDateFilter.data('date'),
                            status: $invoiceStatusFilter.val(),
                        }, {
                            filter: {}
                        }, d);
                    }
                },
                columns: invoice_columns,
                dom: "<'row'<'col-sm-12'tr>><'row mt-3'<'col-sm-12'p>>",
                language: {
                    zeroRecords: ConnectpxBookingCustomerInvoicesL10n.zeroRecords,
                    processing: ConnectpxBookingCustomerInvoicesL10n.processing
                }
            });

            $invoices_table.on('click', 'button', function () {
                if ($(this).closest('tr').hasClass('child')) {
                    row = invoices_datatable.row($(this).closest('tr').prev().find('td:first-child'));
                } else {
                    row = invoices_datatable.row($(this).closest('td'));
                }
            });

            $invoices_table.on('click', '[data-action=show-invoice]', function () {
                ConnectpxBookingInvoiceViewDialog.showDialog( row.data().id );
            });
        }

        initInvoices($container);

        $container
            .on('click', '[data-type="open-modal"]', function () {
                $($(this).attr('data-target')).connectpx_bookingModal('show');
            });

    }

    $(document).ready(function(){
        window.connectpx_bookingCustomerInvoices();
    })
})(jQuery);