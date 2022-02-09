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
$active_step = $userData->getActiveStep();
$next_step = null;
$prev_step = null;

$step_keys = array_keys($steps);
$position = array_search($active_step, $step_keys);

if (isset($step_keys[$position + 1])) {
    $next_step = $step_keys[$position + 1];
}

if (isset($step_keys[$position - 1])) {
    $prev_step = $step_keys[$position - 1];
}
?>
<div class="cbf-buttons">
    <?php if( $prev_step ): ?>
        <button type="button" class="cbf-button cbf-button-primary cbf-button-prev" data-step="<?php echo $prev_step; ?>"><?php echo __('Back', 'connectpx_booking') ?></button>
    <?php endif; ?>
    <?php if( $next_step ): ?>
        <button type="button" class="cbf-button cbf-button-primary cbf-button-next" data-step="<?php echo $next_step; ?>"><?php echo __('Next', 'connectpx_booking') ?></button>
    <?php endif; ?>
</div> 