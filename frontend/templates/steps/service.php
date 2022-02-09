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

<div class="cbf-step step-service">
    <div class="cbf-box">
        <h4><?php echo __('Select Service', 'connectpx_booking'); ?></h4>
        <div class="choose-service">
            <?php foreach ($services as $key => $service): ?>
                <div class="service-item">
                    <button type="button" data-service="<?php echo $key; ?>"><?php echo $service['title']; ?></button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php // echo $buttons; ?>