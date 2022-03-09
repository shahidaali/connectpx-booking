<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use ConnectpxBooking\Lib;

$connectpx_booking_email_sender_name = Lib\Utils\Common::getOption ( 'email_sender_name' ) == '' ?
    get_option( 'blogname' ) : Lib\Utils\Common::getOption ( 'email_sender_name' );
$connectpx_booking_email_sender = Lib\Utils\Common::getOption ( 'email_sender' ) == '' ?
    get_option( 'admin_email' ) : Lib\Utils\Common::getOption ( 'email_sender_name' );
?>
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label for="sender_name<?php echo esc_attr( $tail ) ?>"><?php esc_html_e( 'Sender name', 'connectpx_booking' ) ?></label>
            <input id="sender_name<?php echo esc_attr( $tail ) ?>" name="connectpx_booking_email_sender_name" class="form-control" type="text" value="<?php echo esc_attr( $connectpx_booking_email_sender_name ) ?>">
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label for="sender_email<?php echo esc_attr( $tail ) ?>"><?php esc_html_e( 'Sender email', 'connectpx_booking' ) ?></label>
            <input id="sender_email<?php echo esc_attr( $tail ) ?>" name="connectpx_booking_email_sender" class="form-control connectpx_booking-sender" type="text" value="<?php echo esc_attr( $connectpx_booking_email_sender ) ?>">
        </div>
    </div>
</div>