<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use ConnectpxBooking\Backend\Components\Controls\Buttons;
/** @var bool $show_reason */
?>
<div id="connectpx_booking-customer-bookings-cancel-dialog" class="connectpx_booking-modal connectpx_booking-fade" tabindex=-1 role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form>
                <div class="modal-header">
                    <h5><?php esc_html_e( 'Cancel Appointment', 'connectpx_booking' ) ?></h5>
                    <button type="button" class="close" data-dismiss="connectpx_booking-modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger collapse" id="connectpx_booking-cancel-error"></div>
                    <?php esc_html_e( 'You are going to cancel a scheduled appointment. Are you sure?', 'connectpx_booking' ) ?>
                    <?php if ( $show_reason ) : ?>
                        <div class="form-group">
                            <input class="form-control" id="connectpx_booking-cancel-reason" type="text" placeholder="<?php esc_html_e( 'Cancellation reason', 'connectpx_booking' ) ?>"/>
                            <div class="alert alert-danger mt-2 collapse" id="connectpx_booking-cancel-reason-error"><?php esc_html_e( 'Required', 'connectpx_booking' ) ?></div>
                        </div>
                    <?php endif ?>
                </div>
                <div class="modal-footer">
                    <div>
                        <?php Buttons::render( 'connectpx_booking-yes', 'btn-danger', __( 'Yes', 'connectpx_booking' ) ) ?>
                        <?php Buttons::renderCancel( __( 'No', 'connectpx_booking' ) ) ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
