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
use Bookly\Lib as BooklyLib;
use BooklyConnectpx\Lib\Proxy\Local;
?>
<div class="woocommerce-booking-details">
    <h3><?php _e('Booking Details', 'bookly') ?></h3>
    <div class="woocommerce-booking-details-wrapper">
        <?php __pre($userData->getPickupDetail()); ?>
    </div>
</div>