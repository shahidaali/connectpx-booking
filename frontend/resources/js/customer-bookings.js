(function ($) {
    'use strict';
    window.connectpx_bookingCustomerBookings = function () {
        let $container = $('.connectpx_booking-customer-bookings');
        console.log($container)
        if (!$container.length) {
            return;
        }

        // Appointments section
        function initAppointments($container) {
            var $appointments_table = $('.connectpx_booking-appointments-list', $container),
                $reschedule_dialog = $('#connectpx_booking-customer-bookings-reschedule-dialog', $container),
                $reschedule_date = $('#connectpx_booking-reschedule-date', $reschedule_dialog),
                $reschedule_time = $('#connectpx_booking-reschedule-time', $reschedule_dialog),
                $reschedule_error = $('#connectpx_booking-reschedule-error', $reschedule_dialog),
                $reschedule_save = $('#connectpx_booking-save', $reschedule_dialog),
                $cancel_dialog = $('#connectpx_booking-customer-bookings-cancel-dialog', $container),
                $cancel_button = $('#connectpx_booking-yes', $cancel_dialog),
                $cancel_reason = $('#connectpx_booking-cancel-reason', $cancel_dialog),
                $cancel_reason_error = $('#connectpx_booking-cancel-reason-error', $cancel_dialog),
                appointments_columns = [],
                $appointmentDateFilter = $('#connectpx_booking-filter-date'),
                $serviceFilter = $('#connectpx_booking-filter-service'),
                $searchQuery = $('#connectpx_booking-search-query'),
                row;
            console.log(ConnectpxBookingCustomerBookingsL10n);
            Object.keys(ConnectpxBookingCustomerBookingsL10n.appointment_columns).map(function(objectKey) {
                let column = ConnectpxBookingCustomerBookingsL10n.appointment_columns[objectKey];
                switch (column) {
                    case 'date':
                        appointments_columns.push({data: 'pickup_datetime', responsivePriority: 1});
                        break;
                    case 'service':
                        appointments_columns.push({
                            data: 'service_title', responsivePriority: 3, render: function (data, type, row, meta) {
                                return data.split('<br/>').map(function (item) {
                                    return $.fn.dataTable.render.text().display(item);
                                }).join('<br/>');
                            }
                        });
                        break;
                    case 'total_amount':
                        appointments_columns.push({
                            data: 'total_amount',
                            responsivePriority: 3,
                            render: function ( data, type, row, meta ) {
                                return '<button type="button" class="btn btn-sm btn-default" data-action="show-payment" data-payment_id="' + row.id + '">' + ConnectpxBookingCustomerBookingsL10n.payment + '</button>';

                                return data;
                            }
                        });
                        break;
                    case 'status':
                        appointments_columns.push({data: 'status', responsivePriority: 3, render: $.fn.dataTable.render.text()});
                        break;
                    case 'cancel':
                        appointments_columns.push({
                            data              : 'id',
                            render            : function (data, type, row, meta) {
                                switch (row.allow_cancel) {
                                    case 'expired' :
                                        return ConnectpxBookingCustomerBookingsL10n.expired_appointment;
                                    case 'blank' :
                                        return '';
                                    case 'allow' :
                                        return '<button class="btn btn-sm btn-default" data-type="open-modal" data-target="#connectpx_booking-customer-bookings-cancel-dialog">' + ConnectpxBookingCustomerBookingsL10n.cancel + '</button>';
                                    case 'deny':
                                        return '<button class="btn btn-sm btn-default" data-type="open-modal" data-target="#connectpx_booking-customer-bookings-cancel-dialog">' + ConnectpxBookingCustomerBookingsL10n.cancel + '</button>';
                                    // case 'deny':
                                    //     return ConnectpxBookingCustomerBookingsL10n.deny_cancel_appointment;
                                }
                            },
                            responsivePriority: 2,
                            orderable         : false
                        });
                        break;
                    case 'reschedule':
                        appointments_columns.push({
                            data              : 'id',
                            render            : function (data, type, row, meta) {
                                switch (row.allow_reschedule) {
                                    case 'expired' :
                                        return ConnectpxBookingCustomerBookingsL10n.expired_appointment;
                                    case 'blank' :
                                        return '';
                                    case 'allow' :
                                        return '<button class="btn btn-sm btn-default" data-type="open-modal" data-target="#connectpx_booking-customer-bookings-reschedule-dialog">' + ConnectpxBookingCustomerBookingsL10n.reschedule + '</button>';
                                    case 'deny':
                                        return ConnectpxBookingCustomerBookingsL10n.deny_cancel_appointment;
                                }
                            },
                            responsivePriority: 2,
                            orderable         : false
                        });
                        break;
                    default:
                        appointments_columns.push({data: column, render: $.fn.dataTable.render.text()});
                        break;
                }
            });
            // Date range filter
            let pickerRanges = {};

            pickerRanges[ConnectpxBookingCustomerBookingsL10n.dateRange.anyTime]   = [moment(), moment().add(100, 'years')];
            pickerRanges[ConnectpxBookingCustomerBookingsL10n.dateRange.yesterday] = [moment().subtract(1, 'days'), moment().subtract(1, 'days')];
            pickerRanges[ConnectpxBookingCustomerBookingsL10n.dateRange.today]     = [moment(), moment()];
            pickerRanges[ConnectpxBookingCustomerBookingsL10n.dateRange.tomorrow]  = [moment().add(1, 'days'), moment().add(1, 'days')];
            pickerRanges[ConnectpxBookingCustomerBookingsL10n.dateRange.last_7]    = [moment().subtract(7, 'days'), moment()];
            pickerRanges[ConnectpxBookingCustomerBookingsL10n.dateRange.last_30]   = [moment().subtract(30, 'days'), moment()];
            pickerRanges[ConnectpxBookingCustomerBookingsL10n.dateRange.thisMonth] = [moment().startOf('month'), moment().endOf('month')];
            pickerRanges[ConnectpxBookingCustomerBookingsL10n.dateRange.nextMonth] = [moment().add(1, 'month').startOf('month'), moment().add(1, 'month').endOf('month')];
            $appointmentDateFilter.daterangepicker(
                {
                    parentEl : $appointmentDateFilter.closest('div'),
                    startDate: moment(),
                    endDate  : moment().add(100, 'years'),
                    ranges   : pickerRanges,
                    showDropdowns  : true,
                    linkedCalendars: false,
                    autoUpdateInput: false,
                    locale: $.extend({},ConnectpxBookingCustomerBookingsL10n.dateRange, ConnectpxBookingCustomerBookingsL10n.datePicker)
                },
                function(start, end, label) {
                    switch (label) {
                        case ConnectpxBookingCustomerBookingsL10n.dateRange.anyTime:
                            $appointmentDateFilter
                            .data('date', 'any')
                            .find('span')
                            .html(ConnectpxBookingCustomerBookingsL10n.dateRange.anyTime);
                            break;
                        default:
                            $appointmentDateFilter
                            .data('date', start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'))
                            .find('span')
                            .html(start.format(ConnectpxBookingCustomerBookingsL10n.dateRange.format) + ' - ' + end.format(ConnectpxBookingCustomerBookingsL10n.dateRange.format));
                    }
                }
            ).data('date', 'any').find('span')
            .html(ConnectpxBookingCustomerBookingsL10n.dateRange.anyTime);

            $appointmentDateFilter.on('apply.daterangepicker', function () {
                appointments_datatable.ajax.reload();
            });
            $serviceFilter.on('change', function () {
                appointments_datatable.ajax.reload();
            })

            var timeout = null;
            $searchQuery.on('keyup', function () {
                var value = $(this).val();
                clearTimeout(timeout);
                timeout = setTimeout(function(){
                    if ( value.length >= 3 ) {
                        appointments_datatable.ajax.reload();
                    }
                    else if( value == '' ) {
                        appointments_datatable.ajax.reload();
                    }
                }, 500);

            })

            $('.connectpx_booking-js-select')
            .val(null)
            .select2({
                width: '100%',
                theme: 'bootstrap4',
                dropdownParent: '#connectpx_booking_tbs',
                allowClear: true,
                placeholder: '',
                language: {
                    noResults: function() { return ConnectpxBookingCustomerBookingsL10n.no_result_found; }
                },
            });

            /**
             * Init DataTables.
             */
            var appointments_datatable = $appointments_table.DataTable({
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
                    url: ConnectpxBookingCustomerBookingsL10n.ajax_url,
                    type: 'POST',
                    data: function (d) {
                        return $.extend({
                            action: 'connectpx_booking_get_customer_appointments',
                            appointment_columns: ConnectpxBookingCustomerBookingsL10n.appointment_columns,
                            csrf_token: ConnectpxBookingL10nGlobal.csrf_token,
                            date: $appointmentDateFilter.data('date'),
                            service: $serviceFilter.val(),
                            search_query: $searchQuery.val(),
                        }, {
                            filter: {}
                        }, d);
                    }
                },
                columns: appointments_columns,
                dom: "<'row'<'col-sm-12'tr>><'row mt-3'<'col-sm-12'p>>",
                language: {
                    zeroRecords: ConnectpxBookingCustomerBookingsL10n.zeroRecords,
                    processing: ConnectpxBookingCustomerBookingsL10n.processing
                }
            });

            $appointments_table.on('click', 'button', function () {
                if ($(this).closest('tr').hasClass('child')) {
                    row = appointments_datatable.row($(this).closest('tr').prev().find('td:first-child'));
                } else {
                    row = appointments_datatable.row($(this).closest('td'));
                }
            });

            // Cancel appointment dialog
            $cancel_button.on('click', function () {
                if ($cancel_reason.length && $cancel_reason.val() === '') {
                    $cancel_reason_error.show();
                } else {
                    $.ajax({
                        url: ConnectpxBookingCustomerBookingsL10n.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'connectpx_booking_customer_cancel_appointment',
                            csrf_token: ConnectpxBookingL10nGlobal.csrf_token,
                            id: row.data().id,
                            reason: $cancel_reason.val()
                        },
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {
                                $cancel_dialog.connectpx_bookingModal('hide');
                                appointments_datatable.ajax.reload();
                            } else {
                                connectpx_bookingAlert({error: [ConnectpxBookingCustomerBookingsL10n.errors.cancel]});
                            }
                        }
                    });
                }
            });

            // Reschedule appointment dialog
            $reschedule_date.daterangepicker({
                parentEl        : '#connectpx_booking-customer-bookings-reschedule-dialog',
                singleDatePicker: true,
                showDropdowns   : true,
                autoUpdateInput : true,
                minDate         : moment().add(ConnectpxBookingCustomerBookingsL10n.minDate, 'days'),
                maxDate         : moment().add(ConnectpxBookingCustomerBookingsL10n.maxDate, 'days'),
                locale          : ConnectpxBookingCustomerBookingsL10n.datePicker
            }).on('change', function () {
                $reschedule_save.prop('disabled', true);
                $reschedule_time.html('');
                $reschedule_error.hide();
                $.ajax({
                    url     : ConnectpxBookingCustomerBookingsL10n.ajax_url,
                    type    : 'POST',
                    data    : {
                        action    : 'connectpx_booking_customer_cabinet_get_day_schedule',
                        csrf_token: ConnectpxBookingL10nGlobal.csrf_token,
                        id     : row.data().id,
                        date      : moment($(this).val(), ConnectpxBookingCustomerBookingsL10n.datePicker.format).format('DD-MM-YYYY')
                    },
                    dataType: 'json',
                    success : function (response) {
                        if (response.data.length) {
                            var time_options = response.data[0].options;
                            $.each(time_options, function (index, option) {
                                var $option = $('<option/>');
                                $option.text(option.title).val(option.value);
                                if (option.disabled) {
                                    $option.attr('disabled', 'disabled');
                                }
                                $reschedule_time.append($option);
                            });
                            $reschedule_save.prop('disabled', false);
                        } else {
                            $reschedule_error.text(ConnectpxBookingCustomerBookingsL10n.noTimeslots).show();
                        }
                    }
                });
            });
            $reschedule_dialog.on('show.bs.modal', function (e) {
                let previous = $reschedule_date.data('daterangepicker').startDate.format('YYYY-MM-DD');
                $reschedule_date.data('daterangepicker').setStartDate(row.data().pickup_datetime);
                $reschedule_date.data('daterangepicker').setEndDate(row.data().pickup_datetime);
                if (previous === $reschedule_date.data('daterangepicker').startDate.format('YYYY-MM-DD')) {
                    // Even if the date hasn't changed, forcibly inform the object that it has been changed
                    $reschedule_date.trigger('change');
                }
            });
            $reschedule_save.on('click', function (e) {
                e.preventDefault();
                $.ajax({
                    url     : ConnectpxBookingCustomerBookingsL10n.ajax_url,
                    type    : 'POST',
                    data    : {
                        action    : 'connectpx_booking_customer_cabinet_save_reschedule',
                        csrf_token: ConnectpxBookingL10nGlobal.csrf_token,
                        id     : row.data().id,
                        slot      : $reschedule_time.val(),
                    },
                    dataType: 'json',
                    success : function (response) {
                        if (response.success) {
                            $reschedule_dialog.connectpx_bookingModal('hide');
                            appointments_datatable.ajax.reload();
                        } else {
                            connectpx_bookingAlert({error: [ConnectpxBookingCustomerBookingsL10n.errors.reschedule]});
                        }
                    }
                });
            });

            $appointments_table.on('click', '[data-action=show-payment]', function () {
                ConnectpxBookingAppointmentDialog.showDialog( row.data().id, null, 'payment' );
            });
        }

        initAppointments($container);

        $container
            .on('click', '[data-type="open-modal"]', function () {
                $($(this).attr('data-target')).connectpx_bookingModal('show');
            });

    }

    $(document).ready(function(){
        window.connectpx_bookingCustomerBookings();
    })
})(jQuery);