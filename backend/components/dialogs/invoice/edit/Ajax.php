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
        foreach (self::_getCustomers() as $key => $customer) {
            $name = $customer['full_name'];
            if ( $customer['email'] != '' || $customer['phone'] != '' ) {
                $customer_type = $customer['wp_user_id'] ? __('Contract', 'connectpx_booking') : __('Private', 'connectpx_booking');
                $name .= ' (' . $customer['email'] . ', ' . $customer_type . ')';
            }
            $customers[ $customer['id'] ] = $name;
        }

        $periods_options = Lib\Utils\Common::getInvoicePeriodOptions();

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

        $customers = [];
        if( !$customer || $customer == 'all' ) {
            foreach ( self::_getCustomers() as $key => $customer ) {
                $customers[] = $customer['id'];
            }
        } else {
            $customers[] = $customer;
        }

        $periods = Lib\Utils\Common::getInvoicePeriodOptions();
        $weeks = $periods[$period]['weeks'];

        foreach ( $weeks as $week ) {
            $startDate = $week['start']->format('Y-m-d');
            $endDate = $week['end']->format('Y-m-d');

            foreach ( $customers as $customer_id ) {
                $appointments = Appointment::query( 'a' )
                    ->select( 'a.*' )
                    ->whereGte('DATE(a.pickup_datetime)', $startDate)
                    ->whereLte('DATE(a.pickup_datetime)', $endDate)
                    ->where('a.customer_id', $customer_id)
                    ->whereIn('a.status', Appointment::getCompletedStatuses())
                    ->sortBy('DATE(a.pickup_datetime)')
                    ->order('DESC')
                    ->fetchArray();

                if( !empty( $appointments ) ) {
                    $invoice = Invoice::query( 'i' )
                        ->select( 'i.*' )
                        ->where('i.start_date', $startDate)
                        ->where('i.end_date', $endDate)
                        ->where('i.customer_id', $customer_id)
                        ->fetchRow();

                    if( !empty($invoice) ) {
                        $invoice = new Invoice( $invoice );
                    } else {
                        $invoice = new Invoice();
                    }

                    $invoice
                        ->setCustomerId($customer_id)
                        ->setStartDate($startDate)
                        ->setEndDate($endDate)
                        ->save();

                    $invoice->updateTotals( $appointments );
                }
                
            }
        }
        

        $response = array( 
            'success' => true, 
            'data' => array() 
        );

        wp_send_json( $response );
    }

    /**
     * Get invoice data when editing an invoice.
     */
    public static function _getCustomers()
    {
        $customers = Customer::query( 'c' )
            ->select( 'c.*, CONCAT(c.first_name, " ", c.last_name) as full_name' )
            ->fetchArray();
        return $customers;
    }
}