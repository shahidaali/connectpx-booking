<?php 
use ConnectpxBooking\Lib;
use ConnectpxBooking\Lib\Utils;
use ConnectpxBooking\Lib\Entities\Invoice;
use ConnectpxBooking\Lib\Utils\Common;
use ConnectpxBooking\Lib\Utils\DateTime;
use ConnectpxBooking\Lib\Config;
use ConnectpxBooking\Backend\Components\Dialogs;
use ConnectpxBooking\Backend\Components\Controls;
?>
<div id="connectpx_booking_tbs" class="wrap">
    <div class="form-row align-items-center mb-3">
        <h4 class="col m-0"><?php esc_html_e( 'Notifications', 'connectpx_booking' ) ?></h4>
    </div>

    <?php echo Utils\Session::falsh_messages(); ?>

    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs connectpx_booking-js-notifications-tabs flex-column flex-lg-row connectpx_booking-nav-tabs-md" role="tablist">
                <li class="nav-item text-center">
                    <a class="nav-link<?php if ( $tab === 'notifications' ) : ?> active<?php endif ?>" href="#" data-toggle="connectpx_booking-tab" data-tab="notifications"><?php esc_html_e( 'Notifications', 'connectpx_booking' ) ?></a>
                </li>
                <li class="nav-item text-center">
                    <a class="nav-link<?php if ( $tab === 'logs' ) : ?> active<?php endif ?>" href="#" data-toggle="connectpx_booking-tab" data-tab="logs"><?php esc_html_e( 'Logs', 'connectpx_booking' ) ?></a>
                </li>
            </ul>
        </div>
        <div class="card-body connectpx_booking-js-notifications-wrap">
        </div>
        <div class="card-footer bg-transparent text-right connectpx_booking-js-notifications-footer" style="display: none;">
            <?php Controls\Buttons::renderSubmit( null, 'connectpx_booking-js-save', __( 'Save', 'connectpx_booking' ) ) ?>
        </div>
    </div>
    <?php Dialogs\Notifications\Dialog::render() ?>
</div>
