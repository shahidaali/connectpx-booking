jQuery(function ($) {
    $(document.body).on('connectpx_booking.init_email_logs', {},
        function (event) {
            let $table          = $('#connectpx_booking-email-logs'),
                $modal          = $('#connectpx_booking-email-logs-dialog'),
                $to             = $('#connectpx_booking-email-to', $modal),
                $subject        = $('#connectpx_booking-email-subject', $modal),
                $body           = $('#connectpx_booking-email-body', $modal),
                $headers        = $('#connectpx_booking-email-headers', $modal),
                $attachments    = $('#connectpx_booking-email-attachments', $modal),
                $date           = $('#connectpx_booking-email-date', $modal),
                $checkAllButton = $('#connectpx_booking-check-all'),
                $deleteButton   = $('#connectpx_booking-email-log-delete'),
                dt
            ;
            $checkAllButton.on('change', function () {
                $table.find('tbody input:checkbox').prop('checked', this.checked);
            });
            $table.on('change', 'tbody input:checkbox', function () {
                $checkAllButton.prop('checked', $table.find('tbody input:not(:checked)').length == 0);
            })
            $deleteButton.on('click', function () {
                if (confirm(ConnectpxBookingL10n.areYouSure)) {

                    let data = [];
                    $table.find('tbody input:checked').each(function () {
                        data.push(this.value);
                    });
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'connectpx_booking_delete_email_logs',
                            csrf_token: ConnectpxBookingL10nGlobal.csrf_token,
                            data: data
                        },
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {
                                dt.ajax.reload();
                            } else {
                                alert(response.data.message);
                            }
                        }
                    });
                }
            });
            // Date range picker options.
            let pickers = {
                dateFormat: 'YYYY-MM-DD',
                creationDate: {
                    startDate: moment().subtract(30, 'days'),
                    endDate: moment(),
                },
            }
            var picker_ranges = {};
            picker_ranges[ConnectpxBookingEmailLogsL10n.dateRange.yesterday] = [moment().subtract(1, 'days'), moment().subtract(1, 'days')];
            picker_ranges[ConnectpxBookingEmailLogsL10n.dateRange.today] = [moment(), moment()];
            picker_ranges[ConnectpxBookingEmailLogsL10n.dateRange.last_7] = [moment().subtract(7, 'days'), moment()];
            picker_ranges[ConnectpxBookingEmailLogsL10n.dateRange.last_30] = [moment().subtract(30, 'days'), moment()];
            picker_ranges[ConnectpxBookingEmailLogsL10n.dateRange.thisMonth] = [moment().startOf('month'), moment().endOf('month')];
            picker_ranges[ConnectpxBookingEmailLogsL10n.dateRange.lastMonth] = [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')];
            // Init date range picker.
            let $date_range = $('#connectpx_booking-email-logs-date-range');
            $date_range.daterangepicker(
                {
                    parentEl: $date_range.parent(),
                    startDate: pickers.creationDate.startDate,
                    endDate: pickers.creationDate.endDate,
                    ranges: picker_ranges,
                    showDropdowns: true,
                    linkedCalendars: false,
                    autoUpdateInput: false,
                    locale: $.extend({}, ConnectpxBookingEmailLogsL10n.dateRange, ConnectpxBookingEmailLogsL10n.datePicker)
                },
                function (start, end) {
                    let format = 'YYYY-MM-DD';
                    $date_range
                    .data('date', start.format(format) + ' - ' + end.format(format))
                    .find('span')
                    .html(start.format(ConnectpxBookingEmailLogsL10n.dateRange.format) + ' - ' + end.format(ConnectpxBookingEmailLogsL10n.dateRange.format));
                }
            );
            $date_range.on('apply.daterangepicker', function () { dt.ajax.reload(); });
            // Init datatable columns.
            let columns = [];

            $.each(ConnectpxBookingEmailLogsL10n.datatables.email_logs.settings.columns, function (column, show) {
                if (show) {
                    columns.push({data: column, render: $.fn.dataTable.render.text()});
                }
            });
            columns.push({
                className: 'text-right',
                orderable: false,
                responsivePriority: 1,
                render: function (data, type, row, meta) {
                    return ' <button type="button" class="btn btn-default ladda-button" data-action="edit" data-spinner-size="40" data-style="zoom-in" data-spinner-color="#666666"><i class="far fa-fw fa-file-alt mr-lg-1"></i><span class="ladda-label"><span class="d-none d-lg-inline">' + ConnectpxBookingEmailLogsL10n.details + '…</span></span></button>';
                }
            });
            columns.push({
                orderable: false,
                responsivePriority: 1,
                render: function (data, type, row, meta) {
                    return '<div class="custom-control custom-checkbox">' +
                        '<input value="' + row.id + '" id="connectpx_booking-dt-' + row.id + '" type="checkbox" class="custom-control-input">' +
                        '<label for="connectpx_booking-dt-' + row.id + '" class="custom-control-label"></label>' +
                        '</div>';
                }
            });

            if (columns.length) {
                dt = $table.DataTable({
                    ordering: true,
                    info: false,
                    searching: false,
                    lengthChange: false,
                    processing: true,
                    responsive: true,
                    pageLength: 25,
                    pagingType: 'numbers',
                    serverSide: true,
                    ajax: {
                        url: ajaxurl,
                        type: 'POST',
                        data: function (d) {
                            return $.extend({}, d, {
                                action: 'connectpx_booking_get_email_logs',
                                csrf_token: ConnectpxBookingL10nGlobal.csrf_token,
                                range: $date_range.data('date')
                            });
                        },
                    },
                    columns: columns,
                    dom: "<'row'<'col-sm-12'tr>><'row float-left mt-3'<'col-sm-12'p>>",
                    language: {
                        zeroRecords: ConnectpxBookingEmailLogsL10n.zeroRecords,
                        processing: ConnectpxBookingEmailLogsL10n.processing
                    }
                });

                $table.on('click', 'button', function (e) {
                    let rowData = getDTRowData(this);
                    $to.val(rowData.to);
                    $body.val(rowData.body);
                    $subject.val(rowData.subject);
                    $headers.val(rowData.headers.join("\n"));
                    $attachments.val(rowData.attach.join("\n"));
                    $date.val(rowData.created_at);
                    $modal.connectpx_bookingModal('show');
                });

                function getDTRowData(element) {
                    let $el = $(element).closest('td');
                    if ($el.hasClass('child')) {
                        $el = $el.closest('tr').prev();
                    }
                    return dt.row($el).data();
                }
            }
        }
    );
});
