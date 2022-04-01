var ConnectpxBookingInvoiceDialog = function(Dialog, $, moment, ConnectpxBookingL10nAppDialog) {
	"use strict";

	var $dialog = $('#connectpx_booking-invoice-dialog'),
		invoiceData;

	Dialog.showCreateInvoiceDialog = function( callback ) {
        return $.ajax({
            url: ConnectpxBookingL10nGlobal.ajax_url,
            type: 'GET',
            data: {
            	action: "connectpx_booking_render_create_invoices",
            	csrf_token: ConnectpxBookingL10nGlobal.csrf_token,
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                	invoiceData = response.data;

                	$dialog.html( invoiceData.html );

                	var $modal = $('.connectpx_booking-modal', $dialog),
                		$customer = $('#connectpx_booking-invoice-customer', $dialog),
                		$period = $('#connectpx_booking-invoice-period', $dialog),
                		$btn_update = $('.btn-update-invoices', $dialog);

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
				                url: ajaxurl,
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

                    $btn_update.click(function(){
                    	$modal.find('.modal-dialog').addClass('loading');
	                    $.ajax({
				            url: ConnectpxBookingL10nGlobal.ajax_url,
				            type: 'POST',
				            data: {
					        	action: "connectpx_booking_update_invoices",
					            csrf_token: ConnectpxBookingL10nGlobal.csrf_token,
					            customer: $customer.val(),
					            period: $period.val(),
					        },
				            dataType: 'json',
				            success: function (response) {
				            	$modal.find('.modal-dialog').removeClass('loading');
				                if (response.success) {
				                	$modal.connectpx_bookingModal('hide');
				                	if( callback !== undefined && typeof callback === 'function' ) {
				                		callback( invoiceData );
				                	}
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