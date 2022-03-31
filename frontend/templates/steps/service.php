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
<?php if($service_available): ?>
    <?php echo $progress_bar; ?>

    <div class="cbf-step step-service">
        <div class="cbf-box">
            <h4><?php echo __('Trip Type', 'connectpx_booking'); ?></h4>
            <div class="connectpx_booking_form_errors"></div>
            <?php if($sub_services): ?>
                <div class="choose-service">
                    <?php foreach ($sub_services as $key => $sub_service): ?>
                        <div class="service-item">
                            <button type="button" data-service="<?php echo $key; ?>"><?php echo $sub_service->getTitle(); ?></button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p><?php echo __('This service is not available or your account is not configured. Please contact service provider.', 'connectpx_booking'); ?></p>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <p style="color: red;"><?php echo $service_not_available_message; ?></p>
<?php endif; ?>
<?php // echo $buttons; ?>