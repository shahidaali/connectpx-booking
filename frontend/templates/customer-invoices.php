<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use ConnectpxBooking\Lib\Utils\Common;
use ConnectpxBooking\Lib\Config;
use ConnectpxBooking\Lib\Entities\Invoice;
use ConnectpxBooking\Backend\Components\Dialogs as ConnectpxBookingDialogs;
use ConnectpxBooking\Frontend\Components\Dialogs;

/** @var ConnectpxBookingLib\Entities\Customer $customer */
?>
<div id="connectpx_booking_tbs" class="wrap connectpx_booking-customer-invoices">
    <div class="mt-4">
        <div class="connectpx_booking-js-customer-invoices-content connectpx_booking-js-customer-invoices-content">
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
                    <button type="button" class="btn btn-default w-100 mb-3 text-truncate text-left" id="connectpx_booking-filter-due-date" data-date="any">
                        <i class="far fa-calendar-alt mr-1"></i>
                        <span>
                            <?php esc_html_e( 'Due at any time', 'connectpx_booking' ) ?>
                        </span>
                    </button>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <select class="form-control connectpx_booking-js-select" id="connectpx_booking-filter-status" data-placeholder="<?php esc_attr_e( 'Status', 'connectpx_booking' ) ?>">
                            <?php foreach ( Invoice::getStatuses() as $status ): ?>
                                <option value="<?php echo esc_attr( $status ) ?>"><?php echo esc_html( Invoice::statusToString( $status ) ) ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>
            </div>
            <?php endif ?>
            <table class="table table-striped connectpx_booking-invoices-list w-100">
                <thead>
                <tr>
                    <?php foreach ( $invoice_columns as $column ) : ?>
                        <?php if ( $column != 'timezone' )  : ?>
                            <th><?php echo $titles[ $column ] ?></th>
                        <?php endif ?>
                    <?php endforeach ?>
                </tr>
                </thead>
            </table>
        </div>
    </div>
    <?php ConnectpxBookingDialogs\Appointment\Edit\Dialog::render() ?>
    <?php ConnectpxBookingDialogs\Invoice\View\Dialog::render() ?>
</div>