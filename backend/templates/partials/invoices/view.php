<?php 
use ConnectpxBooking\Lib;
use ConnectpxBooking\Lib\Utils;
?>
<style type="text/css">
	.preview-table {
		font-size: 11px;
	}
</style>
<div class="wrap">
    <div class="form-row align-items-center mb-3">
        <h2 class="col m-0"><?php esc_html_e( sprintf('Invoice #%d', $invoice->getId()), 'connectpx_booking' ) ?></h2>
    </div>

    <?php echo Utils\Session::falsh_messages(); ?>

    <div class="preview-table">
		<?php echo $preview; ?>
	</div>
</div>