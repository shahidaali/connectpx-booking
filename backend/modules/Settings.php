<?php
namespace ConnectpxBooking\Backend\Modules;

use ConnectpxBooking\Lib;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    ConnectpxBooking
 * @subpackage ConnectpxBooking\Backend\Modules
 * @author     Shahid Hussain <shahidhussainaali@gmail.com>
 */
class Settings extends Lib\Base\Component {
    protected static $pageSlug = 'connectpx_booking_settings';

	/**
     * Render page.
     */
    public static function render()
    {
        $messages = self::save();
        self::renderTemplate( 'backend/templates/settings', compact('messages') );
    }

    /**
     * Register settings page
     *
     * @since    1.0.0
     */
    public static function save() {
        if ( ! isset( $_POST['connectpx_booking_options'] ) ) {
            return;
        }

        $old_options = Lib\Utils\Common::getOptions();
        $connectpx_booking_options = isset($_POST['connectpx_booking']) ? $_POST['connectpx_booking'] : [];

        $connectpx_booking_options = array_merge($old_options, $connectpx_booking_options);

        // Update options
        update_option( 'connectpx_booking_options',  $connectpx_booking_options );

        Lib\Utils\Common::resetOptions($connectpx_booking_options);

        return [
            'status' => 'success',
            'message' => __( 'Settings saved' )
        ];
    }
}
