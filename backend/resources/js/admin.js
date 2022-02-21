(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-specific JavaScript source
	 * should reside in this file.
	 *
	 * Note that this assume you're going to use jQuery, so it prepares
	 * the $ function reference to be used within the scope of this
	 * function.
	 *
	 * From here, you're able to define handlers for when the DOM is
	 * ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * Or when the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and so on.
	 *
	 * Remember that ideally, we should not attach any more than a single DOM-ready or window-load handler
	 * for any particular page. Though other scripts in WordPress core, other plugins, and other themes may
	 * be doing this, we should try to minimize doing that in our own work.
	 */


	 $(document).on('click', '.os-tabs a', function(e){
	 	e.preventDefault();
	 	$('.os-tabs li').removeClass('active');
	 	$(this).closest('li').addClass('active');

	 	$('.os-tab-content').removeClass('active');
	 	$($(this).attr('href')).addClass('active');
	 });

	 // setTimeout(function(){
	 // 	tinymce.init({ selector: '#' + editor_id});
	 // }, 500)

	jQuery( document ).ready( function( $ ) {
		if( jQuery('.connectpx_booking_upload_media').length > 0 ) {
			// Uploading files
			var file_frame;
			var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id

			jQuery('.connectpx_booking_upload_media .upload_media_button').on('click', function( event ){

				event.preventDefault();

				var $wrapper = $('.connectpx_booking_upload_media'),
				 	$preview = $wrapper.find('.image-preview'),
				 	$icon = $wrapper.find('.image-preview-icon'),
				 	$value = $wrapper.find('.upload_media_value'),
					set_to_post_id = $wrapper.attr('data-id'),
					attachment;

				// If the media frame already exists, reopen it.
				if ( file_frame ) {
					// Set the post ID to what we want
					file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
					// Open frame
					file_frame.open();
					return;
				} else {
					// Set the wp.media post id so the uploader grabs the ID we want when initialised
					wp.media.model.settings.post.id = set_to_post_id;
				}

				// Create the media frame.
				file_frame = wp.media.frames.file_frame = wp.media({
					title: 'Select image to upload',
					button: {
						text: 'Use this image',
					},
					multiple: false	// Set to true to allow multiple files to be selected
				});

				// When an image is selected, run a callback.
				file_frame.on( 'select', function() {
					// We set multiple to false so only get one image from the uploader
					attachment = file_frame.state().get('selection').first().toJSON();

					// Do something with attachment.id and/or attachment.url here
					$preview.attr( 'src', attachment.url ).css( 'width', 'auto' ).show();
					$icon.hide();
					$value.val( attachment.id );

					// Restore the main post ID
					wp.media.model.settings.post.id = wp_media_post_id;
				});

					// Finally, open the modal
					file_frame.open();
			});

			// Restore the main ID when the add media button is pressed
			jQuery( 'a.add_media' ).on( 'click', function() {
				wp.media.model.settings.post.id = wp_media_post_id;
			});
		}
		
	});
})( jQuery );
