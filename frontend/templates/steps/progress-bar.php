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
<div class="cbf-step-bar">
    <?php $is_active = true; $counter = 1; ?>
    <?php foreach($steps as $key => $step): ?>
        <div class="cbf-bar-item <?php if($is_active) echo 'active'; ?>">
            <div class="title"><?php echo $counter; ?>. <?php echo $step['title']; ?></div>          
            <div class="step"></div>
        </div>    
        <?php 
            if($userData->getActiveStep() == $key) 
                $is_active = false; 

            $counter++;
        ?>
    <?php endforeach; ?>
</div> 