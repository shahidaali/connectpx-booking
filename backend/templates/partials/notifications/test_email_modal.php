<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use ConnectpxBooking\Backend\Components\Controls\Buttons;
use ConnectpxBooking\Backend\Components\Controls\Inputs;
?>
<div id=connectpx_booking-test-email-notifications-modal class="connectpx_booking-modal connectpx_booking-fade" tabindex=-1 role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form>
                <div class="modal-header">
                    <h5 class="modal-title"><?php esc_html_e( 'Test email notifications', 'connectpx_booking' ) ?></h5>
                    <button type="button" class="close" data-dismiss="connectpx_booking-modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="connectpx_booking_test_to_email"><?php esc_html_e( 'To email', 'connectpx_booking' ) ?></label>
                                <input id="connectpx_booking_test_to_email" class="form-control" type="text" name="to_email" value="<?php echo get_bloginfo('admin_email'); ?>"/>
                            </div>
                        </div>
                    </div>
                    <?php self::renderTemplate( 'backend/templates/partials/notifications/common_settings', array( 'tail' => '_test' ) ) ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <div class="dropdown">
                                    <button class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-display="static">
                                        <?php esc_html_e( 'Notification templates', 'connectpx_booking' ) ?>
                                        (<span class="connectpx_booking-js-count">0</span>)
                                    </button>
                                    <div class="dropdown-menu">
                                        <div class="dropdown-item my-0 pl-3">
                                            <?php Inputs::renderCheckBox( __( 'All templates', 'connectpx_booking' ), null, null, array( 'id' => 'connectpx_booking-check-all-entities' ) ) ?>
                                        </div>
                                        <div class="dropdown-divider"></div>
                                        <div id="connectpx_booking-js-test-notifications-list"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <?php Inputs::renderCsrf() ?>
                    <?php Buttons::render( null, 'btn-success', __( 'Send', 'connectpx_booking' ), array( 'disabled' => 'disabled' ) ) ?>
                </div>
            </form>
        </div>
    </div>
</div>