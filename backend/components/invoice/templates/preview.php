<?php 
use ConnectpxBooking\Lib;
use ConnectpxBooking\Lib\Utils;
?>
<style type="text/css">
    .preview-table {
        font-size: 11px;
    }
    #connectpx_booking_tbs table#invoice-appointments tr td {
        padding: 2px;
    }
</style>
<div id="connectpx_booking_tbs" class="wrap">
    <div class="form-row align-items-center mb-3">
        <h4 class="col m-0"><?php esc_html_e( sprintf('Invoice #%d', $invoice->getId()), 'connectpx_booking' ) ?></h4>
    </div>
    <br>
    <?php echo Utils\Session::falsh_messages(); ?>

    <div id="invoice-peview">
        
    </div>
</div>