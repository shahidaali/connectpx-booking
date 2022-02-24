jQuery(function ($) {

    let $calendar        = $('.connectpx_booking-js-calendar'),
        $servicesFilter  = $('#connectpx_booking-js-services-filter'),
        $locationsFilter = $('#connectpx_booking-js-locations-filter'),
        serviceIds       = getCookie('connectpx_booking_cal_service_ids'),
        tabId            = getCookie('connectpx_booking_cal_tab_id'),
        lastView         = getCookie('connectpx_booking_cal_view'),
        headerToolbar    = {
            start: 'prev,next today',
            center: 'title',
            end: 'dayGridMonth,timeGridWeek,timeGridDay,resourceTimeGridDay,listWeek'
        },
        calendarTimer    = null;

    /**
     * Init services filter.
     */
    $servicesFilter.connectpx_bookingDropdown({
        onChange: function (values, selected, all) {
            serviceIds = this.connectpx_bookingDropdown('getSelected');
            setCookie('connectpx_booking_cal_service_ids', serviceIds);
            calendar.ec.refetchEvents();
        }
    });
    if (serviceIds === null) {
        $servicesFilter.connectpx_bookingDropdown('selectAll');
    } else if (serviceIds !== '') {
        $servicesFilter.connectpx_bookingDropdown('setSelected', serviceIds.split(','));
    } else {
        $servicesFilter.connectpx_bookingDropdown('toggle');
    }
    // Populate serviceIds.
    serviceIds = $servicesFilter.connectpx_bookingDropdown('getSelected');

    /**
     * Init calendar refresh buttons.
     */
    function refreshConnectpxBookingCalendar() {
        let $refresh = $('input[name="connectpx_booking_calendar_refresh_rate"]:checked');
        clearTimeout(calendarTimer);
        if ($refresh.val() > 0) {
            calendarTimer = setInterval(function () {
                calendar.ec.refetchEvents();
            }, $refresh.val() * 1000);
        }
    }

    function encodeHTML(s) {
        return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    $('#connectpx_booking-calendar-refresh').on('click', function () {
        calendar.ec.refetchEvents();
    });

    $('input[name="connectpx_booking_calendar_refresh_rate"]').change(function () {
        $.post(
            ajaxurl,
            {action: 'connectpx_booking_update_calendar_refresh_rate', csrf_token: ConnectpxBookingL10nGlobal.csrf_token, rate: this.value},
            function (response) {},
            'json'
        );
        if (this.value > 0) {
            $(this).closest('.btn-group').find('button').addClass('btn-success').removeClass('btn-default');
        } else {
            $(this).closest('.btn-group').find('button').addClass('btn-default').removeClass('btn-success');
        }
        refreshConnectpxBookingCalendar();
    });

    refreshConnectpxBookingCalendar();

    // View buttons
    headerToolbar.end = 'dayGridMonth,timeGridWeek,resourceTimeGridDay,listWeek';
    if (headerToolbar.end.indexOf(lastView) === -1) {
        lastView = 'resourceTimeGridDay';
    }

    /**
     * Init Calendar.
     */
    let calendar = new ConnectpxBookingCalendar($calendar, {
        calendar: {
            // General Display.
            headerToolbar: headerToolbar,
            // Views.
            view: lastView,
            views: {
                resourceTimeGridDay: {
                    filterResourcesWithEvents: ConnectpxBookingL10n.filterResourcesWithEvents,
                    titleFormat: {year: 'numeric', month: 'short', day: 'numeric', weekday: 'short'}
                }
            }
        },
        getServiceIds: function () {
            return serviceIds;
        },
        refresh: refreshConnectpxBookingCalendar,
        viewChanged: function (view) {
            setCookie('connectpx_booking_cal_view', view.type);
            calendar.ec.setOption('height', heightEC(view.type));
        },
        l10n: ConnectpxBookingL10n
    });

    function heightEC(view_type) {
        let calendar_tools_height = 81,
            calendar_top = $calendar.offset().top + calendar_tools_height,
            calendar_height = $(window).height() - calendar_top,
            day_head_height = 31,
            weeks_rows = 5,
            day_height = calendar_height / weeks_rows,
            slot_height = 20.4,
            day_slots_count = Math.floor((day_height - day_head_height) / slot_height);
        if (day_slots_count < 3) {
            day_slots_count = 3;
        }
        let height = ((day_slots_count * slot_height + day_head_height) * weeks_rows);
        if (view_type != 'dayGridMonth') {
            if ($('.ec-content', $calendar).height() > height) {
                height = Math.max(height, 300);
            } else {
                height = 'auto';
            }
        }

        return height === 'auto' ? 'auto' : (calendar_tools_height + height) + 'px';
    }

    $(window).on('resize', function () {
        calendar.ec.setOption('height', heightEC(calendar.ec.getOption('view')));
    });

    /**
     * Set cookie.
     *
     * @param key
     * @param value
     */
    function setCookie(key, value) {
        var expires = new Date();
        expires.setTime(expires.getTime() + 86400000); // 60 × 60 × 24 × 1000
        document.cookie = key + '=' + value + ';expires=' + expires.toUTCString();
    }

    /**
     * Get cookie.
     *
     * @param key
     * @return {*}
     */
    function getCookie(key) {
        var keyValue = document.cookie.match('(^|;) ?' + key + '=([^;]*)(;|$)');
        return keyValue ? keyValue[2] : null;
    }
});