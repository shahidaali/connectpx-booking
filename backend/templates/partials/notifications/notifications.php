<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use ConnectpxBooking\Backend\Components\Controls\Buttons;
use ConnectpxBooking\Backend\Components\Controls\Inputs;
use ConnectpxBooking\Backend\Components\Dialogs;
use ConnectpxBooking\Backend\Modules\Notifications;
use ConnectpxBooking\Lib\Utils\Common;
use ConnectpxBooking\Lib\Config;

/** @var array $datatables */
?>

<div class="d-block d-lg-flex">
    <div>
        <div class="form-group">
            <input class="form-control" type="text" id="connectpx_booking-filter" placeholder="<?php esc_attr_e( 'Quick search notifications', 'connectpx_booking' ) ?>"/>
        </div>
    </div>
</div>
<div class="row">
    <div class="col">
        <table id="connectpx_booking-js-notification-list" class="table table-striped w-100">
            <thead>
            <tr>
                <?php foreach ( $datatables['email_notifications']['settings']['columns'] as $column => $show ) : ?>
                    <?php if ( $show ) : ?>
                        <?php if ( $column == 'type' ) : ?>
                            <th width="1"></th>
                        <?php else : ?>
                            <th><?php echo esc_html( $datatables['email_notifications']['titles'][ $column ] ) ?></th>
                        <?php endif ?>
                    <?php endif ?>
                <?php endforeach ?>
                <th width="75"></th>
                <th width="16"><?php Inputs::renderCheckBox( null, null, null, array( 'id' => 'connectpx_booking-check-all' ) ) ?></th>
            </tr>
            </thead>
        </table>
    </div>
</div>
<div class="form-row mb-3">
    <div class="col-auto">
        <?php Inputs::renderCsrf() ?>
        <?php Buttons::renderDefault( 'connectpx_booking-js-test-email-notifications', null, __( 'Test email notifications', 'connectpx_booking' ), array(), true ) ?>
    </div>
</div>
<div class="alert alert-info">
    <div class="row">
        <div class="col-md-12">
            <p><?php esc_html_e( 'To send scheduled notifications please execute the following command hourly with your cron:', 'connectpx_booking' ) ?></p>
                <code>wget -q -O - <?php echo site_url( 'wp-cron.php' ) ?></code>
        </div>
    </div>
</div>
<?php $self::renderTemplate( 'backend/templates/partials/notifications/test_email_modal' ) ?>

