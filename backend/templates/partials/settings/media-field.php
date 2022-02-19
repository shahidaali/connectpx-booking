<?php 
use ConnectpxBooking\Lib\Utils;
?>
<?php $uploaded_image = Utils\Common::getOptionMediaSource($field_key); ?>
<div class="connectpx_booking_upload_media"  data-id="<?php echo Utils\Common::getOption($field_key, 0); ?>">
	<div class='image-preview-wrapper'>
		<img class='image-preview' src='<?php echo $uploaded_image; ?>' width='100' height='100' style="max-height: 100px; width: 100px; display: <?php echo $uploaded_image ? 'inline-block' : 'none' ?>">
		<i class="image-preview-icon fas fa-fw fa-4x fa-camera mt-2 text-white w-100" style="display: <?php echo !$uploaded_image ? 'inline-block' : 'none' ?>"></i>
	</div>
	<input class="upload_media_button" type="button" class="button" value="<?php _e( 'Upload' ); ?>" />
	<input class="upload_media_value" type="hidden" name="connectpx_booking[company_logo_attachment_id]" value="<?php echo Utils\Common::getOption($field_key, 0); ?>" size="50">
</div>
						