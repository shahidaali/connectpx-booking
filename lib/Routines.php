<?php
namespace ConnectpxBooking\Lib;

use ConnectpxBooking\Lib;

/**
 * Class Routines
 * @package ConnectpxBooking\Lib
 */
abstract class Routines
{
    /**
     * Init routines.
     */
    public static function init()
    {
        // Register hourly routine.
        add_action( 'connectpx_booking_hourly_routine', function () {
            // Email and SMS notifications routine.
            Notifications\Routine::sendNotifications();
        }, 10, 0 );

        // Register daily routine.
        add_action( 'connectpx_booking_daily_routine', function () {
            // Update invoices
            self::updateInvoices();
        }, 10, 0 );

        // Schedule hourly routine.
        if ( ! wp_next_scheduled( 'connectpx_booking_hourly_routine' ) ) {
            wp_schedule_event( current_time( 'timestamp' ), 'hourly', 'connectpx_booking_hourly_routine' );
        }

        // Schedule daily routine.
        if ( ! wp_next_scheduled( 'connectpx_booking_daily_routine' ) ) {
            wp_schedule_event( current_time( 'timestamp' ), 'daily', 'connectpx_booking_daily_routine' );
        }
    }

    /**
     * Update or create new invoices
     */
    public static function updateInvoices()
    {
        Lib\Utils\Invoice::updateInvoices( 'this_week', 'all' );
    }
}