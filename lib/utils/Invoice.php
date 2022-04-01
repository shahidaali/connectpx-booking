<?php
namespace ConnectpxBooking\Lib\Utils;

use ConnectpxBooking\Lib;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    ConnectpxBooking
 * @subpackage ConnectpxBooking\Lib\Utils
 * @author     Shahid Hussain <shahidhussainaali@gmail.com>
 */
class Invoice {

	private static $customers;

	/**
     * Get invoice data when editing an invoice.
     */
    public static function getInvoiceAppointments( $start_date, $end_date, $customer_id )
    {
    	$appointments = Lib\Entities\Appointment::query( 'a' )
            ->select( 'a.*' )
            ->whereGte('DATE(a.pickup_datetime)', $start_date)
            ->whereLte('DATE(a.pickup_datetime)', $end_date)
            ->where('a.customer_id', $customer_id)
            ->whereIn('a.status', Lib\Entities\Appointment::getCompletedStatuses())
            ->sortBy('DATE(a.pickup_datetime)')
            ->order('DESC')
            ->fetchArray();

        return $appointments;
    }

	/**
     * Get invoice data when editing an invoice.
     */
    public static function updateInvoices( $period, $customer = null )
    {
        $customers = [];
        if( ! $customer || $customer == 'all' ) {
            foreach ( self::getCustomers() as $key => $customer ) {
                $customers[] = $customer['id'];
            }
        } else {
            $customers[] = $customer;
        }

        $periods = self::getInvoicePeriodOptions();
        $weeks = $periods[$period]['weeks'];

        foreach ( $weeks as $week ) {
            $startDate = $week['start']->format('Y-m-d');
            $endDate = $week['end']->format('Y-m-d');

            foreach ( $customers as $customer_id ) {
            	$appointments = self::getInvoiceAppointments( $startDate, $endDate, $customer_id );

                if( ! empty( $appointments ) ) {
                    $invoice = Lib\Entities\Invoice::query( 'i' )
                        ->select( 'i.*' )
                        ->where('i.start_date', $startDate)
                        ->where('i.end_date', $endDate)
                        ->where('i.customer_id', $customer_id)
                        ->fetchRow();

                    if( !empty($invoice) ) {
                        $invoice = new Lib\Entities\Invoice( $invoice );
                    } else {
                        $invoice = new Lib\Entities\Invoice();
                    }

                    $due_days = Lib\Utils\Common::getOption('invoices_due_days', 30);
                    $due_date = $invoice ? Lib\Slots\DatePoint::fromStr( $invoice->getCreatedAt() ) : Lib\Slots\DatePoint::now();
                    $due_date = $due_date->modify( $due_days * DAY_IN_SECONDS )->format( 'Y-m-d' );
                    
                    $invoice
                        ->setCustomerId($customer_id)
                        ->setStartDate($startDate)
                        ->setEndDate($endDate)
                        ->setDueDate( $due_date )
                        ->save();

                    $invoice->updateTotals( $appointments );
                }
                
            }
        }
        

        $response = array( 
            'success' => true, 
            'data' => array() 
        );

        return $response;
    }

    public static function getWeekDates( $startDateStr, $endDateStr = null ) {
        $startDate = Lib\Slots\DatePoint::fromStr( $startDateStr );
        $endDate = $endDateStr ? Lib\Slots\DatePoint::fromStr( $endDateStr ) : Lib\Slots\DatePoint::now();

        $monday = clone $startDate->modify('Monday this week');
        $sunday = clone $startDate->modify('Sunday this week');
        $week = [
            'start' => $monday, 
            'end' => $sunday
        ];

        $weeks[] = $week;
        while ( $week['end']->lt( $endDate ) ) {
            $monday = clone $week['end']->modify('+1 day');
            $sunday = clone $week['end']->modify('+7 days');
            $week = [
                'start' => $monday, 
                'end' => $sunday
            ];
            $weeks[] = $week;
        }

        if( count($weeks) == 1 ) {
        	$startDate = $weeks[0]['start'];
        	$endDate = $weeks[0]['end'];
        }

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'weeks' => $weeks,
        ];
    }

    public static function getInvoicePeriodOptions() {
        $periods = [
            [
                'key' => 'this_week',
                'start' => date('Y-m-d'),
                'end' => null,
                'label' => 'This Week (%s - %s)',
            ],
            [
                'key' => 'last_week',
                'start' => date('Y-m-d', strtotime('Monday last week')),
                'end' => date('Y-m-d', strtotime('Monday last week')),
                'label' => 'Last Week (%s - %s)',
            ],
            [
                'key' => 'last_three_months',
                'start' => date('Y-m-d', strtotime('-3 months')),
                'end' => null,
                'label' => 'Last 3 Months (%s - %s)',
            ],
            [
                'key' => 'last_six_months',
                'start' => date('Y-m-d', strtotime('-6 months')),
                'end' => null,
                'label' => 'Last 6 Months (%s - %s)',
            ],
            [
                'key' => 'this_year',
                'start' => date('Y-01-01'),
                'end' => null,
                'label' => 'This Year (%s - %s)',
            ],
            [
                'key' => 'last_year',
                'start' => date('Y-m-d', strtotime('first day of january last year')),
                'end' => null,
                'label' => 'Last Year (%s - %s)',
            ],
        ];

        $options = [];

        foreach( $periods as $period ) {
            $dates = self::getWeekDates( $period['start'], $period['end'] );
            $options[ $period['key'] ] = [
                'weeks' => $dates['weeks'],
                'label' => __( sprintf( $period['label'], $dates['start_date']->format('m/d/Y'), $dates['end_date']->format('m/d/Y') ), 'connectpx_booking' ),
            ];
        }

        return $options;
    }

    /**
     * Get invoice data when editing an invoice.
     */
    public static function getCustomers()
    {
    	if( empty(self::$customers) ) {
        	self::$customers = Lib\Entities\Customer::query( 'c' )
	            ->select( 'c.*, CONCAT(c.first_name, " ", c.last_name) as full_name' )
	            ->fetchArray();

    	}

        return self::$customers;
    }
}
