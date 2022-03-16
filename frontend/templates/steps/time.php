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
<div class="cbf-js-time-error cbf-label-error"></div>
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
        <div class="cbf-checkbox-group cbf-box">
          <div class="cbf-checkbox-group" style="line-height: 28px; margin-top: 31px;">
             <input type="checkbox" class="cbf-js-return-pickup-time-cbx" id="cbf-return-pickup-time-cbx">
             <label class="cbf-square cbf-checkbox" style="width:28px; float:left; margin-left: 0; margin-right: 5px;" for="cbf-return-pickup-time-cbx">
                <i class="cbf-icon-sm"></i>
             </label>
             <label for="cbf-return-pickup-time-cbx"><?php echo $return_pickup_checkbox_text; ?></label>
          </div>
          <div class="cbf-js-return-pickup-time-cbx-error cbf-label-error"></div>
       </div>  
    <?php endif; ?>
</div>
