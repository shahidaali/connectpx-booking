<?php 
use ConnectpxBooking\Lib;
use ConnectpxBooking\Lib\Utils\Form;

$fields = static::isEdit() ? $customer->getFields() : [];
$is_contract_customer = static::isEdit() ? $customer->isContractCustomer() : 0;
$sub_services = static::isEdit() ? json_decode($customer->getSubServices(), true) : [];

if(static::isEdit() && $is_contract_customer) {
	$wp_users = $wp_users;
}
else if(static::isEdit() && !$is_contract_customer) {
	$wp_users = array('' => __('Private Customer', 'connectpx_booking'));
}
else {
	$wp_users = array('create_new' => __('Create New', 'connectpx_booking'));
}
?>
<form action="" method="post" autocomplete="off">
	<div class="os-tabs">
		<ul>
			<li class="active"><a href="#tab-details"><?php _e('Customer Details', 'connectpx_booking'); ?></a></li>
			<?php foreach (Lib\Utils\Common::getSubServices() as $key => $sub_service): ?>
				<li><a href="#tab-<?php echo $key; ?>"><?php echo $sub_service['title']; ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
	<div class="os-tab-content active" id="tab-details">
		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th colspan="2">
						<h2><?php _e('Customer Details', 'connectpx_booking'); ?></h2>
					</th>
				</tr>
				<tr>
					<th scope="row"><?php _e('First Name', 'connectpx_booking'); ?></th>
					<td><input name="first_name" type="text"  value="<?php echo Form::old('first_name', $fields, ''); ?>" size="50"></td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Last Name', 'connectpx_booking'); ?></th>
					<td><input name="last_name" type="text"  value="<?php echo Form::old('last_name', $fields, ''); ?>" size="50"></td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Phone', 'connectpx_booking'); ?></th>
					<td><input name="phone" type="text"  value="<?php echo Form::old('phone', $fields, ''); ?>" size="50"></td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Email', 'connectpx_booking'); ?></th>
					<td><input name="email" type="text"  value="<?php echo Form::old('email', $fields, ''); ?>" size="50"></td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Country', 'connectpx_booking'); ?></th>
					<td><input name="country" type="text"  value="US" size="50" readonly></td>
				</tr>
				<tr>
					<th scope="row"><?php _e('State', 'connectpx_booking'); ?></th>
					<td><input name="state" type="text"  value="Michigan" size="50" readonly></td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Postcode', 'connectpx_booking'); ?></th>
					<td><input name="postcode" type="text"  value="<?php echo Form::old('postcode', $fields, ''); ?>" size="50"></td>
				</tr>
				<tr>
					<th scope="row"><?php _e('City', 'connectpx_booking'); ?></th>
					<td><input name="city" type="text"  value="<?php echo Form::old('city', $fields, ''); ?>" size="50"></td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Street Address', 'connectpx_booking'); ?></th>
					<td><input name="street" type="text"  value="<?php echo Form::old('street', $fields, ''); ?>" size="50"></td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Street Number', 'connectpx_booking'); ?></th>
					<td><input name="street_number" type="text"  value="<?php echo Form::old('street_number', $fields, ''); ?>" size="50"></td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Additional Address', 'connectpx_booking'); ?></th>
					<td><input name="additional_address" type="text"  value="<?php echo Form::old('additional_address', $fields, ''); ?>" size="50"></td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Notes', 'connectpx_booking'); ?></th>
					<td><textarea name="notes" rows="5" cols="52"><?php echo Form::old('notes', $fields, ''); ?></textarea></td>
				</tr>
				<tr>
					<th colspan="2">
						<h2><?php _e('Customer Account', 'connectpx_booking'); ?></h2>
					</th>
				</tr>
				<tr>
					<th scope="row"><?php _e('Customer Account', 'connectpx_booking'); ?></th>
					<td>
						<select name="wp_user" id="wp_user_account" <?php if($is_contract_customer): echo 'disabled'; endif; ?>>
							<?php echo Lib\Utils\Form::selectOptions($wp_users, Form::old('wp_user_id', $fields, 0)); ?>
						</select>
					</td>
					<?php if(static::isAdd() || $is_contract_customer): ?>
					<tr>
						<th scope="row"><?php _e('Account Password', 'connectpx_booking'); ?></th>
						<td>
							<input type="password" name="password" style="position: absolute; width: 0px; height: 0px; z-index: -999; opacity: 0;">
							<input name="account_password" type="password"  value="" size="50" autocomplete="off">
							<?php if(static::isEdit()): ?>
								<br><p><em><?php echo __('Leave empty to use existing password.', 'connectpx_booking') ?></em></p>
							<?php endif; ?>
						</td>
					</tr>
					<?php endif; ?>
				</tr>
			</tbody>
		</table>
	</div>

	<?php self::renderTemplate( 'backend/templates/partials/services/fields', ['fields_data' => $sub_services] ); ?>
	
	<p class="submit">
		<?php if(static::isEdit()): ?>
    		<input type="hidden" name="id" value="<?php echo $customer->getId(); ?>" />
		<?php endif; ?>
    	<input type="hidden" name="connectpx_save_customer" value="1" />
    	<input type="submit" class="button-primary" value="<?php _e('Submit'); ?>"/>
	</p>
</form>