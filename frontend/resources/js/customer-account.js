(function ($) {
    'use strict';
    window.connectpx_bookingCustomerAccount = function () {
        let $container = $('.connectpx_booking-customer-account');
        console.log($container)
        if (!$container.length) {
            return;
        }

        // Profile section
        function initProfile($container) {
            var $profile_content = $('.connectpx_booking-js-customer-account-content-profile', $container),
                $form = $('form', $profile_content),
                $phone_field = $('.connectpx_booking-js-user-phone-input', $profile_content),
                $save_btn = $('button.connectpx_booking-js-save-profile', $profile_content);

            $save_btn.on('click', function (e) {
                e.preventDefault();
                var ladda = Ladda.create(this);
                ladda.start();
                $('.is-invalid', $profile_content).removeClass('is-invalid');
                $('.form-group .connectpx_booking-js-error').remove();
                var phone_number = $phone_field.val();
                $phone_field.val(phone_number);

                var data = $form.serializeArray();
                data.push({name: 'action', value: 'connectpx_booking_customer_save_profile'});
                data.push({name: 'csrf_token', value: ConnectpxBookingL10nGlobal.csrf_token});
                $.ajax({
                    url     : ConnectpxBookingCustomerAccountL10n.ajax_url,
                    type    : 'POST',
                    data    : data,
                    dataType: 'json',
                    success : function (response) {
                        if (response.success) {
                            connectpx_bookingAlert({success: [ConnectpxBookingCustomerAccountL10n.profile_update_success]});
                            if ($('[name="current_password"]', $profile_content).val()) {
                                window.location.reload();
                            }
                        } else {
                            $.each(response.errors, function (name, value) {
                                var $form_group = $('.form-group [id="connectpx_booking_' + name + '"]', $profile_content).closest('.form-group');
                                $form_group.find('.connectpx_booking-js-control-input').addClass('is-invalid');
                                $form_group.append('<div class="connectpx_booking-js-error text-danger">' + value + '</div>');
                            });
                            $('html, body').animate({
                                scrollTop: $profile_content.find('.is-invalid').first().offset().top - 100
                            }, 1000);
                        }
                        ladda.stop();
                    }
                });
            });
        }

        initProfile($container);

    }

    $(document).ready(function(){
        window.connectpx_bookingCustomerAccount();
    })
})(jQuery);