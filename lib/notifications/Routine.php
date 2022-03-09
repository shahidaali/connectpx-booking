<?php

namespace ConnectpxBooking\Lib\Notifications;

use ConnectpxBooking\Lib;
use ConnectpxBooking\Lib\DataHolders\Notification\Settings;
use ConnectpxBooking\Lib\Entities\Appointment;
use ConnectpxBooking\Lib\Entities\Customer;
use ConnectpxBooking\Lib\Entities\Notification;
use ConnectpxBooking\Lib\Entities\SentNotification;
use ConnectpxBooking\Lib\Entities\Service;
use ConnectpxBooking\Lib\Utils\DateTime;

/**
 * Class Routine
 *
 * @package ConnectpxBooking\Lib\Notifications
 */
abstract class Routine
{
    /** @var Lib\Slots\DatePoint */
    private static $date_point;
    /** @var Lib\Slots\DatePoint */
    private static $today;
    /** @var string Format: YYYY-MM-DD */
    private static $mysql_today;
    /** @var int hours */
    private static $processing_interval;
    /** @var int */
    private static $hours;

    /**
     * Notification
     *
     * @param Notification $notification
     */
    public static function processNotification( Notification $notification )
    {
        $settings = new Settings( $notification );

        if ( ! $settings->getInstant() ) {
            $appointments_list   = array();
            $customers = array();
            $statuses = array(
                Lib\Entities\Appointment::STATUS_PENDING,
                Lib\Entities\Appointment::STATUS_APPROVED,
            );

            switch ( $notification->getType() ) {
                // Appointment start date add time.
                case Notification::TYPE_APPOINTMENT_REMINDER:
                    $appointments_list = self::getAppointments( $notification, $settings );
                    if ( $settings->getStatus() === 'any' ) {
                        $statuses = array();
                    }
                    break;
            }

            if ( $appointments_list ) {
                foreach ( $appointments_list as $appointment ) {
                    if ( Booking\Reminder::send( $notification, $appointment ) ) {
                        self::wasSent( $notification, $appointment->getId() );
                    }
                }
            } else {
                foreach ( $customers as $customer ) {
                    $codes = new Assets\Customer\Codes( $customer );
                    if ( $notification->getToCustomer() && Base\Reminder::sendToClient( $customer, $notification, $codes ) ) {
                        self::wasSent( $notification, $customer->getId() );
                    }
                    if ( $notification->getToCustom() && Base\Reminder::sendToCustom( $notification, $codes ) ) {
                        self::wasSent( $notification, $customer->getId() );
                    }
                }
            }
        }
    }

    /**
     * Get customer appointments for notification
     *
     * @param Notification $notification
     * @param Settings     $settings
     * @return Appointment[]
     */
    private static function getAppointments( Notification $notification, Settings $settings )
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        $appointments_list = array();

        if ( $settings->getAtHour() !== null ) {
            // Send at time after start_date date (some day at 08:00)
            if ( self::isTimeToSend( $settings->getAtHour() ) ) {
                $query = sprintf(
                    'SELECT `a`.* FROM `%s` `a`
                      WHERE DATE(`a`.`pickup_datetime`) = DATE("%s")',
                    Appointment::getTableName(),
                    self::$today->modify( - $settings->getOffsetHours() * HOUR_IN_SECONDS )->format( 'Y-m-d' )
                );
            } else {
                return $appointments_list;
            }
        } else {
            $query = sprintf(
                'SELECT `a`.* FROM `%s` `a`
                  WHERE `a`.`pickup_datetime` BETWEEN "%s" AND "%s"',
                Appointment::getTableName(),
                self::$date_point->modify( - ( $settings->getOffsetHours() + self::$processing_interval ) * HOUR_IN_SECONDS )->format( 'Y-m-d H:i:s' ),
                self::$date_point->modify( - $settings->getOffsetHours() * HOUR_IN_SECONDS )->format( 'Y-m-d H:i:s' )
            );
        }

        // Select appointments for which reminders need to be sent today.
        $query .= sprintf( ' AND NOT EXISTS ( %s )',
            self::getQueryIfNotificationWasSent( $notification )
        );
        if ( $settings->getStatus() != 'any' ) {
            $query .= sprintf( ' AND `a`.`status` = "%s"', $settings->getStatus() );
        }

        foreach ( (array) $wpdb->get_results( $query, ARRAY_A ) as $fields ) {
            $appointments_list[] = new Appointment( $fields );
        }

        return $appointments_list;
    }

    /**
     * @param Notification $notification
     * @return string
     */
    private static function getQueryIfNotificationWasSent( Notification $notification )
    {
        return sprintf( '
                SELECT * FROM `%s` `sn` 
                WHERE `sn`.`ref_id` = `a`.`id`
                  AND `sn`.`notification_id` = %d
            ',
            SentNotification::getTableName(),
            $notification->getId()
        );
    }

    /**
     * @param int $at_hour
     * @return bool
     */
    private static function isTimeToSend( $at_hour )
    {
        $range = Lib\Slots\Range::fromDates(
            sprintf( '%02d:00:00', $at_hour ),
            sprintf( '%02d:00:00 + %d hours', $at_hour, self::$processing_interval )
        );

        return $range->contains( self::$date_point );
    }

    /**
     * Mark notification as sent.
     *
     * @param Notification $notification
     * @param int          $ref_id
     */
    private static function wasSent( Notification $notification, $ref_id )
    {
        $sent_notification = new SentNotification();
        $sent_notification
            ->setRefId( $ref_id )
            ->setNotificationId( $notification->getId() )
            ->setCreatedAt( current_time( 'mysql' ) )
            ->save();
    }

    /**
     * Send notifications.
     */
    public static function sendNotifications()
    {
        // Disable caching.
        Lib\Utils\Common::noCache( true );

        $original_timezone = date_default_timezone_get();

        // @codingStandardsIgnoreStart
        date_default_timezone_set( 'UTC' );

        self::$date_point = Lib\Slots\DatePoint::now();
        self::$today = Lib\Slots\DatePoint::fromStr( 'today' );
        self::$mysql_today = self::$today->format( 'Y-m-d' );
        self::$hours = self::$date_point->format( 'H' );
        self::$processing_interval = (int) Lib\Utils\Common::getOption( 'ntf_processing_interval', 2 );

        // Custom notifications.
        $custom_notifications = Notification::query()
            ->where( 'active', 1 )
            ->find();
        $notifications = array( 'email' => Notification::getTypes( 'email' ) );

        /** @var Notification $notification */
        foreach ( $custom_notifications as $notification ) {
            if ( in_array( $notification->getType(), $notifications[ $notification->getGateway() ] ) ) {
                self::processNotification( $notification );
            }
        }
        
        date_default_timezone_set( $original_timezone );
        // @codingStandardsIgnoreEnd
    }
}