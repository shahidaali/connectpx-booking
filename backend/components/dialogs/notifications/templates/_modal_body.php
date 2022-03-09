<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use ConnectpxBooking\Backend\Components\Controls\Container;
use ConnectpxBooking\Backend\Components\Controls\Inputs;
/** @var string $gateway */
?>
<div class="connectpx_booking-js-loading" style="height: 200px;"></div>
<div class="connectpx_booking-js-loading">
    <?php Container::renderHeader( __( 'Notification settings', 'connectpx_booking' ), 'connectpx_booking-js-settings-container' ) ?>
    <input type="hidden" name="notification[id]" value="0">
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="form-group">
                <label for="notification_name"><?php esc_attr_e( 'Name', 'connectpx_booking' ) ?></label>
                <input type="text" class="form-control" id="notification_name" name="notification[name]" value=""/>
                <small class="form-text text-muted"><?php esc_html_e( 'Enter notification name which will be displayed in the list.', 'connectpx_booking' ) ?></small>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <?php Inputs::renderRadioGroup( __( 'State', 'connectpx_booking' ), __( 'Choose whether notification is enabled and sending messages or it is disabled and no messages are sent until you activate the notification.', 'connectpx_booking' ), array(), 1, array( 'name' => 'notification[active]' ) ) ?>
        </div>
    </div>

    <?php $self::renderTemplate( 'backend/components/dialogs/notifications/templates/_types' ) ?>
    <?php static::renderTemplate( 'backend/components/dialogs/notifications/templates/_settings' ) ?>

    <div class="row connectpx_booking-js-recipient-container">
        <div class="col-md-12">
            <div class="form-group">
                <label><?php esc_attr_e( 'Recipients', 'connectpx_booking' ) ?></label>
                <input type="hidden" name="notification[to_customer]" value="0">
                <?php Inputs::renderCheckBox( __( 'Client', 'connectpx_booking' ), 1, null, array( 'name' => 'notification[to_customer]' ) ) ?>
                <input type="hidden" name="notification[to_admin]" value="0">
                <?php Inputs::renderCheckBox( __( 'Administrators', 'connectpx_booking' ), 1, null, array( 'name' => 'notification[to_admin]' ) ) ?>
                <input type="hidden" name="notification[to_custom]" value="0">
                <?php Inputs::renderCheckBox( __( 'Custom', 'connectpx_booking' ), 1, null, array( 'name' => 'notification[to_custom]' ) ) ?>
                <div class="connectpx_booking-js-custom-recipients">
                    <textarea name="notification[custom_recipients]" rows="2" class="form-control"></textarea>
                    <?php if ( $gateway == 'email' ) : ?>
                        <small class="form-text text-muted"><?php esc_html_e( 'You can enter multiple email addresses (one per line)', 'connectpx_booking' ) ?></small>
                    <?php else: ?>
                        <small class="form-text text-muted"><?php esc_html_e( 'You can enter multiple phone numbers (one per line)', 'connectpx_booking' ) ?></small>
                    <?php endif ?>
                </div>
                <small class="form-text text-muted"><?php esc_html_e( 'Choose who will receive this notification.', 'connectpx_booking' ) ?></small>
            </div>
        </div>
    </div>

    <?php Container::renderFooter() ?>
    <?php Container::renderHeader( '', 'connectpx_booking-js-message-container' ) ?>

    <?php $self::renderTemplate( 'backend/components/dialogs/notifications/templates/_subject' ) ?>
    <?php $self::renderTemplate( 'backend/components/dialogs/notifications/templates/_editor' ) ?>
    <?php if ( $gateway == 'email' ) : ?>
        <?php $self::renderTemplate( 'backend/components/dialogs/notifications/templates/_codes' ) ?>
    <?php endif ?>
    <?php Container::renderFooter() ?>
</div>