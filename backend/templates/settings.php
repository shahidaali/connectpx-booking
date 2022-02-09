<?php 
use ConnectpxBooking\Lib\Utils;
?>
<div class="wrap">
	<h1><?php _e('Booking Settings', 'connectpx_booking'); ?></h1>

	<?php if( !empty($messages) ): ?>
		<div id="setting-error-settings_updated" class="notice notice-<?php echo $messages['status']; ?> settings-error is-dismissible"> 
			<p><strong><?php echo $messages['message']; ?></strong></p>
			<button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e('Dismiss this notice.') ?></span></button>
		</div>
	<?php endif; ?>

	<form action="" method="post">
		<div class="os-tabs">
			<ul>
				<li class="active"><a href="#tab-general"><?php _e('General', 'connectpx_booking'); ?></a></li>
				<li><a href="#tab-step-google-maps"><?php _e('Google Maps', 'connectpx_booking'); ?></a></li>
			</ul>
		</div>
		<div class="os-tab-content active" id="tab-general">
			<table class="form-table" role="presentation">
				<tbody>

				</tbody>
			</table>
		</div>
	
		<div class="os-tab-content" id="tab-step-google-maps">
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<td colspan="2">
							<div class="connectpx_booking_instructions">
				                <h2>Instructions</h2>
				                <p>Follow these steps to get an API key:</p>
				                <ol>
				                    <li>Go to the <a href="https://console.developers.google.com/flows/enableapi?apiid=places_backend&amp;reusekey=true" target="_blank">Google API Console</a>.</li>
				                    <li>Create or select a project. Click <b>Continue</b> to enable the API.</li>
				                    <li>On the <b>Credentials</b> page, get an <b>API key</b> (and set the API key restrictions). Note: If you have an existing unrestricted API key, or a key with server restrictions, you may use that key.</li>
				                    <li>Click <b>Library</b> on the left sidebar menu. Select Google Maps JavaScript API and make sure it's enabled.</li>
				                    <li>Use your <b>API key</b> in the form below.</li>
				                </ol>
				            </div>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Google API Key', 'connectpx_booking'); ?></th>
						<td>
						<input name="connectpx_booking[google_api_key]" type="text"  value="<?php echo Utils\Common::getOption('google_api_key', ''); ?>" size="50"></td>
					</tr>
				</tbody>
			</table>
		</div>
		<p class="submit">
	    	<input type="hidden" name="connectpx_booking_options" value="1" />
	    	<input type="submit" class="button-primary" value="<?php _e('Submit'); ?>"/>
		</p>
	</form>
</div>
