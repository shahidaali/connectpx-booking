<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="connectpx_booking-js-dashboard-appointments">
    <div style="text-align: right">
    <span>
        <?php esc_html_e( 'Period', 'connectpx_booking' ) ?>
    </span>
        <span>
        <select id="connectpx_booking-filter-date">
            <option value="<?php echo date( 'Y-m-d', strtotime( '-7 days' ) ) ?> - <?php echo date( 'Y-m-d' ) ?>"><?php esc_html_e( 'Last 7 days', 'connectpx_booking' ) ?></option>
                <option value="<?php echo date( 'Y-m-d', strtotime( '-30 days' ) ) ?> - <?php echo date( 'Y-m-d' ) ?>"><?php esc_html_e( 'Last 30 days', 'connectpx_booking' ) ?></option>
                <option value="<?php echo date( 'Y-m-d', strtotime( 'first day of this month' ) ) ?> - <?php echo date( 'Y-m-d', strtotime( 'last day of this month' ) ) ?>"><?php esc_html_e( 'This month', 'connectpx_booking' ) ?></option>
                <option value="<?php echo date( 'Y-m-d', strtotime( 'first day of previous month' ) ) ?> - <?php echo date( 'Y-m-d', strtotime( 'last day of previous month' ) ) ?>"><?php esc_html_e( 'Last month', 'connectpx_booking' ) ?></option>
        </select>
    </span>
    </div>
    <table style="width: 100%">
        <tr>
            <td><?php esc_html_e( 'Approved appointments', 'connectpx_booking' ) ?></td>
            <td style="text-align: right"><a href="#" class="connectpx_booking-js-approved connectpx_booking-js-href-approved"></a></td>
        </tr>
        <tr>
            <td><?php esc_html_e( 'Pending appointments', 'connectpx_booking' ) ?></td>
            <td style="text-align: right"><a href="#" class="connectpx_booking-js-pending connectpx_booking-js-href-pending"></a></td>
        </tr>
        <tr>
            <td><?php esc_html_e( 'Total appointments', 'connectpx_booking' ) ?></td>
            <td style="text-align: right"><a href="#" class="connectpx_booking-js-total connectpx_booking-js-href-total"></a></td>
        </tr>
        <tr>
            <td><?php esc_html_e( 'Revenue', 'connectpx_booking' ) ?></td>
            <td style="text-align: right"><a href="#" class="connectpx_booking-js-revenue connectpx_booking-js-href-revenue"></a></td>
        </tr>
    </table>
    <hr>
    <canvas id="canvas" style="width:100%;height: 200px"></canvas>
</div>