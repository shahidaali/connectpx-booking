<?php 
use ConnectpxBooking\Lib;
use ConnectpxBooking\Lib\Utils;
?>
<div class="wrap">
	<h1><?php _e('Services'); ?></h1>
	<a href="<?php echo self::escAdminUrl( self::pageSlug(), ['tab' => 'add'] ); ?>" class="page-title-action"><?php _e('Add New') ?></a>

	<?php echo Utils\Session::falsh_messages(); ?>

	<?php self::renderTemplate( $tpl, $params ); ?>
</div>
