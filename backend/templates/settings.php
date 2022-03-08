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
				<li><a href="#tab-company-detail"><?php _e('Company Detail', 'connectpx_booking'); ?></a></li>
				<li><a href="#tab-business-hours"><?php _e('Office Hours', 'connectpx_booking'); ?></a></li>
				<li><a href="#tab-google-maps"><?php _e('Google Maps', 'connectpx_booking'); ?></a></li>
				<li><a href="#tab-wocoomerce"><?php _e('WooCommerce', 'connectpx_booking'); ?></a></li>
			</ul>
		</div>
		<div class="os-tab-content active" id="tab-general">
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<td colspan="2">
							<div class="connectpx_booking_instructions">
				                <h2><?php echo __('General Settings', 'connectpx_booking'); ?></h2>
				            </div>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Slot Length', 'connectpx_booking'); ?></th>
						<td>
							<select name="connectpx_booking[slot_length]">
								<?php 
								echo Utils\Form::selectOptions(
									$slot_length_options, 
									Utils\Common::getOption('slot_length', 15)
								); 
								?>
							</select>
							<div class="connectpx-booking-field-info"><?php echo __('Select a time interval which will be used as a step for pickup time.', 'connectpx_booking') ?></div>
						</td>
					</tr>
					<!-- <tr>
						<th scope="row"><?php _e('Display available time slots in client\'s time zone', 'connectpx_booking'); ?></th>
						<td>
							<select name="connectpx_booking[use_client_time_zone]">
								<?php 
								echo Utils\Form::selectOptions([
									'yes' => __('Enabled', 'connectpx_booking'), 
									'no' => __('Disabled', 'connectpx_booking')
								], 
								Utils\Common::getOption('use_client_time_zone', 'no')); 
								?>
							</select>
							<div class="connectpx-booking-field-info"><?php echo __('The value is taken from client\'s browser.', 'connectpx_booking') ?></div>
						</td>
					</tr> -->
					<tr>
						<th scope="row"><?php _e('Minimum time requirement prior to booking', 'connectpx_booking'); ?></th>
						<td>
							<select name="connectpx_booking[min_time_prior_booking]">
								<?php 
								echo Utils\Form::selectOptions($min_time_requirements['min_time_prior_booking'], 
								Utils\Common::getOption('min_time_prior_booking', 'no')); 
								?>
							</select>
							<div class="connectpx-booking-field-info"><?php echo __('Set how late appointments can be booked (for example, require customers to book at least 1 hour before the appointment time).', 'connectpx_booking') ?></div>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Minimum time requirement prior to canceling.', 'connectpx_booking'); ?></th>
						<td>
							<select name="connectpx_booking[min_time_prior_cancel]">
								<?php 
								echo Utils\Form::selectOptions($min_time_requirements['min_time_prior_cancel'], 
								Utils\Common::getOption('min_time_prior_cancel', 'no')); 
								?>
							</select>
							<div class="connectpx-booking-field-info"><?php echo __('Set how late appointments can be cancelled (for example, require customers to cancel at least 1 hour before the appointment time).', 'connectpx_booking') ?></div>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Number of days available for booking', 'connectpx_booking'); ?></th>
						<td>
							<input name="connectpx_booking[max_days_for_booking]" type="text"  value="<?php echo Utils\Common::getOption('max_days_for_booking', 365); ?>" size="20">
							<div class="connectpx-booking-field-info"><?php echo __('Set how far in the future the clients can book appointments.', 'connectpx_booking') ?></div>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Default appointment status', 'connectpx_booking'); ?></th>
						<td>
							<select name="connectpx_booking[appointment_default_status]">
								<?php 
								echo Utils\Form::selectOptions(
									$appointment_statuses, 
									Utils\Common::getOption('appointment_default_status', 'pending')
								); 
								?>
							</select>
							<div class="connectpx-booking-field-info"><?php echo __('Select status for newly booked appointments.', 'connectpx_booking') ?></div>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Address Format', 'connectpx_booking'); ?></th>
						<td><textarea name="connectpx_booking[address_format]" rows="3" cols="52"><?php echo Utils\Common::getOption('address_format', ''); ?></textarea></td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Calendar Format', 'connectpx_booking'); ?></th>
						<td><textarea name="connectpx_booking[calendar_format]" rows="3" cols="52"><?php echo Utils\Common::getOption('calendar_format', ''); ?></textarea></td>
					</tr>
				</tbody>
			</table>
		</div>

		<div class="os-tab-content" id="tab-company-detail">
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<td colspan="2">
							<div class="connectpx_booking_instructions">
				                <h2><?php echo __('Company Details', 'connectpx_booking'); ?></h2>
				            </div>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Company Logo', 'connectpx_booking'); ?></th>
						<td>
							<?php self::renderTemplate( 'backend/templates/partials/settings/media-field', ['field_key' => 'company_logo_attachment_id'] ); ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Company Name', 'connectpx_booking'); ?></th>
						<td><input name="connectpx_booking[company_name]" type="text"  value="<?php echo Utils\Common::getOption('company_name', ''); ?>" size="50"></td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Address', 'connectpx_booking'); ?></th>
						<td><textarea name="connectpx_booking[company_address]" rows="5" cols="52"><?php echo Utils\Common::getOption('company_address', ''); ?></textarea></td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Phone', 'connectpx_booking'); ?></th>
						<td><input name="connectpx_booking[company_phone]" type="text"  value="<?php echo Utils\Common::getOption('company_phone', ''); ?>" size="50"></td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Website', 'connectpx_booking'); ?></th>
						<td><input name="connectpx_booking[company_website]" type="text"  value="<?php echo Utils\Common::getOption('company_website', ''); ?>" size="50"></td>
					</tr>
				</tbody>
			</table>
		</div>

		<div class="os-tab-content" id="tab-business-hours">
			<?php $business_hours_data = Utils\Common::getOption('business_hours', []); ?>
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<td colspan="2">
							<div class="connectpx_booking_instructions">
				                <h2><?php echo __('Business Hours', 'connectpx_booking'); ?></h2>
				            </div>
						</td>
					</tr>
					<?php foreach($business_hours['days'] as $day_index => $day_name): ?>
						<?php 
						$business_hour_from = $business_hours_data[$day_index]['from'] ?? ''; 
						$business_hour_to = $business_hours_data[$day_index]['to'] ?? ''; 
						?>
						<tr>
							<th scope="row"><?php echo $day_name; ?></th>
							<td>
								<select name="connectpx_booking[business_hours][<?php echo $day_index; ?>][from]">
									<?php 
									echo Utils\Form::selectOptions($business_hours['from'], 
									Utils\Form::old(
										['connectpx_booking', 'business_hours', $day_index, 'from'], 
										['connectpx_booking' => [ 'business_hours' => $business_hours_data, ]],
										'yes'
									)); 
									?>
								</select>
								<label> <?php echo __('to', 'connectpx_booking') ?> </label>
								<select name="connectpx_booking[business_hours][<?php echo $day_index; ?>][to]">
									<?php 
									echo Utils\Form::selectOptions($business_hours['to'], 
									Utils\Form::old(
										['connectpx_booking', 'business_hours', $day_index, 'to'], 
										['connectpx_booking' => [ 'business_hours' => $business_hours_data, ]],
										'yes'
									)); 
									?>
								</select>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	
		<div class="os-tab-content" id="tab-google-maps">
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

		<div class="os-tab-content" id="tab-wocoomerce">
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<td colspan="2">
							<div class="connectpx_booking_instructions">
				                <h2>Instructions</h2>
				                <p>You need to install and activate WooCommerce plugin before using the options below.</p>
				                <p>Once the plugin is activated do the following steps:</p>
				                <ol>
				                    <li>Create a product in WooCommerce that can be placed in cart.</li>
				                    <li>In the form below enable WooCommerce option.</li>
				                    <li>Select the product that you created at step 1 in the drop down list of products.</li>
				                    <li>If needed, edit item data which will be displayed in the cart. Besides cart item data Bookly passes address and account fields into WooCommerce if you collect them in your booking form.</li>
				                </ol>
				                <p>Note that once you have enabled WooCommerce option in Bookly the built-in payment methods will no longer work. All your customers will be redirected to WooCommerce cart instead of standard payment step.</p>
				            </div>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('WooCommerce Product', 'connectpx_booking'); ?></th>
						<td>
							<select name="connectpx_booking[wc_product_id]">
								<?php 
								echo Utils\Form::selectOptions($wc_products, 
								Utils\Common::getOption('wc_product_id', 0)); 
								?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Cart item title', 'connectpx_booking'); ?></th>
						<td><input name="connectpx_booking[wc_cart_item_title]" type="text"  value="<?php echo Utils\Common::getOption('wc_cart_item_title', 'Booking'); ?>" size="50"></td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Cart item data', 'connectpx_booking'); ?></th>
						<td><textarea name="connectpx_booking[wc_cart_item_data]" rows="5" cols="52"><?php echo Utils\Common::getOption('wc_cart_item_data', ''); ?></textarea></td>
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
