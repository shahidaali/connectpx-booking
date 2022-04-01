<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use ConnectpxBooking\Backend\Components\Dialogs;
use ConnectpxBooking\Lib\Entities\Appointment;
use ConnectpxBooking\Lib\Utils\Common;
?>
<div class="connectpx_booking-modal connectpx_booking-fade" tabindex="-1" role="dialog"  aria-modal="true">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title"><?php echo __(sprintf('Appointment #%d (%s)', $appointment->getId(), Appointment::statusToString($appointment->getStatus())), 'connectpx_booking'); ?></h5>
            <button type="button" class="close" data-dismiss="connectpx_booking-modal" aria-label="Close"><span aria-hidden="true">×</span></button>
         </div>
         <div class="modal-body">
         	<div class="nav-hoverable mb-3 connectpx_booking-js-service-tabs" style="">
			   <ul class="nav nav-tabs">
			      <li class="nav-item">
			         <a id="connectpx_booking-appointment-general-tab" class="nav-link <?php if($active_tab == 'appointment') { echo 'active'; } ?>" href="#connectpx_booking-appointment-general" data-toggle="connectpx_booking-tab">
			         <i class="fas fa-fw fa-cog mr-lg-1"></i>
			         <span class="d-none d-lg-inline"><?php echo __('Appointment', 'connectpx_booking'); ?></span>
			         </a>
			      </li>
			      <li class="nav-item connectpx_booking-appointment" style="">
			         <a id="connectpx_booking-appointment-payment-tab" class="nav-link <?php if($active_tab == 'payment') { echo 'active'; } ?>" href="#connectpx_booking-appointment-payment" data-toggle="connectpx_booking-tab">
			         <i class="far fa-fw fa-clock mr-lg-1"></i>
			         <span class="d-none d-lg-inline"><?php echo __('Payment', 'connectpx_booking'); ?></span>
			         </a>
			      </li>
			   </ul>
			</div>
			<div class="tab-content connectpx_booking-js-service-containers">
				<div class="tab-pane <?php if($active_tab == 'appointment') { echo 'active'; } ?>" id="connectpx_booking-appointment-general">
					<div id="connectpx_booking-appointment-general-container">
						<div class="form-group">
			               <h6 class="mb-3 mt-3"><?php echo __('Customer', 'connectpx_booking'); ?></h6>   
			               <ul class="list-unstyled pl-0 connectpx_booking-hide-empty mr-3">
			                  <li class="row mb-1">
			                     <div class="col mt-1"><a title="<?php echo esc_attr('Edit booking details', 'connectpx_booking'); ?>" href=""><span id="connectpx_booking_customer-name"></span></a></div>
			                     <?php 
			                     if( in_array( $appointment->getStatus(), [
				                     	Appointment::STATUS_CANCELLED, 
				                     	Appointment::STATUS_REJECTED, 
				                     	Appointment::STATUS_NOSHOW, 
				                     	Appointment::STATUS_DONE
			                     ])): ?>
			                     	<div class="ml-auto">
				                        <strong class="text-muted"><?php echo __('Status:', 'connectpx_booking') ?> <?php echo __(Appointment::statusToString($appointment->getStatus()), 'connectpx_booking'); ?></strong>
				                     </div>
			                     <?php elseif(Common::isCurrentUserAdmin()): ?>
				                     <div class="ml-auto">
				                        <div class="dropdown d-inline-block">
				                           <button type="button" class="btn btn-default px-2 py-1 dropdown-toggle appointment-update-status-toggle" data-toggle="dropdown" data-original-title="" title=""><span class="<?php echo Appointment::statusToIcon( $appointment->getStatus() ) ?>"></span></button> 
				                           <div class="dropdown-menu">
				                           	<?php foreach ( $statuses as $key => $status ) { ?>
				                           		<a href="#" class="dropdown-item pl-3 appointment-update-status" data-status="<?php echo $status['id']; ?>"><span class="fa-fw mr-2 <?php echo $status['icon']; ?>"></span><?php echo $status['title']; ?> </a>
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
							         <td><div id="connectpx_booking-appointment-pickup-info"></div></td>
							         <td><div id="connectpx_booking-appointment-destination-info"></div></td>
							      </tr>
							   </tbody>
							</table>
			         	</div>
			         	<div class="form-group">
			         		<h6 class="mb-3 mt-3"><?php echo __('Route', 'connectpx_booking'); ?></h6>
			         		<div id="connectpx_booking-appointment-map"></div>
			         	</div>

			         	<?php if(Common::isCurrentUserAdmin()): ?>
			            	<div class="form-group"><label for="connectpx_booking-admin-notes"><?php echo __('Admin notes', 'connectpx_booking') ?></label> <textarea class="form-control" id="connectpx_booking-admin-notes"></textarea></div>
			            <?php endif; ?>
					</div>
				</div>
				<div class="tab-pane <?php if($active_tab == 'payment') { echo 'active'; } ?>" id="connectpx_booking-appointment-payment">
					<div id="connectpx_booking-appointment-payment-container">

					</div>
				</div>
			</div>
         </div>
         <div class="modal-footer">
            <div slot="footer">  
            	<?php if(Common::isCurrentUserAdmin()): ?>
            		<button type="button" class="btn ladda-button btn-success btn-save-appointment" data-spinner-size="40" data-style="zoom-in" classname="btn-success"><span class="ladda-label"><?php echo __('Save', 'connectpx_booking') ?></span><span class="ladda-spinner"></span></button> 
            	<?php endif; ?>
            	<button type="button" class="btn ladda-button btn-default" data-spinner-size="40" data-style="zoom-in" data-dismiss="connectpx_booking-modal"><span class="ladda-label"><?php echo __('Cancel', 'connectpx_booking') ?></span><span class="ladda-spinner"></span></button>
            </div>
         </div>
      </div>
   </div>
</div>