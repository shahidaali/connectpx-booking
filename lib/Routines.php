<?php
namespace ConnectpxBooking\Lib;

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

        // Schedule hourly routine.
        if ( ! wp_next_scheduled( 'connectpx_booking_hourly_routine' ) ) {
            wp_schedule_event( current_time( 'timestamp' ), 'hourly', 'connectpx_booking_hourly_routine' );
        }
    }
}