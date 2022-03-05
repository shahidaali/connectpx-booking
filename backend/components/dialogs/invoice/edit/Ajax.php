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

        $periods = Lib\Utils\Common::getInvoicePeriodOptions();
        $periods['all'] = __( 'All', 'connectpx_booking' );

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
        $period       = self::parameter( 'period', 'all' );

        $customers = [];
        if( !$customer || $customer == 'all' ) {
            foreach ( self::_getCustomers() as $key => $customer ) {
                $customers[] = $customer['id'];
            }
        } else {
            $customers[] = $customer;
        }

        $weeks = [];
        if( !$period || $period == 'all' ) {

        } else {
            $weeks[] = explode(",", $period);
        }

        foreach ( $weeks as $week ) {
            foreach ( $customers as $customer_id ) {
                $appointments = Appointment::query( 'a' )
                    ->select( 'a.*' )
                    ->whereGte('DATE(a.pickup_datetime)', $week[0])
                    ->whereLte('DATE(a.pickup_datetime)', $week[1])
                    ->where('a.customer_id', $customer_id)
                    ->whereIn('a.status', Appointment::getCompletedStatuses())
                    ->sortBy('DATE(a.pickup_datetime)')
                    ->order('DESC')
                    ->fetchArray();

                if( !empty($appointments) ) {
                    $total_amount = 0;    
                    $paid_amount = 0;    
                    $a_ids = [];    
                    foreach ($appointments as $key => $appointment) {
                        $a_ids[] = $appointment['id'];
                        $total_amount += $appointment['total_amount'];
                        $paid_amount += $appointment['paid_amount'];
                    }

                    $invoice = Invoice::query( 'i' )
                        ->select( 'i.*' )
                        ->where('i.start_date', $week[0])
                        ->where('i.end_date', $week[1])
                        ->where('i.customer_id', $customer_id)
                        ->fetchRow();
                    
                    $details = [];

                    if( !empty($invoice) ) {
                        $invoice = new Invoice( $invoice );

                        if( $invoice->getStatus() == Invoice::STATUS_COMPLETED ) {
                            continue;
                        }

                        $details = !empty($invoice->getDetails()) ? json_decode($invoice->getDetails()) : [];
                    } else {
                        $invoice = new Invoice();
                    }

                    $details['a_ids'] = $a_ids;

                    $invoice
                        ->setCustomerId($customer_id)
                        ->setStartDate($week[0])
                        ->setEndDate($week[1])
                        ->setTotalAmount($total_amount)
                        ->setPaidAmount($paid_amount)
                        ->setDetails( json_encode( $details ) );

                    $invoice->save();
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