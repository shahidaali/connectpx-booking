jQuery(function ($) {
    'use strict';

    /**
     * Loading overlay plugin
     * @param busy
     */
    $.fn.connectpx_bookingLoading = function (busy) {
        var $t = $(this);
        if ($t.length <= 0) return;
        var key = $t.data('connectpx_booking-loading-key');
        if (key === undefined) {
            key = Math.random().toString(36).substr(2, 9);
            $t.data('connectpx_booking-loading-key', key);
        }
        var $overlay = $('#connectpx_booking-js-loading-overlay-' + key);
        if (busy) {
            var zIndex   = $t.css('z-index') === 'auto' ? 2 : parseFloat($t.css('z-index')) + 1,
                $spinner = $('#connectpx_booking-js-loading-spin-' + key);
            if ($overlay.length === 0) {
                $spinner = $('<div/>')
                    .css({position: 'absolute'})
                    .attr('id', 'connectpx_booking-js-loading-spin-' + key)
                    .html('<i class="fas fa-spin fa-spinner fa-4x"></i>');
                $overlay = $('<div/>')
                    .css({display: 'none', position: 'absolute', background: '#eee'})
                    .attr('id', 'connectpx_booking-js-loading-overlay-' + key)
                    .append($spinner);

                $('body').append($overlay);
            }

            $overlay.css({
                opacity: 0.5,
                zIndex : zIndex,
                top    : $t.offset().top,
                left   : $t.offset().left,
                width  : $t.outerWidth(),
                height : $t.outerHeight()
            }).fadeIn();

            var topOverlay = (($t.height() / 2) - 32);
            if (topOverlay < 0) topOverlay = 0;
            $spinner.css({
                top : topOverlay,
                left: (($t.width() / 2) - 32)
            });

        } else {
            $overlay.fadeOut();
        }
    };

    var $container = $('.connectpx_booking-js-dashboard-appointments'),
        $dateFilter = $('select#connectpx_booking-filter-date', $container),
        totals = {
            $approved: $('.connectpx_booking-js-approved', $container),
            $pending: $('.connectpx_booking-js-pending', $container),
            $total: $('.connectpx_booking-js-total', $container),
            $revenue: $('.connectpx_booking-js-revenue', $container),
        },
        href = {
            $approved: $('.connectpx_booking-js-href-approved', $container),
            $pending: $('.connectpx_booking-js-href-pending', $container),
            $total: $('.connectpx_booking-js-href-total', $container),
            $revenue: $('.connectpx_booking-js-href-revenue', $container),
        },
        revenue = {
            label: ConnectpxBookingAppointmentsWidgetL10n.revenue,
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.5)',
            fill: true,
            data: [],
            yAxisID: 'yl',
        },
        total = {
            label: ConnectpxBookingAppointmentsWidgetL10n.appointments,
            borderColor: 'rgb(201, 203, 207)',
            backgroundColor: 'rgba(201, 203, 207, 0.5)',
            fill: true,
            data: [],
            yAxisID: 'yr'
        };

    const chart = new Chart(document.getElementById('canvas').getContext('2d'), {
        type: 'line',
        data: {
            labels: [],
            datasets: [revenue, total]
        },
        options: {
            responsive: true,
            hoverMode: 'index',
            stacked: false,
            title: {
                display: false,
            },
            animation: false,
            elements: {
                line: {
                    tension: 0.01
                }
            },
            scales: {
                yl: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: ConnectpxBookingAppointmentsWidgetL10n.revenue + ' (' + ConnectpxBookingAppointmentsWidgetL10n.currency + ')'
                    },
                },
                yr: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        text: ConnectpxBookingAppointmentsWidgetL10n.appointments,
                        display: true,
                    }
                },
            },
            plugins: {
                legend: {
                    position: 'bottom',
                },
            }
        }
    });

    $(document.body).on('connectpx_booking.dateRange.changed', {},
        function (event, data) {
            $container.parent().connectpx_bookingLoading(true);
            $.ajax({
                url     : ajaxurl,
                type    : 'POST',
                data    : {
                    action    : 'connectpx_booking_get_appointments_data_for_dashboard',
                    csrf_token: ConnectpxBookingAppointmentsWidgetL10n.csrfToken,
                    range     : data
                },
                dataType: 'json',
                success : function (response) {
                    $container.parent().connectpx_bookingLoading(false);
                    revenue.data = [];
                    total.data = [];
                    $.each(response.data.days,function (date, item) {
                        revenue.data.push(item.revenue);
                        total.data.push(item.total);
                    });
                    totals.$revenue.html(response.data.totals.revenue);
                    totals.$approved.html(response.data.totals.approved);
                    totals.$pending.html(response.data.totals.pending);
                    totals.$total.html(response.data.totals.total);

                    href.$revenue.attr('href', response.data.filters.revenue);
                    href.$approved.attr('href',response.data.filters.approved);
                    href.$pending.attr('href', response.data.filters.pending);
                    href.$total.attr('href',   response.data.filters.total);

                    chart.data.labels = response.data.labels;
                    chart.update();
                }
            });
        }
    );
    $dateFilter.on('change', function () {
        $(document.body).trigger('connectpx_booking.dateRange.changed', [$dateFilter.val()]);
    }).trigger('change');
});