<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://devinvinson.com
 * @since      1.0.0
 *
 * @package    ConnectpxBooking
 * @subpackage ConnectpxBooking/public/partials
 */
?>

<div class="connectpx_booking_form">
   <div class="connectpx_booking_form_container"></div>
</div>
<script type="text/javascript">
   var ConnextpxBookingShortcode = <?php echo json_encode( $shortcode_options ) ?>;
</script>