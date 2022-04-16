<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use ConnectpxBooking\Lib;
use ConnectpxBooking\Lib\Utils\Common;
use ConnectpxBooking\Backend\Components;

/**
 * @var ConnectpxBooking\Lib\Entities\Staff[] $staff_members
 * @var array                       $staff_dropdown_data
 * @var array                       $services_dropdown_data
 * @var int                         $refresh_rate
 */
?>
<div id="connectpx_booking_tbs" class="wrap">
    <div class="form-row align-items-center mb-3">
        <h4 class="col m-0"><?php esc_html_e( 'Calendar', 'connectpx_booking' ) ?></h4>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="form-row justify-content-xl-end justify-content-center">
                <div class="col-sm-auto mb-2">
                    <ul id="connectpx_booking-js-status-filter">
                        <?php foreach ( Lib\Entities\Appointment::getStatusColors() as $status => $color ) : ?>
                            <li>
                                <span class="color-code" style="background: <?php echo $color; ?>"></span>
                                <span><?php echo Lib\Entities\Appointment::statusToString( $status ); ?></span>
                            </li>
                        <?php endforeach ?>
                    </ul>
                </div>
                <div class="col-sm-auto mb-2">
                    <ul id="connectpx_booking-js-services-filter"
                        data-icon-class="far fa-dot-circle"
                        data-align="right"
                        data-txt-select-all="<?php esc_attr_e( 'All services', 'connectpx_booking' ) ?>"
                        data-txt-all-selected="<?php esc_attr_e( 'All services', 'connectpx_booking' ) ?>"
                        data-txt-nothing-selected="<?php esc_attr_e( 'No service selected', 'connectpx_booking' ) ?>"
                    >
                        <?php foreach ( $services_dropdown_data as $service ) : ?>
                            <li data-value="<?php echo esc_attr( $service['id'] ) ?>">
                                <?php echo esc_html( $service['title'] ) ?>
                            </li>
                        <?php endforeach ?>
                    </ul>
                </div>
                <div class="col-sm-auto mb-2 text-center">
                    <div class="btn-group">
                        <button type="button" class="btn <?php echo esc_attr( $refresh_rate > 0 ? 'btn-success' : 'btn-default' ) ?>" id="connectpx_booking-calendar-refresh"><i class="fas fa-sync-alt"></i></button>
                        <button type="button" class="btn <?php echo esc_attr( $refresh_rate > 0 ? 'btn-success' : 'btn-default' ) ?> dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></button>
                        <div class="dropdown-menu pb-0 dropdown-menu-right">
                            <h6 class="dropdown-header"><?php esc_html_e( 'Auto-refresh Calendar', 'connectpx_booking' ) ?></h6>
                            <div class="dropdown-divider"></div>
                            <?php Components\Controls\Inputs::renderRadioGroup( null, null,
                                array(
                                    '60' => array( 'title' => __( 'Every 1 minute', 'connectpx_booking' ) ),
                                    '300' => array( 'title' => __( 'Every 5 minutes', 'connectpx_booking' ) ),
                                    '900' => array( 'title' => __( 'Every 15 minutes', 'connectpx_booking' ) ),
                                    '0' => array( 'title' => __( 'Disable', 'connectpx_booking' ) ),
                                ),
                                $refresh_rate,
                                array( 'name' => 'connectpx_booking_calendar_refresh_rate', 'parent-class' => 'mx-3 my-2 w-100' ) ) ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-3 position-relative">
                <div class="connectpx_booking-ec-loading" style="display: none">
                    <div class="connectpx_booking-ec-loading-icon"></div>
                </div>
                <div class="connectpx_booking-js-calendar"></div>
                <?php Components\Dialogs\Appointment\Edit\Dialog::render() ?>
            </div>
        </div>
    </div>

    <?php //Components\Dialogs\Appointment\Delete\Dialog::render() ?>
    <?php //Components\Dialogs\Queue\Dialog::render() ?>
</div>