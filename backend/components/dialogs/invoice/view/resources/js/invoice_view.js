var ConnectpxBookingInvoiceViewDialog = function(Dialog, $, moment, ConnectpxBookingL10nAppDialog) {
	"use strict";

	var $dialog = $('#connectpx_booking-invoice-view-dialog'),
		invoiceData;

	Dialog.showDialog = function( invoice_id, callback ) {
        Dialog.loadInvoice( invoice_id, callback );
	};

	Dialog.loadInvoice = function( invoice_id, callback ) {
        return $.ajax({
            url: ConnectpxBookingL10nGlobal.ajax_url,
            type: 'GET',
            data: {
            	action: "connectpx_booking_render_invoice_view",
            	csrf_token: ConnectpxBookingL10nGlobal.csrf_token,
            	id: invoice_id,
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                	invoiceData = response.data;

                	$dialog.html( invoiceData.html );

                	var $modal = $('.connectpx_booking-modal', $dialog),
                		$btn_view_appointment = $('a.view-appointment', $dialog),
                		$btn_update_status = $('.invoice-update-status', $dialog),
                		$btn_update_status_toggle = $('.invoice-update-status-toggle', $dialog),
                		$btn_send_notification = $('.btn-send-notification', $dialog),
                		$btn_update_paid = $('.btn-update-paid', $dialog),
                		$btn_update_invoice = $('.btn-update-invoice', $dialog);

                	$btn_view_appointment.click(function (e) {
			            e.preventDefault();
			            $modal.connectpx_bookingModal('hide');
			            ConnectpxBookingAppointmentDialog.showDialog(
			                $(this).attr('data-id'),
			                function (event) {
			                    Dialog.loadInvoice( invoiceData.invoice_id, callback );
			                }
			            )
			        });

                	/**
				     * On filters change.
				     */
				    $('.connectpx_booking-js-select')
				        .select2({
				            width: '100%',
				            theme: 'bootstrap4',
				            dropdownParent: '#connectpx_booking_tbs',
				            allowClear: true,
				            placeholder: '',
				            language: {
				                noResults: function() { return ConnectpxBookingL10n.no_result_found; }
				            },
				            matcher: function (params, data) {
				                const term = $.trim(params.term).toLowerCase();
				                if (term === '' || data.text.toLowerCase().indexOf(term) !== -1) {
				                    return data;
				                }

				                let result = null;
				                const search = $(data.element).data('search');
				                search &&
				                search.find(function (text) {
				                    if (result === null && text.toLowerCase().indexOf(term) !== -1) {
				                        result = data;
				                    }
				                });

				                return result;
				            }
				        });

				    $('.connectpx_booking-js-select-ajax')
				        .select2({
				            width: '100%',
				            theme: 'bootstrap4',
				            dropdownParent: '#connectpx_booking_tbs',
				            allowClear: true,
				            placeholder: '',
				            language  : {
				                noResults: function () { return ConnectpxBookingL10n.no_result_found; },
				                searching: function () { return ConnectpxBookingL10n.searching; }
				            },
				            ajax: {
				                url: ConnectpxBookingL10nGlobal.ajax_url,
				                dataType: 'json',
				                delay: 250,
				                data: function (params) {
				                    params.page = params.page || 1;
				                    return {
				                        action: this.action === undefined ? $(this).data('ajax--action') : this.action,
				                        filter: params.term,
				                        page: params.page,
				                        csrf_token: ConnectpxBookingL10nGlobal.csrf_token
				                    };
				                }
				            },
				        });

                    $modal.connectpx_bookingModal('show');
                    $modal.on('hidden.bs.modal', function(){
                    	if( callback !== undefined && typeof callback === 'function' ) {
	                		callback( invoiceData );
	                	}
                    });

                    $btn_update_invoice.click(function(){
                    	$dialog.find('.modal-dialog').addClass('loading');
	                    $.ajax({
				            url: ConnectpxBookingL10nGlobal.ajax_url,
				            type: 'POST',
				            data: {
					        	action: "connectpx_booking_update_invoice",
					            csrf_token: ConnectpxBookingL10nGlobal.csrf_token,
					            id: invoiceData.invoice_id,
					        },
				            dataType: 'json',
				            success: function (response) {
				            	$dialog.find('.modal-dialog').removeClass('loading');
				                if (response.success) {
				                	$modal.connectpx_bookingModal('hide');
				                }
				            }
				        });
                    });

                    $btn_update_paid.click(function(){
                    	$dialog.find('.modal-dialog').addClass('loading');
	                    $.ajax({
				            url: ConnectpxBookingL10nGlobal.ajax_url,
				            type: 'POST',
				            data: {
					        	action: "connectpx_booking_update_invoice_paid",
					            csrf_token: ConnectpxBookingL10nGlobal.csrf_token,
					            id: invoiceData.invoice_id,
					        },
				            dataType: 'json',
				            success: function (response) {
				            	$dialog.find('.modal-dialog').removeClass('loading');
				                if (response.success) {
				                	$modal.connectpx_bookingModal('hide');
				                }
				            }
				        });
                    });

                    $btn_send_notification.click(function(){
                    	$dialog.find('.modal-dialog').addClass('loading');
	                    $.ajax({
				            url: ConnectpxBookingL10nGlobal.ajax_url,
				            type: 'POST',
				            data: {
					        	action: "connectpx_booking_send_invoice_notification",
					            csrf_token: ConnectpxBookingL10nGlobal.csrf_token,
					            id: invoiceData.invoice_id,
					        },
				            dataType: 'json',
				            success: function (response) {
				            	$dialog.find('.modal-dialog').removeClass('loading');
				                if (response.success) {
				                	if( callback !== undefined && typeof callback === 'function' ) {
				                		callback( invoiceData );
				                	}
				                }
				            }
				        });
                    });

                    $btn_update_status.click(function(e){
                    	e.preventDefault();
                    	$btn_update_status_toggle.find('span').remove();
                    	$btn_update_status_toggle.append( $(this).find('span').clone() );

                    	$dialog.find('.modal-dialog').addClass('loading');
	                    $.ajax({
				            url: ConnectpxBookingL10nGlobal.ajax_url,
				            type: 'POST',
				            data: {
					        	action: "connectpx_booking_update_invoice_status",
					            csrf_token: ConnectpxBookingL10nGlobal.csrf_token,
					            id: invoiceData.invoice_id,
					            invoice_status: $(this).attr('data-status'),
					        },
				            dataType: 'json',
				            success: function (response) {
				            	$dialog.find('.modal-dialog').removeClass('loading');
				                if (response.success) {
				                	$modal.connectpx_bookingModal('hide');
				                	if( callback !== undefined && typeof callback === 'function' ) {
				                		callback( invoiceData );
				                	}
				                	// if( callback !== undefined ) {
				                	// 	callback( invoiceData );
				                	// }
				                }
				            }
				        });
                    });

                }
            }
        });
	};

	return Dialog;
}({}, jQuery, moment, ConnectpxBookingL10nAppDialog);