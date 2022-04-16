<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use ConnectpxBooking\Lib\Entities\Notification;
use ConnectpxBooking\Lib\Config;
?>
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label for="notification_type"><?php esc_attr_e( 'Type', 'connectpx_booking' ) ?></label>
            <select class="form-control custom-select" name="notification[type]" id="notification_type">
                <optgroup label="<?php esc_attr_e( 'Instant notifications', 'connectpx_booking' ) ?>">
                    <option value="<?php echo Notification::TYPE_NEW_BOOKING ?>"
                            data-set="instantly"
                            data-recipients='["customer","admin","custom"]'
                            data-icon='<?php echo esc_attr( Notification::getIcon( Notification::TYPE_NEW_BOOKING ) ) ?>'
                            data-attach='["ics","invoice"]'><?php echo esc_attr( Notification::getTitle( Notification::TYPE_NEW_BOOKING ) ) ?></option>
                    <option value="<?php echo Notification::TYPE_NEW_INVOICE ?>"
                            data-set="instantly"
                            data-recipients='["customer","admin","custom"]'
                            data-icon='<?php echo esc_attr( Notification::getIcon( Notification::TYPE_NEW_INVOICE ) ) ?>'
                            data-attach='["ics","invoice"]'><?php echo esc_attr( Notification::getTitle( Notification::TYPE_NEW_INVOICE ) ) ?></option>
                    <option value="<?php echo Notification::TYPE_APPOINTMENT_STATUS_CHANGED ?>"
                            data-set="instantly"
                            data-recipients='["customer","admin","custom"]'
                            data-icon='<?php echo esc_attr( Notification::getIcon( Notification::TYPE_APPOINTMENT_STATUS_CHANGED ) ) ?>'
                            data-attach='["ics","invoice"]'><?php echo esc_attr( Notification::getTitle( Notification::TYPE_APPOINTMENT_STATUS_CHANGED ) ) ?></option>
                    <option value="<?php echo Notification::TYPE_SCHEDULE_STATUS_CHANGED ?>"
                            data-set="instantly"
                            data-recipients='["customer","admin","custom"]'
                            data-icon='<?php echo esc_attr( Notification::getIcon( Notification::TYPE_SCHEDULE_STATUS_CHANGED ) ) ?>'
                            data-attach='["ics","invoice"]'><?php echo esc_attr( Notification::getTitle( Notification::TYPE_SCHEDULE_STATUS_CHANGED ) ) ?></option>
                    <option value="<?php echo Notification::TYPE_CUSTOMER_NEW_WP_USER ?>"
                            data-set="instantly"
                            data-recipients='["customer"]'
                            data-icon='<?php echo esc_attr( Notification::getIcon( Notification::TYPE_CUSTOMER_NEW_WP_USER ) ) ?>'
                            data-attach='[]'><?php echo esc_attr( Notification::getTitle( Notification::TYPE_CUSTOMER_NEW_WP_USER ) ) ?></option>
                </optgroup>
                <optgroup label="<?php esc_attr_e( 'Scheduled notifications (require cron setup)', 'connectpx_booking' ) ?>">
                    <option value="<?php echo Notification::TYPE_APPOINTMENT_REMINDER ?>"
                            data-set="bidirectional full"
                            data-recipients='["customer","admin","custom"]'
                            data-icon='<?php echo esc_attr( Notification::getIcon( Notification::TYPE_APPOINTMENT_REMINDER ) ) ?>'
                            data-attach='["ics","invoice"]'><?php echo esc_attr( Notification::getTitle( Notification::TYPE_APPOINTMENT_REMINDER ) ) ?></option>
                </optgroup>
            </select>
            <small class="text-muted"><?php esc_html_e( 'Select the type of event at which the notification is sent.', 'connectpx_booking' ) ?></small>
        </div>
    </div>
</div>