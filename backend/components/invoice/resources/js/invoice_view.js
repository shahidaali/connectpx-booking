var ConnectpxBookingInvoiceViewDialog = function(Dialog, $, moment, ConnectpxBookingL10nAppDialog) {
	"use strict";

	var $dialog = $('#connectpx_booking-invoice-view-dialog'),
		invoiceData;

	Dialog.showDialog = function( invoice_id, callback, tab ) {
		Dialog.loadInvoiceView({
			id: invoice_id,
			callback: callback
		});
	};

	Dialog.loadInvoiceView = function (options) {
        let data = $.extend({}, options, {
        	action: "connectpx_booking_render_invoice_view",
            csrf_token: ConnectpxBookingL10nGlobal.csrf_token,
        });

        return $.ajax({
            url: ConnectpxBookingL10nGlobal.ajax_url,
            type: 'GET',
            data: data,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                	invoiceData = response.data;

                	$dialog.html( invoiceData.html );

                	var $modal = $('.connectpx_booking-modal', $dialog),
                		$customer_name = $('#connectpx_booking_customer-name', $dialog),
                		$admin_notes = $('#connectpx_booking-admin-notes', $dialog),
                		$pickup_info = $('#connectpx_booking-appointment-pickup-info', $dialog),
                		$destination_info = $('#connectpx_booking-appointment-destination-info', $dialog),
                		$map = $('#connectpx_booking-appointment-map', $dialog),
                		$service_info = $('#connectpx_booking-service-info', $dialog),
                		$schedule_info = $('#connectpx_booking-schedule-info', $dialog),
                		$payment_info = $('#connectpx_booking-appointment-payment-container', $dialog),
                		$btn_update_status_toggle = $('.appointment-update-status-toggle', $dialog),
                		$btn_update_status = $('.appointment-update-status', $dialog),
                		$btn_save = $('.btn-save-appointment', $dialog);
                
                	$pickup_info.html( invoiceData.pickup_info );
                	$destination_info.html( invoiceData.destination_info );
                	$service_info.html( invoiceData.service_info );
                	$schedule_info.html( invoiceData.schedule_info );
                	if( invoiceData.map_link ) {
                		$map.html( invoiceData.map_link.iframe );
                	}
                	$customer_name.text( invoiceData.customer_data.name );
                	$admin_notes.val( invoiceData.admin_notes );
                	$payment_info.html( invoiceData.payment_info );

                    $modal.connectpx_bookingModal('show');

                    $btn_update_status.click(function(e){
                    	e.preventDefault();
                    	$btn_update_status_toggle.find('span').remove();
                    	$btn_update_status_toggle.append( $(this).find('span').clone() );

	                    $.ajax({
				            url: ConnectpxBookingL10nGlobal.ajax_url,
				            type: 'POST',
				            data: {
					        	action: "connectpx_booking_update_appointment_status",
					            csrf_token: ConnectpxBookingL10nGlobal.csrf_token,
					            id: data.id,
					            appointment_status: $(this).attr('data-status'),
					        },
				            dataType: 'json',
				            success: function (response) {
				                if (response.success) {
				                	$modal.connectpx_bookingModal('hide');
				                	if( data.callback !== undefined ) {
				                		data.callback( response );
				                	}
				                }
				            }
				        });
                    });

                    $btn_save.click(function(){
	                    $.ajax({
				            url: ConnectpxBookingL10nGlobal.ajax_url,
				            type: 'POST',
				            data: {
					        	action: "connectpx_booking_save_appointment_form",
					            csrf_token: ConnectpxBookingL10nGlobal.csrf_token,
					            id: data.id,
					            admin_notes: $admin_notes.val(),
					        },
				            dataType: 'json',
				            success: function (response) {
				                if (response.success) {
				                	$modal.connectpx_bookingModal('hide');
				                	if( data.callback !== undefined ) {
				                		data.callback( response );
				                	}
				                }
				            }
				        });
                    });

                    var $adjustment_fields = $('.payment-adjustment-fields-row', $dialog),
                        $adjustment_buttons = $('.payment-adjustment-buttons-row', $dialog),
                        $btn_show_adjustment = $('.payment-adjustment-button', $dialog),
                        $btn_cancel_adjustment = $('.payment-adjustment-cancel', $dialog),
                        $btn_apply_adjustments = $('.btn-apply-adjustments', $dialog),
                		$adjustment_miles = $('#connectpx_booking-adjustment-miles', $dialog),
                		$adjustment_time = $('#connectpx_booking-adjustment-time', $dialog),
                		$adjustment_reason = $('#connectpx_booking-adjustment-reason', $dialog),
                		$adjustment_amount = $('#connectpx_booking-adjustment-amount', $dialog),
                		$btn_update_payment_status_toggle = $('.appointment-update-payment-status-toggle', $dialog),
                		$btn_update_payment_status = $('.appointment-update-payment-status', $dialog);

                	$btn_update_payment_status.click(function(e){
                    	e.preventDefault();
                    	$btn_update_payment_status_toggle.find('span').remove();
                    	$btn_update_payment_status_toggle.append( $(this).find('span').clone() );

	                    $.ajax({
				            url: ConnectpxBookingL10nGlobal.ajax_url,
				            type: 'POST',
				            data: {
					        	action: "connectpx_booking_update_appointment_payment_status",
					            csrf_token: ConnectpxBookingL10nGlobal.csrf_token,
					            id: data.id,
					            payment_status: $(this).attr('data-status'),
					        },
				            dataType: 'json',
				            success: function (response) {
				                if (response.success) {
				                	Dialog.loadInvoiceView({
										id: data.id,
										tab: 'payment',
										callback: data.callback
									});
				                }
				            }
				        });
                    });

                	$btn_show_adjustment.click(function(){
                		$adjustment_fields.show();
                		$adjustment_buttons.hide();
                	});

                	$btn_cancel_adjustment.click(function(){
                		$adjustment_fields.hide();
                		$adjustment_buttons.show();
                	});

                    $btn_apply_adjustments.click(function(){
	                    $.ajax({
				            url: ConnectpxBookingL10nGlobal.ajax_url,
				            type: 'POST',
				            data: {
					        	action: "connectpx_booking_adjust_appointment_payment",
					            csrf_token: ConnectpxBookingL10nGlobal.csrf_token,
					            id: data.id,
					            miles: $adjustment_miles.val(),
					            waiting_time: $adjustment_time.val(),
					            adjustment_reason: $adjustment_reason.val(),
					            adjustment_amount: $adjustment_amount.val(),
					        },
				            dataType: 'json',
				            success: function (response) {
				                if (response.success) {
				                	Dialog.loadInvoiceView({
										id: data.id,
										tab: 'payment',
										callback: data.callback
									});
				                }
				            }
				        });
                    });
                }
            }
        });
    }

	return Dialog;
}({}, jQuery, moment, ConnectpxBookingL10nAppDialog);