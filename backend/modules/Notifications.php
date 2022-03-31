<?php
namespace ConnectpxBooking\Backend\Modules;

use ConnectpxBooking\Lib;
use ConnectpxBooking\Backend\Modules\Forms;
use ConnectpxBooking\Lib\Utils;
use ConnectpxBooking\Backend;

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
class Notifications extends Lib\Base\Component {
    protected static $pageSlug = 'connectpx_booking_notifications';

	/**
     * Render page.
     */
    public static function render()
    {
        // $wc_order = new \WC_Order( 610 );
        // Lib\Notifications\Cart\Sender::send( $wc_order );
        // return;
        
        // $appointment = Lib\Entities\Appointment::find( 2 );
        // Lib\Notifications\Appointment\Sender::send( $appointment );
        // return;
        
        // $customer = Lib\Entities\Customer::find( 2 );
        // Lib\Notifications\Customer\Sender::send( $customer, 'shahid', 'abc123' );
        // return;
        
        // $invoice = Lib\Entities\Invoice::find( 2 );
        // Lib\Notifications\Invoice\Sender::send( $invoice );
        // return;

        $tab = self::parameter( 'tab', 'notifications' );

        $datatables = Lib\Utils\Tables::getSettings( array( 'email_notifications', 'email_logs' ) );

        wp_localize_script( 'connectpx_booking_notifications', 'ConnectpxBookingL10n', array(
            'sentSuccessfully' => __( 'Sent successfully.', 'connectpx_booking' ),
            'settingsSaved' => __( 'Settings saved.', 'connectpx_booking' ),
            'areYouSure' => __( 'Are you sure?', 'connectpx_booking' ),
            'noResults' => __( 'No records.', 'connectpx_booking' ),
            'processing' => __( 'Processing...', 'connectpx_booking' ),
            'state' => array( __( 'Disabled', 'connectpx_booking' ), __( 'Enabled', 'connectpx_booking' ) ),
            'action' => array( __( 'enable', 'connectpx_booking' ), __( 'disable', 'connectpx_booking' ) ),
            'edit' => __( 'Edit', 'connectpx_booking' ),
            'gateway' => 'email',
            'tab' => $tab,
            'datatables' => $datatables,
        ) );

        wp_localize_script( 'connectpx_booking_notifications', 'ConnectpxBookingEmailLogsL10n', array(
            'datatables' => $datatables,
            'datePicker' => Lib\Utils\DateTime::datePickerOptions(),
            'dateRange' => Lib\Utils\DateTime::dateRangeOptions( array( 'lastMonth' => __( 'Last month', 'connectpx_booking' ), ) ),
            'details' => __( 'Details', 'connectpx_booking' ),
            'zeroRecords' => __( 'No records for selected period.', 'connectpx_booking' ),
            'processing' => __( 'Processing...', 'connectpx_booking' ),
        ) );

        wp_enqueue_script('connectpx_booking_notifications');
        
        self::renderTemplate( 'backend/templates/notifications', compact( 'tab', 'datatables' ) );
    }
}
