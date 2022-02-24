(function ($) {

    let Calendar = function($container, options) {
        let obj  = this;
        jQuery.extend(obj.options, options);

        // Special locale for moment
        moment.locale('connectpx_booking', {
            months: obj.options.l10n.datePicker.monthNames,
            monthsShort: obj.options.l10n.datePicker.monthNamesShort,
            weekdays: obj.options.l10n.datePicker.dayNames,
            weekdaysShort: obj.options.l10n.datePicker.dayNamesShort,
            meridiem : function (hours, minutes, isLower) {
                return hours < 12
                    ? obj.options.l10n.datePicker.meridiem[isLower ? 'am' : 'AM']
                    : obj.options.l10n.datePicker.meridiem[isLower ? 'pm' : 'PM'];
            },
        });

        // Settings for Event Calendar
        let settings = {
            view: 'timeGridWeek',
            views: {
                dayGridMonth: {
                    dayHeaderFormat: function (date) {
                        return moment(date).locale('connectpx_booking').format('ddd');
                    },
                    displayEventEnd: true,
                    dayMaxEvents: obj.options.l10n.monthDayMaxEvents === '1'
                },
                timeGridDay: {
                    dayHeaderFormat: function (date) {
                        return moment(date).locale('connectpx_booking').format('dddd');
                    },
                    pointer: true
                },
                timeGridWeek: {pointer: true},
                resourceTimeGridDay: {pointer: true}
            },
            hiddenDays: obj.options.l10n.hiddenDays,
            slotDuration:  obj.options.l10n.slotDuration,
            slotMinTime: obj.options.l10n.slotMinTime,
            slotMaxTime: obj.options.l10n.slotMaxTime,
            scrollTime: obj.options.l10n.scrollTime,
            moreLinkContent: function (arg) {
                return obj.options.l10n.more.replace('%d', arg.num)
            },
            flexibleSlotTimeLimits: true,
            eventStartEditable: false,

            slotLabelFormat: function (date) {
                return moment(date).locale('connectpx_booking').format(obj.options.l10n.mjsTimeFormat);
            },
            eventTimeFormat: function (date) {
                return moment(date).locale('connectpx_booking').format(obj.options.l10n.mjsTimeFormat);
            },
            dayHeaderFormat: function (date) {
                return moment(date).locale('connectpx_booking').format('ddd, D');
            },
            listDayFormat: function (date) {
                return moment(date).locale('connectpx_booking').format('dddd');
            },
            firstDay: obj.options.l10n.datePicker.firstDay,
            locale: obj.options.l10n.locale.replace('_', '-'),
            buttonText: {
                today: obj.options.l10n.today,
                dayGridMonth: obj.options.l10n.month,
                timeGridWeek: obj.options.l10n.week,
                timeGridDay: obj.options.l10n.day,
                resourceTimeGridDay: obj.options.l10n.day,
                listWeek: obj.options.l10n.list
            },
            noEventsContent: obj.options.l10n.noEvents,
            eventSources: [{
                url: ajaxurl,
                method: 'POST',
                extraParams: function () {
                    return {
                        action: 'connectpx_booking_get_calendar_appointments',
                        csrf_token: ConnectpxBookingL10nGlobal.csrf_token,
                        service_ids: obj.options.getServiceIds()
                    };
                }
            }],
            eventBackgroundColor: '#ccc',
            eventMouseEnter: function(arg) {
                if (arg.event.display === 'auto' && arg.view.type !== 'listWeek') {
                    fixPopoverPosition($(arg.el).find('.connectpx_booking-ec-popover'));
                }
            },
            eventContent: function (arg) {
                if (arg.event.display === 'background') {
                    return '';
                }
                let event = arg.event;
                let props = event.extendedProps;
                let nodes = [];
                let $time = $('<div class="ec-event-time"/>');
                let $title = $('<div class="ec-event-title"/>');

                $time.append(props.header_text || arg.timeText);
                nodes.push($time.get(0));
                if (arg.view.type === 'listWeek') {
                    let dot = $('<div class="ec-event-dot"></div>').css('border-color', event.backgroundColor);
                    nodes.push($('<div/>').append(dot).get(0));
                }
                $title.append(props.desc || '');
                nodes.push($title.get(0));

                switch (props.overall_status) {
                    case 'pending':
                        $time.addClass('text-muted');
                        $title.addClass('text-muted');
                        break;
                    case 'rejected':
                    case 'cancelled':
                        $time.addClass('text-muted').wrapInner('<s>');
                        $title.addClass('text-muted');
                        break;
                }

                const $buttons = $('<div class="mt-2 d-flex"/>');
                $buttons.append($('<button class="btn btn-success btn-sm mr-1">').append('<i class="far fa-fw fa-edit">'));
                $buttons.append(
                    $('<a class="btn btn-danger btn-sm text-white">').append('<i class="far fa-fw fa-trash-alt">')
                        .attr('title', obj.options.l10n.delete)
                        .on('click', function (e) {
                            e.stopPropagation();
                            // Localize contains only string values
                            new ConnectpxBookingConfirmDeletingAppointment({
                                    action: 'connectpx_booking_delete_appointment',
                                    appointment_id: arg.event.id,
                                    csrf_token: ConnectpxBookingL10nGlobal.csrf_token
                                },
                                function (response) {calendar.removeEventById(arg.event.id);}
                            );
                        })
                );

                if (arg.view.type !== 'listWeek') {
                    $buttons.addClass('border-top pt-2 justify-content-end');
                    let $popover = $('<div class="connectpx_booking-popover bs-popover-top connectpx_booking-ec-popover">')
                    let $arrow = $('<div class="arrow" style="left:8px;">');
                    let $body = $('<div class="popover-body">');
                    $body.append(props.tooltip).append($buttons).css({minWidth: '200px'});
                    $popover.append($arrow).append($body);
                    nodes.push($popover.get(0));
                    $time.on('touchstart', function () {
                        fixPopoverPosition($popover);
                    });
                    $title.on('touchstart', function () {
                        fixPopoverPosition($popover);
                    });
                } else {
                    $title.append($buttons);
                }

                return {domNodes: nodes};
            },
            eventClick: function (arg) {
                if (arg.event.display === 'background') {
                    return;
                }
                arg.jsEvent.stopPropagation();
                var visible_staff_id = 0;

                ConnectpxBookingAppointmentDialog.showDialog(
                    arg.event.id,
                    null,
                    null,
                    function (event) {
                        if (event == 'refresh') {
                            calendar.refetchEvents();
                        } else {
                            if (event.start === null) {
                                // Task
                                calendar.removeEventById(event.id);
                            } else {
                                if (visible_staff_id == event.resourceId || visible_staff_id == 0) {
                                    // Update event in calendar.
                                    calendar.updateEvent(event);
                                } else {
                                    // Switch to the event owner tab.
                                    jQuery('li > a[data-staff_id=' + event.resourceId + ']').click();
                                }
                            }
                        }

                    }
                );
            },
            dateClick: function (arg) {
                let staff_id, visible_staff_id;
                if (arg.view.type === 'resourceTimeGridDay') {
                    staff_id = arg.resource.id;
                    visible_staff_id = 0;
                } else {
                    staff_id = visible_staff_id = obj.options.getCurrentStaffId();
                }
                addAppointmentDialog(arg.date, staff_id, visible_staff_id);
            },
            noEventsClick: function (arg) {
                let staffId = obj.options.getCurrentStaffId();
                addAppointmentDialog(arg.view.activeStart, staffId, staffId);
            },
            loading: function (isLoading) {
                if (isLoading) {
                    ConnectpxBookingL10nAppDialog.refreshed = true;
                    if (dateSetFromDatePicker) {
                        dateSetFromDatePicker = false;
                    } else {
                        calendar.setOption('highlightedDates', []);
                    }
                    $('.connectpx_booking-ec-loading').show();
                } else {
                    $('.connectpx_booking-ec-loading').hide();
                    obj.options.refresh();
                }
            },
            viewDidMount: function (view) {
                calendar.setOption('highlightedDates', []);
                obj.options.viewChanged(view);
            },
            theme: function (theme) {
                theme.button = 'btn btn-default';
                theme.buttonGroup = 'btn-group';
                theme.active = 'active';
                return theme;
            }
        };

        function fixPopoverPosition($popover) {
            let $event = $popover.closest('.ec-event'),
                offset = $event.offset(),
                top = Math.max($popover.outerHeight() + 40, Math.max($event.closest('.ec-body').offset().top, offset.top) - $(document).scrollTop());

            $popover.css('top', (top - $popover.outerHeight() - 4) + 'px')
            $popover.css('left', (offset.left + 2) + 'px')
        }

        function addAppointmentDialog(date, staffId, visibleStaffId) {
            ConnectpxBookingAppointmentDialog.showDialog(
                null,
                parseInt(staffId),
                moment(date),
                function (event) {
                    if (event == 'refresh') {
                        calendar.refetchEvents();
                    } else {
                        if (visibleStaffId == event.resourceId || visibleStaffId == 0) {
                            if (event.start !== null) {
                                if (event.id) {
                                    // Create event in calendar.
                                    calendar.addEvent(event);
                                } else {
                                    calendar.refetchEvents();
                                }
                            }
                        } else {
                            // Switch to the event owner tab.
                            jQuery('li[data-staff_id=' + event.resourceId + ']').click();
                        }
                    }

                    if (locationChanged) {
                        calendar.refetchEvents();
                        locationChanged = false;
                    }
                }
            );
        }

        let dateSetFromDatePicker = false;

        let calendar = new window.EventCalendar($container.get(0), $.extend(true, {}, settings, obj.options.calendar));

        $('.ec-toolbar .ec-title', $container).on('click', function () {
            let picker = $(this).data('daterangepicker');
            picker.setStartDate(calendar.getOption('date'));
            picker.setEndDate(calendar.getOption('date'));
        });
        // Init date picker for fast navigation in Event Calendar.
        $('.ec-toolbar .ec-title', $container).daterangepicker({
            parentEl        : '.connectpx_booking-js-calendar',
            singleDatePicker: true,
            showDropdowns   : true,
            autoUpdateInput : false,
            locale          : obj.options.l10n.datePicker
        }).on('apply.daterangepicker', function (ev, picker) {
            dateSetFromDatePicker = true;
            if (calendar.view.type !== 'timeGridDay' && calendar.view.type !== 'resourceTimeGridDay') {
                calendar.setOption('highlightedDates', [picker.startDate.toDate()]);
            }
            calendar.setOption('date', picker.startDate.toDate());
        });

        // Export calendar
        this.ec = calendar;
        if (obj.options.l10n.monthDayMaxEvents == '1') {
            let theme = this.ec.getOption('theme');
            theme.month += ' ec-classic';
            this.ec.setOption('theme', theme);
        }
    };

    Calendar.prototype.options = {
        calendar: {},
        getServiceIds: function () { return ['all']; },
        refresh: function () {},
        viewChanged: function () {},
        l10n: {}
    };

    window.ConnectpxBookingCalendar = Calendar;
})(jQuery);