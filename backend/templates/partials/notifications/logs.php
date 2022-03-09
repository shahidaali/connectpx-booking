<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use ConnectpxBooking\Backend\Components\Controls\Buttons;
use ConnectpxBooking\Backend\Components\Controls\Inputs;
use ConnectpxBooking\Backend\Components\Dialogs;
use ConnectpxBooking\Backend\Modules\Notifications;
use ConnectpxBooking\Lib\Utils\DateTime;

/** @var array $datatables */
?>

<div class="form-row">
    <div class="col-xl-4 col-lg-5 col-md-8">
        <div class="form-group">
            <button type="button" class="btn btn-default w-100 text-truncate text-left" id="connectpx_booking-email-logs-date-range" data-date="<?php echo date( 'Y-m-d', strtotime( 'first day of' ) ) ?> - <?php echo date( 'Y-m-d', strtotime( 'last day of' ) ) ?>">
                <i class="far fa-calendar-alt mr-1"></i>
                <span><?php echo DateTime::formatDate( 'first day of this month' ) ?> - <?php echo DateTime::formatDate( 'last day of this month' ) ?></span>
            </button>
        </div>
    </div>
</div>
<div class="row">
    <div class="col">
        <table id="connectpx_booking-email-logs" class="table table-striped w-100">
            <thead>
            <tr>
                <?php foreach ( $datatables['email_logs']['settings']['columns'] as $column => $show ) : ?>
                    <?php if ( $show ) : ?>
                        <?php if ( $column == 'type' ) : ?>
                            <th width="1"></th>
                        <?php else : ?>
                            <th><?php echo $datatables['email_logs']['titles'][ $column ] ?></th>
                        <?php endif ?>
                    <?php endif ?>
                <?php endforeach ?>
                <th width="75"></th>
                <th width="16"><?php Inputs::renderCheckBox( null, null, null, array( 'id' => 'connectpx_booking-check-all' ) ) ?></th>
            </tr>
            </thead>
        </table>
        <div class="text-right mt-3">
            <?php Buttons::renderDelete( 'connectpx_booking-email-log-delete' ) ?>
        </div>
    </div>
</div>
<div id="connectpx_booking-email-logs-dialog" class="connectpx_booking-modal connectpx_booking-fade" tabindex=-1 role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php esc_html_e( 'Email details', 'connectpx_booking' ) ?></h5>
                <button type="button" class="close" data-dismiss="connectpx_booking-modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="connectpx_booking-email-to"><?php esc_html_e( 'Recipient', 'connectpx_booking' ) ?></label>
                    <input type="text" id="connectpx_booking-email-to" class="form-control" readonly/>
                </div>
                <div class="form-group">
                    <label for="connectpx_booking-email-subject"><?php esc_html_e( 'Subject', 'connectpx_booking' ) ?></label>
                    <input type="text" id="connectpx_booking-email-subject" class="form-control" readonly/>
                </div>
                <div class="form-group">
                    <label for="connectpx_booking-email-body"><?php esc_html_e( 'Message', 'connectpx_booking' ) ?></label>
                    <textarea id="connectpx_booking-email-body" class="form-control" rows="12" readonly></textarea>
                </div>
                <div class="form-group">
                    <label for="connectpx_booking-email-headers"><?php esc_html_e( 'Headers', 'connectpx_booking' ) ?></label>
                    <textarea id="connectpx_booking-email-headers" class="form-control" rows="4" readonly></textarea>
                </div>
                <div class="form-group">
                    <label for="connectpx_booking-email-attachments"><?php esc_html_e( 'Attachments', 'connectpx_booking' ) ?></label>
                    <textarea id="connectpx_booking-email-attachments" class="form-control" rows="4" readonly></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <?php Buttons::renderCancel( __( 'Close', 'connectpx_booking' ) ) ?>
            </div>
        </div>
    </div>
</div>