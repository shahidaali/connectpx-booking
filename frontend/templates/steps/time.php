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
<div class="cbf-box cbf-table cbf-timepicker-row">
    <div class="cbf-form-group">
        <label><?php echo __('Pickup Time', 'connectpx_booking'); ?></label>
        <div>
            <input type="text" class="cbf-pickup-time">
        </div>
    </div>
   <?php if( $userData->getSubService()->isRoundTrip() ): ?>
    <div class="cbf-form-group">
        <label><?php echo __('Return Pickup Time', 'connectpx_booking'); ?></label>
        <div>
            <input type="text" class="cbf-return-pickup-time">
        </div>  
    </div>  
<?php endif; ?>
</div>
