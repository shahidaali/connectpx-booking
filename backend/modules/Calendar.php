<?php
namespace ConnectpxBooking\Backend\Modules;

use ConnectpxBooking\Lib;
use ConnectpxBooking\Backend\Modules\Forms;
use ConnectpxBooking\Lib\Utils;

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
class Calendar extends Lib\Base\Component {
    protected static $pageSlug = 'connectpx_booking_calendar';

	/**
     * Render page.
     */
    public static function render()
    {
        wp_localize_script( 'connectpx_booking_calendar', 'ConnectpxBookingL10n', array_merge(
            Lib\Utils\Common::getCalendarSettings(),
            array(
                'delete' => __( 'Delete', 'connectpx_booking' ),
                'are_you_sure' => __( 'Are you sure?', 'connectpx_booking' ),
                'filterResourcesWithEvents' => 0,
            ) ) );

        wp_enqueue_script('connectpx_booking_calendar');

        $refresh_rate = get_user_meta( get_current_user_id(), 'connectpx_booking_calendar_refresh_rate', true );
        $services_dropdown_data = Lib\Utils\Common::getServiceDataForDropDown();

        self::renderTemplate( 'backend/templates/calendar', compact( 'services_dropdown_data', 'refresh_rate' ) );
    }
    
}
