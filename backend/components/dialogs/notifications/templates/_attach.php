<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use ConnectpxBooking\Backend\Components\Controls\Inputs;
?>
<div class="form-group connectpx_booking-js-attach-container">
    <div class="connectpx_booking-js-attach connectpx_booking-js-ics">
        <input type="hidden" name="notification[attach_ics]" value="0">
        <?php Inputs::renderCheckBox( __( 'Attach ICS file', 'connectpx_booking' ), 1, null, array( 'name' => 'notification[attach_ics]' ) ) ?>
    </div>
</div>