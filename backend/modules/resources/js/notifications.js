jQuery(function ($) {

    const $tab_container = $('.connectpx_booking-js-notifications-wrap'),
          $footer        = $('.connectpx_booking-js-notifications-footer');

    $('.connectpx_booking-js-notifications-tabs a').off().on('click', function (e) {
        $footer.hide();
        $tab_container.html('<div class=\'connectpx_booking-loading\'></div>');
        let tab = $(this).data('tab');
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            xhrFields: {withCredentials: true},
            data: {
                action: 'connectpx_booking_email_notifications_load_tab',
                csrf_token: ConnectpxBookingL10nGlobal.csrf_token,
                tab: tab
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    $tab_container.html(response.data.html);
                    switch (tab) {
                         case 'logs':
                            $(document.body).trigger('connectpx_booking.init_email_logs',);
                            break;
                        default:
                            ConnectpxBookingNotificationsList();
                            ConnectpxBookingNotificationDialog();
                            break;
                    }
                }
            }
        });
    });
    $('.connectpx_booking-js-notifications-tabs a[data-tab=' + ConnectpxBookingL10n.tab + ']').trigger('click');
});