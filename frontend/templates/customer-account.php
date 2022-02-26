<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use ConnectpxBooking\Lib\Utils\Common;
use ConnectpxBooking\Lib\Config;

/** @var ConnectpxBookingLib\Entities\Customer $customer */
?>
<div id="connectpx_booking_tbs" class="wrap connectpx_booking-customer-account">
    <div class="mt-4">
        <div class="connectpx_booking-js-customer-account-content connectpx_booking-js-customer-account-content-profile">
            <form>
                <div class="row">
                    <div class="form-group col-sm-6">
                        <label for="connectpx_booking_first_name"><?php echo esc_html( 'First Name', 'connectpx_booking' ) ?></label>
                        <input type="text" name="first_name" class="form-control connectpx_booking-js-control-input" id="connectpx_booking_first_name" value="<?php echo esc_attr( $customer->getFirstName() ) ?>"/>
                    </div>
                    <div class="form-group col-sm-6">
                        <label for="connectpx_booking_last_name"><?php echo esc_html( 'Last Name', 'connectpx_booking' ) ?></label>
                        <input type="text" name="last_name" class="form-control connectpx_booking-js-control-input" id="connectpx_booking_last_name" value="<?php echo esc_attr( $customer->getLastName() ) ?>"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="connectpx_booking_email"><?php echo esc_html( 'Email', 'connectpx_booking' ) ?></label>
                    <input type="text" name="email" class="form-control connectpx_booking-js-control-input" id="connectpx_booking_email" value="<?php echo esc_attr( $customer->getEmail() ) ?>"/>
                </div>
                <div class="form-group">
                    <label for="connectpx_booking_phone"><?php echo esc_html( 'Phone', 'connectpx_booking' ) ?></label>
                    <input type="text" name="phone" class="form-control connectpx_booking-js-user-phone-input<?php if ( get_option( 'connectpx_booking_cst_phone_default_country' ) != 'disabled' ) : ?> connectpx_booking-user-phone<?php endif ?>" id="connectpx_booking_phone" value="<?php echo esc_attr( $customer->getPhone() ) ?>"/>
                </div>
                <?php
                foreach ( $customer_address as $field_name => $field ) : ?>
                    <div class="form-group">
                        <label for="connectpx_booking_<?php echo $field_name ?>"><?php echo esc_html( $field['label'] ) ?></label>
                        <input class="form-control connectpx_booking-js-control-input" type="text" name=<?php echo $field_name ?> id="connectpx_booking_<?php echo $field_name ?>" value="<?php echo esc_attr( $field['value'] ) ?>"/>
                    </div>
                <?php endforeach ?>
                <div class="row">
                    <div class="form-group col-sm-3">
                        <label for="connectpx_booking-wp-user"><?php esc_html_e( 'WP user', 'connectpx_booking' ) ?></label>
                        <p><?php $user_data = get_userdata( $customer->getWpUserId() ); echo $user_data->display_name ?></p>
                    </div>
                    <div class="form-group col-sm-3">
                        <label for="connectpx_booking_current_password"><?php esc_html_e( 'Current password', 'connectpx_booking' ) ?></label>
                        <input type="password" name="current_password" class="form-control connectpx_booking-js-control-input" id="connectpx_booking_current_password" value=""/>
                    </div>
                    <div class="form-group col-sm-3">
                        <label for="connectpx_booking_new_password_1"><?php esc_html_e( 'New password', 'connectpx_booking' ) ?></label>
                        <input type="password" name="new_password_1" class="form-control connectpx_booking-js-control-input" id="connectpx_booking_new_password_1" value=""/>
                    </div>
                    <div class="form-group col-sm-3">
                        <label for="connectpx_booking_new_password_2"><?php esc_html_e( 'Confirm password', 'connectpx_booking' ) ?></label>
                        <input type="password" name="new_password_2" class="form-control connectpx_booking-js-control-input" id="connectpx_booking_new_password_2" value=""/>
                    </div>
                </div>
                <div>
                    <button class="btn btn-success float-right connectpx_booking-js-save-profile ladda-button" data-style="zoom-in"><?php esc_html_e( 'Save', 'connectpx_booking' ) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>