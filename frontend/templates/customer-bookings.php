<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use ConnectpxBooking\Lib\Utils\Common;
use ConnectpxBooking\Lib\Config;
use ConnectpxBooking\Backend\Components\Dialogs as ConnectpxBookingDialogs;
use ConnectpxBooking\Frontend\Components\Dialogs;

/** @var ConnectpxBookingLib\Entities\Customer $customer */
?>
<div id="connectpx_booking_tbs" class="wrap connectpx_booking-customer-bookings">
    <div class="mt-4">
        <div class="connectpx_booking-js-customer-bookings-content connectpx_booking-js-customer-bookings-content-appointments">
            <?php if ( $filters ) : ?>
            <div class="form-row">
                <div class="col-md-4">
                    <button type="button" class="btn btn-default w-100 mb-3 text-truncate text-left" id="connectpx_booking-filter-date">
                        <i class="far fa-calendar-alt mr-1"></i>
                        <span>
                            <?php esc_attr_e( 'Any time', 'connectpx_booking' ) ?>
                        </span>
                    </button>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <select class="form-control connectpx_booking-js-select" id="connectpx_booking-filter-service" data-placeholder="<?php echo esc_attr( 'Services', 'connectpx_booking' ) ?>">
                            <?php foreach ( $services as $service ) : ?>
                                <option value="<?php echo $service['id'] ?>"><?php echo esc_html( $service['title'] ) ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>
            </div>
            <?php endif ?>
            <table class="table table-striped connectpx_booking-appointments-list w-100">
                <thead>
                <tr>
                    <?php foreach ( $appointment_columns as $column ) : ?>
                        <?php if ( $column != 'timezone' )  : ?>
                            <th><?php echo $titles[ $column ] ?></th>
                        <?php endif ?>
                    <?php endforeach ?>
                </tr>
                </thead>
            </table>
        </div>
    </div>
    <?php //Dialogs\Reschedule\Dialog::render() ?>
    <?php Dialogs\Appointment\Cancel\Dialog::render( $show_reason ) ?>
    <?php //Dialogs\Delete\Dialog::render() ?>
    <?php ConnectpxBookingDialogs\Appointment\Edit\Dialog::render() ?>
</div>