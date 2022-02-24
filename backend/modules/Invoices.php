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
class Invoices extends Lib\Base\Component {
    protected static $pageSlug = 'connectpx_booking_invoices';

	/**
     * Render page.
     */
    public static function render()
    {
        $datatables = Lib\Utils\Tables::getSettings( 'invoices' );

        wp_localize_script( 'connectpx_booking_invoices', 'ConnectpxBookingL10n', array(
            'datePicker'      => Lib\Utils\DateTime::datePickerOptions(),
            'dateRange'       => Lib\Utils\DateTime::dateRangeOptions( array( 'anyTime' => __( 'Any time', 'connectpx_booking' ), 'createdAtAnyTime' => __( 'Created at any time', 'connectpx_booking' ), ) ),
            'are_you_sure'    => __( 'Are you sure?', 'connectpx_booking' ),
            'zeroRecords'     => __( 'No invoices for selected period.', 'connectpx_booking' ),
            'processing'      => __( 'Processing...', 'connectpx_booking' ),
            'edit'            => __( 'Edit', 'connectpx_booking' ),
            'no_result_found' => __( 'No result found', 'connectpx_booking' ),
            'searching'       => __( 'Searching', 'connectpx_booking' ),
            'datatables'      => $datatables,
        ) );

        wp_enqueue_script('connectpx_booking_invoices');

        $customers     = Lib\Entities\Customer::query()->count() < Lib\Entities\Customer::REMOTE_LIMIT
            ? array_map( function ( $row ) {
                unset( $row['id'] );

                return $row;
            }, Lib\Entities\Customer::query( 'c' )->select( 'c.id, CONCAT(c.first_name, " ", c.last_name) as full_name, c.email, c.phone' )->indexBy( 'id' )->fetchArray() )
            : false;
        $services      = Lib\Entities\Service::query( 's' )->select( 's.id, s.title' )->fetchArray();

        self::renderTemplate( 'backend/templates/invoices', compact( 'customers', 'services', 'datatables' ) );
    }
}
