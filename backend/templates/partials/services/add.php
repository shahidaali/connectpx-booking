<?php 
use ConnectpxBooking\Lib;
use ConnectpxBooking\Lib\Utils\Form;

$title = static::isEdit() ? $service->getTitle() : '';
$description = static::isEdit() ? $service->getDescription() : '';
$enabled = static::isEdit() ? $service->getEnabled() : '';
$sub_services = static::isEdit() ? json_decode($service->getSubServices(), true) : [];
?>
<form action="" method="post">
	<div class="os-tabs">
		<ul>
			<li class="active"><a href="#tab-details"><?php _e('Service Details', 'connectpx_booking'); ?></a></li>
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
						<h2><?php _e('Service Details', 'connectpx_booking'); ?></h2>
					</th>
				</tr>
				<tr>
					<th scope="row"><?php _e('Title', 'connectpx_booking'); ?></th>
					<td>
					<input name="title" type="text"  value="<?php echo $title; ?>" size="50"></td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Description', 'connectpx_booking'); ?></th>
					<td><textarea name="description" rows="5" cols="52"><?php echo $description; ?></textarea></td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Status', 'connectpx_booking') ?></th>
					<td>
						<select name="enabled">
							<?php 
							echo Form::selectOptions([
								'yes' => __('Enabled', 'connectpx_booking'), 
								'no' => __('Disabled', 'connectpx_booking')
							], 
							$enabled); 
							?>
						</select>
					</td>
				</tr>
			</tbody>
		</table>
	</div>

	<?php self::renderTemplate( 'backend/templates/partials/services/fields', ['fields_data' => $sub_services] ); ?>

	
	<p class="submit">
		<?php if(static::isEdit()): ?>
    		<input type="hidden" name="id" value="<?php echo $service->getId(); ?>" />
		<?php endif; ?>
    	<input type="hidden" name="connectpx_save_service" value="1" />
    	<input type="submit" class="button-primary" value="<?php _e('Submit'); ?>"/>
	</p>
</form>
