<?php
namespace ConnectpxBooking\Backend\Components\Dialogs\Invoice\Edit;

use ConnectpxBooking\Backend\Modules\Calendar;
use ConnectpxBooking\Lib;
use ConnectpxBooking\Lib\Entities\Invoice;
use ConnectpxBooking\Lib\Entities\Customer;
use ConnectpxBooking\Lib\Entities\Appointment;
use ConnectpxBooking\Lib\Slots\DatePoint;
use ConnectpxBooking\Lib\Utils\Common;
use ConnectpxBooking\Lib\Utils\DateTime;

/**
 * Class Ajax
 * @package ConnectpxBooking\Backend\Components\Dialogs\Invoice\Edit
 */
class Ajax extends Lib\Base\Ajax
{
    /**
     * @inheritDoc
     */
    protected static function permissions()
    {
        return array( '_default' => array( 'anonymous' ) );
    }

    /**
     * Get invoice data when editing an invoice.
     */
    public static function renderCreateInvoices()
    {

        $customers = [];
        foreach (Lib\Utils\Invoice::getCustomers() as $key => $customer) {
            $name = $customer['full_name'];
            if ( $customer['email'] != '' || $customer['phone'] != '' ) {
                $customer_type = $customer['wp_user_id'] ? __('Contract', 'connectpx_booking') : __('Private', 'connectpx_booking');
                $name .= ' (' . $customer['email'] . ', ' . $customer_type . ')';
            }
            $customers[ $customer['id'] ] = $name;
        }

        $periods_options = Lib\Utils\Invoice::getInvoicePeriodOptions();

        $periods = [];
        foreach ( $periods_options as $key => $periods_option ) {
            $periods[ $key ] = $periods_option['label'];
        }

        $response = array( 
            'success' => true, 
            'data' => array() 
        );

        $response['data']['html'] = self::renderTemplate( 'backend/components/dialogs/invoice/edit/templates/modal', compact( 'customers', 'periods' ), false );
        wp_send_json( $response );
    }

    /**
     * Get invoice data when editing an invoice.
     */
    public static function updateInvoices()
    {
        $customer       = self::parameter( 'customer', 'all' );
        $period         = self::parameter( 'period', 'last_week' );

        $response = Lib\Utils\Invoice::updateInvoices( $period, $customer );

        wp_send_json( $response );
    }
}