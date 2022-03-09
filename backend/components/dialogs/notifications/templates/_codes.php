<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use ConnectpxBooking\Lib\Entities\Notification;
$codes = new \ConnectpxBooking\Backend\Modules\Lib\NotificationCodes( 'email' );
?>
<div class="form-group connectpx_booking-js-codes-container">
    <a class="collapsed mb-2 d-inline-block" data-toggle="collapse" href="#connectpx_booking-notification-codes" role="button" aria-expanded="false" aria-controls="collapseExample">
        <?php esc_attr_e( 'Codes', 'connectpx_booking' ) ?>
    </a>
    <div class="collapse" id="connectpx_booking-notification-codes">
        <?php foreach ( Notification::getTypes() as $notification_type ) :
            $codes->render( $notification_type );
        endforeach ?>
    </div>
</div>