<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use ConnectpxBooking\Backend\Components\Controls\Buttons;
use ConnectpxBooking\Backend\Components\Controls\Inputs;
?>
<form id="connectpx_booking-js-notification-modal" class="connectpx_booking-modal connectpx_booking-fade" tabindex=-1 role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="close" data-dismiss="connectpx_booking-modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <?php static::renderTemplate( 'backend/components/dialogs/notifications/templates/_modal_body', compact( 'self', 'gateway' ) ) ?>
            </div>
            <div class="modal-footer">
                <?php Inputs::renderCsrf() ?>
                <?php Buttons::render( null, 'connectpx_booking-js-save btn-success', __( 'Save notification', 'connectpx_booking' ) ) ?>
                <?php Buttons::renderCancel() ?>
            </div>
        </div>
    </div>
</form>