<?php 
use ConnectpxBooking\Lib\Utils\DateTime;
use ConnectpxBooking\Lib\Utils\Price;
use ConnectpxBooking\Lib\Entities\Appointment;

$subService = $appointment->getSubService();
$payment_details = !empty($appointment->getPaymentDetails()) ? json_decode($appointment->getPaymentDetails(), true) : null;
$payment_adjustments = $payment_details && isset($payment_details['adjustments']) ? $payment_details['adjustments'] : [];
$lineItems = $subService->paymentLineItems(
	$appointment->getDistance(),
	$appointment->getWaitingTime(),
	$appointment->getIsAfterHours(),
	$appointment->getIsNoShow(),
	$payment_adjustments
);
?>
<div class="form-group">
	<h6 class="mb-3 mt-3"><?php echo __('Service', 'connectpx_booking'); ?></h6>
	<div  class="connectpx_booking-service-info"><?php echo $service_info; ?></div>
</div>
<div class="form-group">
   <h6 class="mb-3 mt-3"><?php echo __('Payment', 'connectpx_booking'); ?></h6>   
   <ul class="list-unstyled pl-0 connectpx_booking-hide-empty mr-3">
      <li class="row mb-1">
         <div class="col mt-1">
         	<div class="connectpx_booking-payment-info">
				<div class="list-item"><strong><?php echo __('Payment Date:', 'connectpx_booking'); ?></strong> <span><?php echo $appointment->getPaymentDate() ? DateTime::formatDateTime( $appointment->getPaymentDate() ) : "N/A"; ?></span></div>
				<div class="list-item"><strong><?php echo __('Payment Type:', 'connectpx_booking'); ?></strong> <span><?php echo Appointment::paymentTypeToString($appointment->getPaymentType()); ?></span></div>
				<div class="list-item"><strong><?php echo __('Payment Status:', 'connectpx_booking'); ?></strong> <span><?php echo Appointment::paymentStatusToString($appointment->getPaymentStatus()); ?></span></div>
			</div>
         </div>
         <div class="ml-auto">
            <div class="dropdown d-inline-block">
               <button type="button" class="btn btn-default px-2 py-1 dropdown-toggle appointment-update-payment-status-toggle" data-toggle="dropdown" data-original-title="" title=""><span class="<?php echo Appointment::paymentStatusToIcon( $appointment->getPaymentStatus() ) ?>"></span></button> 
               <div class="dropdown-menu">
               	<?php foreach ( $payment_statuses as $key => $status ) { ?>
               		<a href="#" class="dropdown-item pl-3 appointment-update-payment-status" data-status="<?php echo $status['id']; ?>"><span class="fa-fw mr-2 <?php echo $status['icon']; ?>"></span><?php echo $status['title']; ?> </a>
               	<?php } ?>
               	</div>
            </div>
         </div>
      </li>
   </ul>
</div>
<div class="table-responsive mt-3">
   <table class="table table-bordered">
      <tbody class="payment-detail-table">
  	 	<?php foreach ($lineItems['items'] as $key => $lineItem) { ?>
       	  	<tr>
       	  		<td><?php echo  $lineItem['qty'] > 1 ? sprintf('%d &times; %s', $lineItem['qty'], $lineItem['label']) : $lineItem['label']; ?></td>
       	  		<td class="text-right"><?php echo $lineItem['total'] <> 0 ? Price::format( $lineItem['total'] ) : 'Free'; ?></td>
       	  	</tr>
   	  	<?php } ?>
      </tbody>
      <tfoot>
         <tr>
            <th><?php echo __('Subtotal', 'connectpx_booking') ?></th>
            <th class="text-right"><?php echo Price::format( $lineItems['totals'] ); ?></th>
         </tr>
         <tr class="payment-adjustment-fields-row" style="display: none;">
            <th></th>
            <th style="font-weight: normal;">
            	<div class="form-group">
               		<label for="connectpx_booking-adjustment-miles"><?php echo __('Miles (One Sided)', 'connectpx_booking') ?></label> 
               		<input class="form-control" type="number" step="1" id="connectpx_booking-adjustment-miles" value="<?php echo $appointment->getDistance(); ?>">
               	</div>
               	<div class="form-group">
               		<label for="connectpx_booking-adjustment-time"><?php echo __('Waiting Time (Mins.)', 'connectpx_booking') ?></label> 
               		<input class="form-control" type="number" step="1" id="connectpx_booking-adjustment-time" value="<?php echo $appointment->getWaitingTime(); ?>">
               	</div>
               	<div class="form-group">
               		<label for="connectpx_booking-adjustment-reason"><?php echo __('Reason', 'connectpx_booking') ?></label> 
               		<textarea class="form-control" id="connectpx_booking-adjustment-reason"></textarea>
               	</div>
               	<div class="form-group">
               		<label for="connectpx_booking-adjustment-amount"><?php echo __('Amount', 'connectpx_booking') ?></label> 
               		<input class="form-control" type="number" step="1" id="connectpx_booking-adjustment-amount">
               	</div>
               <div class="text-right">
               		<button class="btn btn-default  payment-adjustment-cancel"><?php echo __('Cancel', 'connectpx_booking') ?></button> 
               		<button type="button" class="btn ladda-button btn-success btn-apply-adjustments" data-spinner-size="40" data-style="zoom-in"><span class="ladda-label"><?php echo __('Apply', 'connectpx_booking') ?></span><span class="ladda-spinner"></span></button>
               	</div>
            </th>
         </tr>
         <tr>
            <th><?php echo __('Total', 'connectpx_booking') ?></th>
            <th class="text-right"><?php echo Price::format( $lineItems['totals'] ); ?></th>
         </tr>
         <tr>
            <th><?php echo __('Paid', 'connectpx_booking') ?></th>
            <th class="text-right"><?php echo Price::format( $appointment->getPaidAmount() ); ?></th>
         </tr>
         <tr>
            <th><?php echo __('Due', 'connectpx_booking') ?></th>
            <th class="text-right"><?php echo Price::format( $lineItems['totals'] - $appointment->getPaidAmount() ); ?></th>
         </tr>
         <tr class="payment-adjustment-buttons-row">
            <th style="border-left-color: rgb(255, 255, 255); border-bottom-color: rgb(255, 255, 255);"></th>
            <th class="text-right"><button class="btn btn-default payment-adjustment-button"><?php echo __('Manual adjustment', 'connectpx_booking') ?></button> </th>
         </tr>
      </tfoot>
   </table>
</div>
						