<?php 
use ConnectpxBooking\Lib;
use ConnectpxBooking\Lib\Utils\Form;
?>
<style type="text/css">
	.form-table-fields th {
		padding: 5px 0px;
	}
	.form-table-fields td {
		padding: 5px 10px;
	}
</style>
<?php foreach (Lib\Utils\Common::getSubServices() as $key => $sub_service): ?>
	<div class="os-tab-content" id="tab-<?php echo $key; ?>">
		<table class="form-table form-table-fields" role="presentation">
			<tbody>
				<tr>
					<th colspan="2">
						<h2><?php echo $sub_service['title']; ?></h2>
					</th>
				</tr>
				<tr>
					<th scope="row"><?php _e('Enabled', 'connectpx_booking') ?></th>
					<td>
						<select name="sub_services[<?php echo $key; ?>][enabled]">
							<?php 
							echo Form::selectOptions([
								'yes' => __('Enabled', 'connectpx_booking'), 
								'no' => __('Disabled', 'connectpx_booking')
							], 
							Form::old(
								[$key, 'enabled'], 
								$fields_data,
								'yes'
							)); 
							?>
						</select>
					</td>
				</tr>
				<?php foreach (Lib\Utils\Common::getSubServicesFields() as $field): ?>
				<tr>
					<th scope="row"><?php echo $field['label']; ?></th>
					<td>
						<input name="sub_services[<?php echo $key; ?>][<?php echo $field['name']; ?>]" type="number"  value="<?php echo Form::old([$key, $field['name']], $fields_data, 0); ?>" size="50">
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php endforeach; ?>