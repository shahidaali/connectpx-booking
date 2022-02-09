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

<?php echo $progress_bar; ?>

<div class="cbf-step step-date">
    <div class="cbf-step-content">
        <h4><?php echo __('Date & Time', 'connectpx_booking'); ?></h4>
        <div class="cbf-box cbf-table">
            <div class="cbf-form-group cbf-datepicker">
                <label><?php echo __('Select Date', 'connectpx_booking'); ?></label>
                <div>
                    <input type="text" class="cbf-booking-date">
                </div>
            </div>
            <div class="cbf-timepicker">
                
            </div>
        </div>
    </div>
</div>

<?php echo $buttons; ?>