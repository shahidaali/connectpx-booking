<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use ConnectpxBooking\Lib\Entities\Appointment;
use ConnectpxBooking\Backend\Components\Controls\Inputs;
use ConnectpxBooking\Lib\Utils;

$statuses = Appointment::getStatuses();
$service_dropdown_data = Utils\Common::getServiceDataForDropDown();
?>
<div class="connectpx_booking-js-statuses-container border-left ml-4 pl-3">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="notification_status"><?php esc_html_e( 'Appointment status', 'connectpx_booking' ) ?></label>
                <select class="form-control custom-select" class="mt-2 ml-1" name="notification[settings][status]" id="notification_status">
                    <option value="any"><?php esc_html_e( 'Any', 'connectpx_booking' ) ?></option>
                    <?php foreach ( $statuses as $status ) : ?>
                        <option value="<?php echo esc_attr( $status ) ?>"><?php echo esc_html( Appointment::statusToString( $status ) ) ?></option>
                    <?php endforeach ?>
                </select>
                <small class="form-text text-muted"><?php esc_html_e( 'Select what status an appointment should have for the notification to be sent.', 'connectpx_booking' ) ?></small>
            </div>
        </div>
    </div>
</div>
<div class="connectpx_booking-js-services-container border-left ml-4 pl-3">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label><?php esc_html_e( 'Services', 'connectpx_booking' ) ?></label>
                <?php Inputs::renderRadio( __( 'Any', 'connectpx_booking' ), 'any', true, array( 'name' => 'notification[settings][services][any]' ) ) ?>
                <div class="d-flex">
                    <div class="align-self-center">
                        <?php Inputs::renderRadio( '', 'selected', null, array( 'name' => 'notification[settings][services][any]' ) ) ?>
                    </div>
                    <div class="col-auto pl-0">
                        <ul class="connectpx_booking-js-services"
                            data-icon-class="far fa-dot-circle"
                            data-txt-select-all="<?php esc_attr_e( 'All services', 'connectpx_booking' ) ?>"
                            data-txt-all-selected="<?php esc_attr_e( 'All services', 'connectpx_booking' ) ?>"
                            data-txt-nothing-selected="<?php esc_attr_e( 'No service selected', 'connectpx_booking' ) ?>"
                        >
                            <ul>
                                <?php foreach ( $service_dropdown_data as $service ) : ?>
                                    <li data-input-name="notification[settings][services][ids][]"
                                        data-value="<?php echo esc_attr( $service['id'] ) ?>"
                                    >
                                        <?php echo esc_html( $service['title'] ) ?>
                                    </li>
                                <?php endforeach ?>
                            </ul>
                        </ul>
                    </div>
                </div>
                <small class="form-text text-muted"><?php esc_html_e( 'Choose whether notification should be sent for specific services only or not.', 'connectpx_booking' ) ?></small>
            </div>
        </div>
    </div>
</div>

<div class="row connectpx_booking-js-offset connectpx_booking-js-offset-exists border-left ml-4 pl-3">
    <div class="col-md-12 pl-0">
        <label><?php esc_html_e( 'Send', 'connectpx_booking' ) ?></label>
    </div>
</div>

<div class="connectpx_booking-js-offset connectpx_booking-js-offset-bidirectional border-left ml-4 pl-3">
    <div class="row connectpx_booking-js-offsets connectpx_booking-js-relative connectpx_booking-js-full mb-2">
        <div class="col-md-12">
            <div class="form-group mb-0">
                <div class="d-flex flex-row">
                    <div class="align-self-center">
                        <?php Inputs::renderRadio( '', '1', true, array( 'name' => 'notification[settings][option]' ) ) ?>
                    </div>
                    <div>
                        <select class="form-control custom-select" name="notification[settings][offset_hours]">
                            <?php foreach ( array_merge( range( 1, 24 ), range( 48, 336, 24 ), array( 504, 672 ) ) as $hour ) : ?>
                                <option value="<?php echo esc_attr( $hour ) ?>"><?php echo esc_html( Utils\DateTime::secondsToInterval( $hour * HOUR_IN_SECONDS ) ) ?></option>
                            <?php endforeach ?>
                            <option value="43200">30 <?php esc_html_e( 'days', 'connectpx_booking' ) ?></option>
                        </select>
                    </div>

                    <div class="ml-2">
                        <select class="form-control custom-select" name="notification[settings][perform]">
                            <option value="before"><?php esc_html_e( 'before', 'connectpx_booking' ) ?></option>
                            <option value="after"><?php esc_html_e( 'after', 'connectpx_booking' ) ?></option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row connectpx_booking-js-offsets connectpx_booking-js-at-time connectpx_booking-js-full mb-3">
        <div class="col-md-12">
            <div class="form-group mb-0">
                <div class="d-flex">
                    <div class="align-self-center">
                        <?php Inputs::renderRadio( '', '2', true, array( 'name' => 'notification[settings][option]' ) ) ?>
                    </div>
                    <div>
                        <select class="form-control custom-select" name="notification[settings][offset_bidirectional_hours]">
                            <option value='-8760'>1 <?php esc_html_e( 'year', 'connectpx_booking' ) ?>&nbsp;<?php esc_html_e( 'before', 'connectpx_booking' ) ?></option>
                            <option value='-4380'><?php echo esc_html( sprintf( _n( '%d month', '%d months', 6, 'connectpx_booking' ), 6 ) ) ?>&nbsp;<?php esc_html_e( 'before', 'connectpx_booking' ) ?></option>
                            <option value='-2920'><?php echo esc_html( sprintf( _n( '%d month', '%d months', 4, 'connectpx_booking' ), 4 ) ) ?>&nbsp;<?php esc_html_e( 'before', 'connectpx_booking' ) ?></option>
                            <option value='-2190'><?php echo esc_html( sprintf( _n( '%d month', '%d months', 3, 'connectpx_booking' ), 3 ) ) ?>&nbsp;<?php esc_html_e( 'before', 'connectpx_booking' ) ?></option>
                            <option value='-1460'><?php echo esc_html( sprintf( _n( '%d month', '%d months', 2, 'connectpx_booking' ), 2 ) ) ?>&nbsp;<?php esc_html_e( 'before', 'connectpx_booking' ) ?></option>
                            <?php foreach ( array_merge( array( - 672, - 504 ), range( - 336, - 24, 24 ) ) as $hour ) : ?>
                                <option value="<?php echo esc_attr( $hour ) ?>"><?php echo esc_html( Utils\DateTime::secondsToInterval( abs( $hour ) * HOUR_IN_SECONDS ) ) ?>&nbsp;<?php esc_html_e( 'before', 'connectpx_booking' ) ?></option>
                            <?php endforeach ?>
                            <option value="0" selected><?php esc_html_e( 'on the same day', 'connectpx_booking' ) ?></option>
                            <?php foreach ( array_merge( range( 24, 336, 24 ), array( 504, 672 ) ) as $hour ) : ?>
                                <option value="<?php echo esc_attr( $hour ) ?>"><?php echo esc_html( Utils\DateTime::secondsToInterval( $hour * HOUR_IN_SECONDS ) ) ?>&nbsp;<?php esc_html_e( 'after', 'connectpx_booking' ) ?></option>
                            <?php endforeach ?>
                            <option value='1460'><?php echo esc_html( sprintf( _n( '%d month', '%d months', 2, 'connectpx_booking' ), 2 ) ) ?>&nbsp;<?php esc_html_e( 'after', 'connectpx_booking' ) ?></option>
                            <option value='2190'><?php echo esc_html( sprintf( _n( '%d month', '%d months', 3, 'connectpx_booking' ), 3 ) ) ?>&nbsp;<?php esc_html_e( 'after', 'connectpx_booking' ) ?></option>
                            <option value='2920'><?php echo esc_html( sprintf( _n( '%d month', '%d months', 4, 'connectpx_booking' ), 4 ) ) ?>&nbsp;<?php esc_html_e( 'after', 'connectpx_booking' ) ?></option>
                            <option value='4380'><?php echo esc_html( sprintf( _n( '%d month', '%d months', 6, 'connectpx_booking' ), 6 ) ) ?>&nbsp;<?php esc_html_e( 'after', 'connectpx_booking' ) ?></option>
                            <option value='8760'>1 <?php esc_html_e( 'year', 'connectpx_booking' ) ?>&nbsp;<?php esc_html_e( 'after', 'connectpx_booking' ) ?></option>
                        </select>
                    </div>
                    <div class="align-self-center mx-2">
                        <?php esc_html_e( 'at', 'connectpx_booking' ) ?>
                    </div>
                    <div>
                        <select class="form-control custom-select" name="notification[settings][at_hour]">
                            <?php foreach ( range( 0, 23 ) as $hour ) : ?>
                                <option value="<?php echo esc_attr( $hour ) ?>"><?php echo esc_html( Utils\DateTime::formatTime( $hour * HOUR_IN_SECONDS ) ) ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row connectpx_booking-js-offset connectpx_booking-js-offset-before mb-3 border-left ml-4">
    <div class="col-md-12">
        <div class="form-group">
            <div class="d-flex flex-row">
                <div class="align-self-center">
                    <?php Inputs::renderRadio( '', '3', true, array( 'name' => 'notification[settings][option]' ) ) ?>
                </div>
                <div>
                    <select class="form-control custom-select" name="notification[settings][offset_before_hours]" id="notification_send_2">
                        <?php foreach ( array_merge( array( - 672, - 504 ), range( - 336, - 24, 24 ) ) as $hour ) : ?>
                            <option value="<?php echo esc_attr( $hour ) ?>"><?php echo esc_html( Utils\DateTime::secondsToInterval( abs( $hour ) * HOUR_IN_SECONDS ) ) ?>&nbsp;<?php esc_html_e( 'before', 'connectpx_booking' ) ?></option>
                        <?php endforeach ?>
                        <option value="0" selected><?php esc_html_e( 'on the same day', 'connectpx_booking' ) ?></option>
                    </select>
                </div>
                <div class="align-self-center mx-2">
                    <?php esc_html_e( 'at', 'connectpx_booking' ) ?>
                </div>
                <div>
                    <select class="form-control custom-select" name="notification[settings][before_at_hour]">
                        <?php foreach ( range( 0, 23 ) as $hour ) : ?>
                            <option value="<?php echo esc_attr( $hour ) ?>"><?php echo esc_html( Utils\DateTime::formatTime( $hour * HOUR_IN_SECONDS ) ) ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>