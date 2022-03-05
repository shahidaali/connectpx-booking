<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use ConnectpxBooking\Backend\Components\Dialogs;
use ConnectpxBooking\Backend\Components\Controls;
use ConnectpxBooking\Lib\Entities\Appointment;
use ConnectpxBooking\Lib\Entities\Invoice;
use ConnectpxBooking\Lib\Utils\Common;
use ConnectpxBooking\Lib\Utils\Form;
?>
<div class="connectpx_booking-modal connectpx_booking-fade" tabindex="-1" role="dialog"  aria-modal="true">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title"><?php echo __('Update Invoices', 'connectpx_booking'); ?></h5>
            <button type="button" class="close" data-dismiss="connectpx_booking-modal" aria-label="Close"><span aria-hidden="true">×</span></button>
         </div>
         <div class="modal-body">
         	<div class="form-row">
         		<div class="col-md-6">
         			<div class="form-group">
		         		<label for="connectpx_booking-invoice-customer"><?php echo __('Customer', 'connectpx_booking') ?></label> 
		         		<select class="form-control connectpx_booking-js-select" id="connectpx_booking-invoice-customer" data-placeholder="<?php esc_attr_e( 'Customer', 'connectpx_booking' ) ?>">
		         			<option value="all"><?php echo __('All', 'connectpx_booking'); ?></option>
		         			<?php echo Form::selectOptions( $customers ); ?>
		         		</select>
		         	</div>
         		</div>
         		<div class="col-md-6">
         			<div class="form-group">
		         		<label for="connectpx_booking-invoice-period"><?php echo __('Time Period', 'connectpx_booking') ?></label> 
		         		<select class="form-control connectpx_booking-js-select" id="connectpx_booking-invoice-period" data-placeholder="<?php esc_attr_e( 'Time Period', 'connectpx_booking' ) ?>">
		         			<?php echo Form::selectOptions( $periods, 'current_week' ); ?>
		         		</select>
		         	</div>
         		</div>
         	</div>
         </div>
         <div class="modal-footer">
            <div slot="footer">  
            	<button type="button" class="btn ladda-button btn-success btn-update-invoices" data-spinner-size="40" data-style="zoom-in" classname="btn-success"><span class="ladda-label"><?php echo __('Update', 'connectpx_booking') ?></span><span class="ladda-spinner"></span></button> 
            	<button type="button" class="btn ladda-button btn-default" data-spinner-size="40" data-style="zoom-in" data-dismiss="connectpx_booking-modal"><span class="ladda-label"><?php echo __('Cancel', 'connectpx_booking') ?></span><span class="ladda-spinner"></span></button>
            </div>
         </div>
      </div>
   </div>
</div>