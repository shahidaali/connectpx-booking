<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use ConnectpxBooking\Backend\Components\Ace;
?>
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label class="float-left mt-2"><?php esc_html_e( 'Body', 'connectpx_booking' ) ?></label>
            <ul class="nav nav-tabs justify-content-end mr-2<?php if ( !user_can_richedit() ) : ?> collapse<?php endif ?>" style="border-bottom: none;">
                <li class="nav-item">
                    <a class="nav-link active" href="#connectpx_booking-wp-editor-pane" data-toggle="connectpx_booking-tab" data-tinymce><?php esc_html_e( 'Visual', 'connectpx_booking' ) ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#connectpx_booking-ace-editor-pane" data-toggle="connectpx_booking-tab" data-ace><?php esc_html_e( 'Text', 'connectpx_booking' ) ?></a>
                </li>
            </ul>
            <div class="tab-content">
                <div id="connectpx_booking-wp-editor-pane" class="tab-pane<?php if ( user_can_richedit() ) : ?> active<?php endif ?>">
                    <?php wp_editor( '', 'connectpx_booking-js-message', array(
                        'textarea_name' => 'notification[message]',
                        'media_buttons' => false,
                        'editor_height' => 250,
                        'default_editor' => 'tinymce',
                        'quicktags' => false,
                        'editor_css' => '<style>.wp-editor-tools{margin-top:-27px;}.wp-editor-tools [type="button"]{box-sizing:content-box!important;}</style>',
                        'tinymce' => array(
                            'resize' => true,
                            'wp_autoresize_on' => true,
                        ),
                    ) ) ?>
                </div>
                <div id="connectpx_booking-ace-editor-pane" class="tab-pane<?php if ( !user_can_richedit() ) : ?> active<?php endif ?>">
                    <?php Ace\Editor::render( 'connectpx_booking-notifications' ) ?>
                    <?php if ( !user_can_richedit() ) : ?>
                    <input type="hidden" name="notification[message]" />
                    <?php endif ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php // static::renderTemplate( 'backend/components/dialogs/notifications/templates/_attach' ) ?>