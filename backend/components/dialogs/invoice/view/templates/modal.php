<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use ConnectpxBooking\Backend\Components\Dialogs;
use ConnectpxBooking\Backend\Components\Controls;
use ConnectpxBooking\Lib\Utils\Common;
use ConnectpxBooking\Lib;
use ConnectpxBooking\Lib\Entities\Invoice;

$pending_count = count($invoice->getPendingAppointments());
?>
<div class="connectpx_booking-modal connectpx_booking-fade" tabindex="-1" role="dialog"  aria-modal="true">
   <div class="modal-dialog modal-xl">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title"><?php echo __(sprintf('Invoice #%d (%s)', $invoice->getId(), Invoice::statusToString($invoice->getStatus())), 'connectpx_booking'); ?></h5>
            <button type="button" class="close" data-dismiss="connectpx_booking-modal" aria-label="Close"><span aria-hidden="true">×</span></button>
         </div>
         <div class="modal-body">
            <div class="form-group">
               <ul class="list-unstyled pl-0 connectpx_booking-hide-empty mr-3">
                  <li class="row mb-1">
                     <?php if(Common::isCurrentUserAdmin()): ?>
                        <div class="ml-3"><?php echo __('Notification Status:', 'connectpx_booking') ?> <?php echo $invoice->getNotificationStatus() ? sprintf('<span class="text-success">%s</span>', __('Sent', 'connectpx_booking')) : sprintf('<span class="text-danger">%s</span>', __('Not sent', 'connectpx_booking')) ?></div>
                        <div class="ml-3"><?php echo __('Pending appointments:', 'connectpx_booking') ?> <?php echo $pending_count; ?></div>
                     <?php endif; ?>
                     
                     <?php 
                     if( in_array( $invoice->getStatus(), [
                           Invoice::STATUS_COMPLETED
                     ])): ?>
                        <div class="ml-auto">
                           <strong class="text-muted"><?php echo __('Status:', 'connectpx_booking') ?> <?php echo __(Invoice::statusToString($invoice->getStatus()), 'connectpx_booking'); ?></strong>
                        </div>
                     <?php elseif($pending_count <= 0 && Common::isCurrentUserAdmin()): ?>
                        
                        <div class="ml-auto">
                           <div class="dropdown d-inline-block">
                              <button type="button" class="btn btn-default px-2 py-1 dropdown-toggle invoice-update-status-toggle" data-toggle="dropdown" data-original-title="" title=""><span class="<?php echo Lib\Entities\Invoice::statusToIcon( $invoice->getStatus() ) ?>"></span></button> 
                              <div class="dropdown-menu">
                                 <?php foreach ( $statuses as $key => $status ) { ?>
                                    <a href="#" class="dropdown-item pl-3 invoice-update-status" data-status="<?php echo $status['id']; ?>"><span class="fa-fw mr-2 <?php echo $status['icon']; ?>"></span><?php echo $status['title']; ?> </a>
                                 <?php } ?>
                                 </div>
                           </div>
                        </div>
                     <?php endif; ?>
                  </li>
               <?php if($pending_count > 0 && Common::isCurrentUserAdmin()): ?>
                  <p class="text-danger"><em><small>Please update pending appointments to mark this invoice completed.</small></em></p>
               <?php endif; ?>
               </ul>
            </div>
            <div class="invoice-preview-table table-responsive">
               <?php echo $preview_table; ?>
            </div>
         </div>
         <div class="modal-footer">
            <div slot="footer"> 

               <?php if( $pending_count <= 0 ):  ?>
                  <?php if(Common::isCurrentUserAdmin()): ?>
                     <button type="button" class="btn ladda-button btn-warning btn-update-paid" data-spinner-size="40" data-style="zoom-in" classname="btn-success"><span class="ladda-label"><?php echo __('Mark Paid', 'connectpx_booking') ?></span><span class="ladda-spinner"></span></button> 
                  <?php endif; ?>
                  <?php if(Common::isCurrentUserAdmin()): ?>
                     <button type="button" class="btn ladda-button btn-info btn-send-notification" data-spinner-size="40" data-style="zoom-in" classname="btn-success"><span class="ladda-label"><?php echo __('Send Notification', 'connectpx_booking') ?></span><span class="ladda-spinner"></span></button> 
                  <?php endif; ?>
               <?php endif; ?>

               <?php if(Common::isCurrentUserAdmin()): ?>
                  <button type="button" class="btn ladda-button btn-success btn-update-invoice" data-spinner-size="40" data-style="zoom-in" classname="btn-success"><span class="ladda-label"><?php echo __('Recalculate Totals', 'connectpx_booking') ?></span><span class="ladda-spinner"></span></button> 
               <?php endif; ?>
            	<button type="button" class="btn ladda-button btn-default" data-spinner-size="40" data-style="zoom-in" data-dismiss="connectpx_booking-modal"><span class="ladda-label"><?php echo __('Cancel', 'connectpx_booking') ?></span><span class="ladda-spinner"></span></button>
            </div>
         </div>
      </div>
   </div>
</div>