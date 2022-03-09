jQuery(function ($) {
    'use strict';
    window.ConnectpxBookingNotificationsList = function () {
        let $notificationList       = $('#connectpx_booking-js-notification-list'),
            $btnCheckAll            = $('#connectpx_booking-check-all', $notificationList),
            $modalTestEmail         = $('#connectpx_booking-test-email-notifications-modal'),
            $btnTestEmail           = $('#connectpx_booking-js-test-email-notifications'),
            $testNotificationsList  = $('#connectpx_booking-js-test-notifications-list', $modalTestEmail),
            $btnDeleteNotifications = $('#connectpx_booking-js-delete-notifications'),
            $filter                 = $('#connectpx_booking-filter'),
            columns                 = [],
            order                   = []
        ;

        /**
         * Init Columns.
         */
        $.each(ConnectpxBookingL10n.datatables[ConnectpxBookingL10n.gateway + '_notifications'].settings.columns, function (column, show) {
            if (show) {
                switch (column) {
                    case 'type':
                        columns.push({
                            data: 'order',
                            render: function (data, type, row, meta) {
                                return '<span class="hidden">' + data + '</span><i class="fa-fw ' + row.icon + '" title="' + row.title + '"></i>';
                            }
                        });
                        break;
                    case 'active':
                        columns.push({
                            data: column,
                            render: function (data, type, row, meta) {
                                return '<span class="badge ' + (row.active == 1 ? 'badge-success' : 'badge-info') + '">' + ConnectpxBookingL10n.state[data] + '</span>' + ' (<a href="#" data-action="toggle-active">' + ConnectpxBookingL10n.action[data] + '</a>)';
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
            className: 'text-right',
            orderable: false,
            responsivePriority: 1,
            render: function (data, type, row, meta) {
                return ' <button type="button" class="btn btn-default ladda-button" data-action="edit" data-spinner-size="40" data-style="zoom-in" data-spinner-color="#666666"><i class="far fa-fw fa-edit mr-lg-1"></i><span class="ladda-label"><span class="d-none d-lg-inline">' + ConnectpxBookingL10n.edit + '…</span></span></button>';
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

        columns[0].responsivePriority = 0;

        $.each(ConnectpxBookingL10n.datatables[ConnectpxBookingL10n.gateway + '_notifications'].settings.order, function (_, value) {
            const index = columns.findIndex(function (c) {
                return c.data === value.column;
            });
            if (index !== -1) {
                order.push([index, value.order]);
            }
        });

        /**
         * Notification list
         */
        var dt = $notificationList.DataTable({
            paging: false,
            info: false,
            processing: true,
            responsive: true,
            serverSide: false,
            ajax: {
                url: ajaxurl,
                data: {
                    action: 'connectpx_booking_get_notifications', 
                    csrf_token: ConnectpxBookingL10nGlobal.csrf_token, 
                    gateway: ConnectpxBookingL10n.gateway
                }
            },
            order: order,
            columns: columns,
            dom: "<'row'<'col-sm-12'tr>><'row float-left mt-3'<'col-sm-12'p>>",
            language: {
                zeroRecords: ConnectpxBookingL10n.noResults,
                processing: ConnectpxBookingL10n.processing
            }
        }).on('click', '[data-action=toggle-active]', function (e) {
            var row  = dt.row($(this).closest('td')),
                data = row.data();
            e.preventDefault();
            data.active = data.active === '1' ? '0' : '1';
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'connectpx_booking_set_notification_state',
                    csrf_token: ConnectpxBookingL10nGlobal.csrf_token,
                    id: data.id,
                    active: data.active
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        row.data(data).draw();
                        connectpx_bookingAlert({success: [ConnectpxBookingL10n.settingsSaved]});
                    }
                }
            });
        });

        /**
         * On filters change.
         */
        $filter
        .on('keyup', function () {
            dt.search(this.value).draw();
        })
        .on('keydown', function (e) {
            if (e.keyCode == 13) {
                e.preventDefault();
                return false;
            }
        })
        ;

        /**
         * Select all notifications.
         */
        $btnCheckAll
        .on('change', function () {
            $('tbody input:checkbox', $notificationList).prop('checked', this.checked);
        });

        $notificationList
        .on('change', 'tbody input:checkbox', function () {
            $btnCheckAll.prop('checked', $notificationList.find('tbody input:not(:checked)').length === 0);
        });

        /**
         * Delete notifications.
         */
        $btnDeleteNotifications.on('click', function () {
            if (confirm(ConnectpxBookingL10n.areYouSure)) {
                let ladda       = Ladda.create(this),
                    data        = [],
                    $checkboxes = $('input:checked', $notificationList);
                ladda.start();

                $checkboxes.each(function () {
                    data.push(this.value);
                });

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'connectpx_booking_delete_notifications',
                        csrf_token: ConnectpxBookingL10nGlobal.csrf_token,
                        notifications: data
                    },
                    dataType: 'json',
                    success: function (response) {
                        ladda.stop();
                        if (response.success) {
                            dt.rows($checkboxes.closest('td')).remove().draw();
                        }
                    }
                });
            }
        });

        $('[href="#connectpx_booking-js-auto"]').click(
            function () {
                if (this.classList.contains("toggle")) {
                    $(this).removeClass("border rounded mb-3 toggle");
                    $(this).addClass("border-light rounded-top bg-light");
                } else {
                    $(this).removeClass("border-light rounded-top bg-light")
                    $(this).addClass("border rounded mb-3 toggle");
                }
            });

        $btnTestEmail
        .on('click', function () {
            $modalTestEmail.connectpx_bookingModal()
        });

        let $check = $('<div/>', {class: 'dropdown-item my-0 pl-3'}).append(
            $('<div>', {class: 'custom-control custom-checkbox'}).append(
                $('<input>', {class: 'custom-control-input', type: 'checkbox'}),
                $('<label>', {class: 'custom-control-label text-wrap w-100'})
            ));
        $modalTestEmail
        .on('change', '#connectpx_booking-check-all-entities', function () {
            $(':checkbox', $testNotificationsList).prop('checked', this.checked);
            $(':checkbox:first-child', $testNotificationsList).trigger('change');
        })
        .on('click', '[for=connectpx_booking-check-all-entities]', function (e) {
            e.stopPropagation();
        })
        .on('click', '.btn-success', function () {
            var ladda = Ladda.create(this),
                data  = $(this).closest('form').serializeArray();
            ladda.start();
            $(':checked', $testNotificationsList).each(function () {
                data.push({name: 'notification_ids[]', value: $(this).data('notification-id')});
            });
            data.push({name: 'action', value: 'connectpx_booking_test_email_notifications'});
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function (response) {
                    ladda.stop();
                    if (response.success) {
                        connectpx_bookingAlert({success: [ConnectpxBookingL10n.sentSuccessfully]});
                        $modalTestEmail.connectpx_bookingModal('hide');
                    }
                }
            });
        })
        .on('shown.bs.modal', function () {
            let $send  = $(this).find('.btn-success'),
                active = 0;
            $send.prop('disabled', true);
            $testNotificationsList.html('');
            (dt.rows().data()).each(function (notification) {
                let $cloneCheck = $check.clone();

                $('label', $cloneCheck).html(notification.name).attr('for', 'connectpx_booking-n-' + notification.id)
                .on('click', function (e) {
                    e.stopPropagation();
                })
                ;
                $(':checkbox', $cloneCheck)
                .prop('checked', notification.active == '1')
                .attr('id', 'connectpx_booking-n-' + notification.id)
                .data('notification-id', notification.id)
                ;

                $testNotificationsList.append($cloneCheck);

                if (notification.active == '1') {
                    active++;
                }
            });
            $('.connectpx_booking-js-count', $modalTestEmail).html(active);
            $send.prop('disabled', false);
        });

        $testNotificationsList
        .on('change', ':checkbox', function () {
            $('.connectpx_booking-js-count', $modalTestEmail).html($(':checked', $testNotificationsList).length);
        });
    };
});