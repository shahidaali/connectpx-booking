<?php 
use ConnectpxBooking\Lib;
use ConnectpxBooking\Lib\Utils;
use ConnectpxBooking\Lib\Entities\Appointment;
use ConnectpxBooking\Lib\Utils\Common;
use ConnectpxBooking\Lib\Utils\DateTime;
use ConnectpxBooking\Lib\Config;
use ConnectpxBooking\Backend\Components\Dialogs;
?>
<div id="connectpx_booking_tbs" class="wrap">
    <div class="form-row align-items-center mb-3">
        <h4 class="col m-0"><?php esc_html_e( 'Invoices', 'connectpx_booking' ) ?></h4>
    </div>

    <?php echo Utils\Session::falsh_messages(); ?>

    <div class="card">
        <div class="card-body">
            <div class="form-row">
                <div class="col-md-1">
                    <div class="form-group">
                        <input class="form-control" type="text" id="connectpx_booking-filter-id" placeholder="<?php esc_attr_e( 'No.', 'connectpx_booking' ) ?>"/>
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-default w-100 mb-3 text-truncate text-left" id="connectpx_booking-filter-date" data-date="<?php echo date( 'Y-m-d', strtotime( 'first day of' ) ) ?> - <?php echo date( 'Y-m-d', strtotime( 'last day of' ) ) ?>">
                        <i class="far fa-calendar-alt mr-1"></i>
                        <span>
                            <?php echo DateTime::formatDate( 'first day of this month' ) ?> - <?php echo DateTime::formatDate( 'last day of this month' ) ?>
                        </span>
                    </button>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-default w-100 mb-3 text-truncate text-left" id="connectpx_booking-filter-creation-date" data-date="any">
                        <i class="far fa-calendar-alt mr-1"></i>
                        <span>
                            <?php esc_html_e( 'Created at any time', 'connectpx_booking' ) ?>
                        </span>
                    </button>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <select class="form-control <?php echo esc_attr( $customers === false ? 'connectpx_booking-js-select-ajax' : 'connectpx_booking-js-select' ) ?>" id="connectpx_booking-filter-customer"
                                data-placeholder="<?php esc_attr_e( 'Customer', 'connectpx_booking' ) ?>" <?php echo esc_attr( $customers === false ? 'data-ajax--action' : 'data-action' ) ?>="connectpx_booking_get_customers_list">
                        <?php if ( $customers !== false ) : ?>
                            <?php foreach ( $customers as $customer_id => $customer ) : ?>
                                <option value="<?php echo esc_attr( $customer_id ) ?>" data-search='<?php echo esc_attr( json_encode( array_values( $customer ) ) ) ?>'><?php echo esc_html( $customer['full_name'] ) ?></option>
                            <?php endforeach ?>
                        <?php endif ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="form-group">
                        <select class="form-control connectpx_booking-js-select" id="connectpx_booking-filter-status" data-placeholder="<?php esc_attr_e( 'Status', 'connectpx_booking' ) ?>">
                            <?php foreach ( Appointment::getStatuses() as $status ): ?>
                                <option value="<?php echo esc_attr( $status ) ?>"><?php echo esc_html( Appointment::statusToString( $status ) ) ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>
            </div>
            <table id="connectpx_booking-invoices-list" class="table table-striped w-100">
                <thead>
                <tr>
                    <?php foreach ( $datatables['invoices']['settings']['columns'] as $column => $show ) : ?>
                        <?php if ( $show ) : ?>
                            <th><?php echo esc_html( $datatables['invoices']['titles'][ $column ] ) ?></th>
                        <?php endif ?>
                    <?php endforeach ?>
                    <th></th>
                    <th width="16"></th>
                </tr>
                </thead>
            </table>

            <div class="text-right mt-3">
                <?php // Controls\Buttons::renderDelete( 'connectpx_booking-js-show-confirm-deletion' ) ?>
            </div>
        </div>
    </div>

    <?php // Dialogs\Appointment\Edit\Dialog::render() ?>
</div>
