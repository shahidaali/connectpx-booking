jQuery(function ($) {
    let $dateFilter = $('#connectpx_booking-filter-date'),
        pickerRanges = [];

    /**
     * Init date range pickers.
     */

    pickerRanges[ConnectpxBookingL10n.dateRange.last_7]    = [moment().subtract(7, 'days'), moment()];
    pickerRanges[ConnectpxBookingL10n.dateRange.last_30]   = [moment().subtract(30, 'days'), moment()];
    pickerRanges[ConnectpxBookingL10n.dateRange.thisMonth] = [moment().startOf('month'), moment().endOf('month')];
    pickerRanges[ConnectpxBookingL10n.dateRange.lastMonth] = [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')];

    $dateFilter.daterangepicker({
        parentEl : $dateFilter.parent(),
        startDate: moment().subtract(7, 'days'),
        endDate  : moment(),
        ranges   : pickerRanges,
        showDropdowns  : true,
        linkedCalendars: false,
        autoUpdateInput: false,
        locale: $.extend({},ConnectpxBookingL10n.dateRange, ConnectpxBookingL10n.datePicker)
    },
    function(start, end, label) {
        switch (label) {
            default:
                var format = 'YYYY-MM-DD';
                $dateFilter
                    .data('date', start.format(format) + ' - ' + end.format(format))
                    .find('span')
                    .html(start.format(ConnectpxBookingL10n.dateRange.format) + ' - ' + end.format(ConnectpxBookingL10n.dateRange.format));
        }
    } );

    $dateFilter.on('apply.daterangepicker', function () {
        $(document.body).trigger('connectpx_booking.dateRange.changed', [$dateFilter.data('date')]);
    }).trigger('apply.daterangepicker');
});