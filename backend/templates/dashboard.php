<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use ConnectpxBooking\Backend\Components;
use ConnectpxBooking\Backend\Components\Dashboard;
use ConnectpxBooking\Backend\Modules\Dashboard\Proxy;
use ConnectpxBooking\Lib\Utils\Common;
use ConnectpxBooking\Lib\Utils\DateTime;
?>
<div id="connectpx_booking_tbs" class="wrap">
    <div class="form-row align-items-center">
        <h4 class="col m-0"><?php esc_html_e( 'Dashboard', 'connectpx_booking' ) ?></h4>
    </div>
    <div class="row my-3">
        <div class="col-md-3 col-sm-6">
            <button type="button" class="btn btn-block btn-default text-left text-truncate" id="connectpx_booking-filter-date" data-date="<?php printf( '%s - %s', date( 'Y-m-d', strtotime( '-7 days' ) ), date( 'Y-m-d' ) ) ?>">
                <i class="far fa-calendar-alt mr-1"></i>
                <span>
                    <?php echo DateTime::formatDate( '-7 days' ) ?> - <?php echo DateTime::formatDate( 'today' ) ?>
                </span>
            </button>
        </div>
        <div class="col-md-9 col-sm-6">
            <h6 class="mt-2 text-muted">
                <?php esc_html_e( 'See the number of appointments and total revenue for the selected period', 'connectpx_booking' ) ?>
            </h6>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <?php Dashboard\Appointments\Widget::renderChart() ?>
        </div>
    </div>
</div>