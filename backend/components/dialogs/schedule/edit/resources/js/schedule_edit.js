var ConnectpxBookingScheduleDialog = function(Dialog, $, moment, ConnectpxBookingL10nAppDialog) {
	"use strict";

	var $dialog = $('#connectpx_booking-schedule-dialog'),
		scheduleData;

	Dialog.showDialog = function( schedule_id, callback, tab ) {
		Dialog.loadSchedule({
			id: schedule_id,
			tab: tab !== undefined && tab ? tab : 'schedule',
		}, callback);
	};

	Dialog.loadSchedule = function (options, callback) {
        let data = $.extend({}, options, {
        	action: "connectpx_booking_render_edit_schedule",
            csrf_token: ConnectpxBookingL10nGlobal.csrf_token,
        });

        return $.ajax({
            url: ConnectpxBookingL10nGlobal.ajax_url,
            type: 'GET',
            data: data,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                	scheduleData = response.data;

                	$dialog.html( scheduleData.html );

                	var $modal = $('.connectpx_booking-modal', $dialog),
                		$customer_name = $('#connectpx_booking_customer-name', $dialog),
                		$pickup_info = $('#connectpx_booking-schedule-pickup-info', $dialog),
                		$destination_info = $('#connectpx_booking-schedule-destination-info', $dialog),
                		$map = $('#connectpx_booking-schedule-map', $dialog),
                		$service_info = $('#connectpx_booking-service-info', $dialog),
                		$schedule_info = $('#connectpx_booking-schedule-info', $dialog),
                		$btn_update_status_toggle = $('.schedule-update-status-toggle', $dialog),
                		$btn_update_status = $('.schedule-update-status', $dialog),
                		$btn_save = $('.btn-save-schedule', $dialog);
                
                	$pickup_info.html( scheduleData.pickup_info );
                	$destination_info.html( scheduleData.destination_info );
                	$service_info.html( scheduleData.service_info );
                	$schedule_info.html( scheduleData.schedule_info );
                	if( scheduleData.map_link ) {
                		$map.html( scheduleData.map_link.iframe );
                	}
                	$customer_name.text( scheduleData.customer_data.name );

                    $modal.connectpx_bookingModal('show');
                    $modal.on('hidden.bs.modal', function(){
                    	if( callback !== undefined && typeof callback === 'function' ) {
	                		callback(scheduleData);
	                	}
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
					        	action: "connectpx_booking_update_schedule_status",
					            csrf_token: ConnectpxBookingL10nGlobal.csrf_token,
					            id: data.id,
					            schedule_status: $(this).attr('data-status'),
					        },
				            dataType: 'json',
				            success: function (response) {
				            	$dialog.find('.modal-dialog').removeClass('loading');
				                if (response.success) {
				                	$modal.connectpx_bookingModal('hide');
				                	if( callback !== undefined && typeof callback === 'function' ) {
				                		callback( scheduleData );
				                	}
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