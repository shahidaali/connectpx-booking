<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use ConnectpxBooking\Backend\Components\Dialogs;
use ConnectpxBooking\Lib\Entities\Schedule;
use ConnectpxBooking\Lib\Utils\Common;
?>
<div class="connectpx_booking-modal connectpx_booking-fade" tabindex="-1" role="dialog"  aria-modal="true">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title"><?php echo __(sprintf('Schedule #%d (%s)', $schedule->getId(), Schedule::statusToString($schedule->getStatus())), 'connectpx_booking'); ?></h5>
            <button type="button" class="close" data-dismiss="connectpx_booking-modal" aria-label="Close"><span aria-hidden="true">×</span></button>
         </div>
         <div class="modal-body">
      			
				<div class="form-group">
               <h6 class="mb-3 mt-3"><?php echo __('Customer', 'connectpx_booking'); ?></h6>   
               <ul class="list-unstyled pl-0 connectpx_booking-hide-empty mr-3">
                  <li class="row mb-1">
                     <div class="col mt-1"><a title="<?php echo esc_attr('Edit booking details', 'connectpx_booking'); ?>" href=""><span id="connectpx_booking_customer-name"></span></a></div>
                     <?php if($schedule->getStatus() == Schedule::STATUS_CANCELLED): ?>
                     	<div class="ml-auto">
	                        <span class="text-danger"><?php echo __(Schedule::statusToString($schedule->getStatus()), 'connectpx_booking'); ?></span>
	                     </div>
                     <?php elseif(Common::isCurrentUserAdmin()): ?>
	                     <div class="ml-auto">
	                        <div class="dropdown d-inline-block">
	                           <button type="button" class="btn btn-default px-2 py-1 dropdown-toggle schedule-update-status-toggle" data-toggle="dropdown" data-original-title="" title=""><span class="<?php echo Schedule::statusToIcon( $schedule->getStatus() ) ?>"></span></button> 
	                           <div class="dropdown-menu">
	                           	<?php foreach ( $statuses as $key => $status ) { ?>
	                           		<a href="#" class="dropdown-item pl-3 schedule-update-status" data-status="<?php echo $status['id']; ?>"><span class="fa-fw mr-2 <?php echo $status['icon']; ?>"></span><?php echo $status['title']; ?> </a>
	                           	<?php } ?>
	                           	</div>
	                        </div>
	                     </div>
	                  <?php endif; ?>

                  </li>
               </ul>
            </div>
            <div class="form-group">
            	<h6 class="mb-3 mt-3"><?php echo __('Schedule', 'connectpx_booking'); ?></h6>
            	<div  id="connectpx_booking-schedule-info"></div>
         	</div>
            <div class="form-group">
            	<h6 class="mb-3 mt-3"><?php echo __('Service', 'connectpx_booking'); ?></h6>
            	<div  id="connectpx_booking-service-info"></div>
         	</div>
         	<div class="form-group">
         		<table class="table table-bordered">
				   <thead>
				      <tr>
				         <th width="50%"><?php echo __('Pickup Detail', 'connectpx_booking'); ?></th>
				         <th width="50%"><?php echo __('Destination Detail', 'connectpx_booking'); ?></th>
				      </tr>
				   </thead>
				   <tbody>
				      <tr>
				         <td><div id="connectpx_booking-schedule-pickup-info"></div></td>
				         <td><div id="connectpx_booking-schedule-destination-info"></div></td>
				      </tr>
				   </tbody>
				</table>
         	</div>
         	<div class="form-group">
         		<h6 class="mb-3 mt-3"><?php echo __('Route', 'connectpx_booking'); ?></h6>
         		<div id="connectpx_booking-schedule-map"></div>
         	</div>
					
         </div>
         <div class="modal-footer">
            <div slot="footer">  
            	<button type="button" class="btn ladda-button btn-default" data-spinner-size="40" data-style="zoom-in" data-dismiss="connectpx_booking-modal"><span class="ladda-label"><?php echo __('Cancel', 'connectpx_booking') ?></span><span class="ladda-spinner"></span></button>
            </div>
         </div>
      </div>
   </div>
</div>