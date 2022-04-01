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

<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use ConnectpxBooking\Lib;
?>
<div class="woocommerce-booking-details">
    <h3><?php _e('Booking Details', 'bookly') ?></h3>
    <div class="woocommerce-booking-details-wrapper">
        <h5><?php echo __('Pickup Details', 'connectpx_booking') ?></h5>
        <div>
            <?php 
            $pickupDetail = $userData->getPickupDetail();
            $pickupDetail['patient_name'] = $userData->getPickupPatientName();
            ?>
            <?php echo Lib\Utils\Common::formatedPickupInfo( $pickupDetail ); ?>
        </div>
    </div>
    <div class="woocommerce-booking-details-wrapper">
        <h5><?php echo __('Destination Details', 'connectpx_booking') ?></h5>
        <div>
            <?php echo Lib\Utils\Common::formatedDestinationInfo( $userData->getDestinationDetail() ); ?>
        </div>
    </div>
</div>