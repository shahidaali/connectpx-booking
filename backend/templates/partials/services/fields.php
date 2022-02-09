<?php 
use ConnectpxBooking\Lib;
use ConnectpxBooking\Lib\Utils\Form;
?>

<?php foreach (Lib\Utils\Common::getSubServices() as $key => $sub_service): ?>
	<div class="os-tab-content" id="tab-<?php echo $key; ?>">
		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th colspan="2">
						<h2><?php echo $sub_service['title']; ?></h2>
					</th>
				</tr>
				<?php foreach (Lib\Utils\Common::getSubServicesFields() as $field): ?>
				<tr>
					<th scope="row"><?php echo $field['label']; ?></th>
					<td>
					<input name="sub_services[<?php echo $key; ?>][<?php echo $field['name']; ?>]" type="number"  value="<?php echo Form::old(['sub_services', $key, $field['name']], ['sub_services' => $fields_data], 0); ?>" size="50"></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php endforeach; ?>