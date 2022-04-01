<?php
namespace ConnectpxBooking\Lib\Notifications\Appointment;

use ConnectpxBooking\Lib\Config;
use ConnectpxBooking\Lib\Entities\Appointment;
use ConnectpxBooking\Lib\Entities\Notification;
use ConnectpxBooking\Lib\Notifications\Base;
use ConnectpxBooking\Lib\Notifications\WPML;
use ConnectpxBooking\Lib\Notifications\Assets\Appointment\Attachments;
use ConnectpxBooking\Lib\Notifications\Assets\Appointment\Codes;

/**
 * Class Sender
 * @package ConnectpxBooking\Lib\Notifications\Instant\Backend
 */
abstract class Sender extends Base\Sender
{
    /**
     * Send notifications.
     *
     * @param Item       $appointment
     * @param array      $codes_data
     * @param bool       $force_new_booking
     * @param bool|array $queue
     */
    public static function send( Appointment $appointment, $codes_data = array(), $force_new_booking = false, &$queue = false )
    {
        $codes = new Codes( $appointment );
        if ( isset ( $codes_data['cancellation_reason'] ) ) {
            $codes->cancellation_reason = $codes_data['cancellation_reason'];
        }
   
        $notifications = static::getNotifications( Notification::TYPE_APPOINTMENT_STATUS_CHANGED );

        // Notify client.
        self::notifyClient( $notifications['client'], $appointment, $codes, $queue );

        // Notify staff and admins.
        self::notifyStaffAndAdmins( $notifications['admin'], $appointment, $codes, $queue );
    }

    /**
     * Notify client.
     *
     * @param Notification[] $notifications
     * @param Appointment           $appointment
     * @param Order          $order
     * @param Codes          $codes
     * @param bool|array     $queue
     */
    protected static function notifyClient( array $notifications, Appointment $appointment, Codes $codes, &$queue = false )
    {
        $attachments = new Attachments( $codes );

        foreach ( $notifications as $notification ) {
            if ( $notification->matchesAppointmentForClient( $appointment ) ) {
                static::sendToClient( $appointment->getCustomer(), $notification, $codes, $attachments, $queue );
            }
        }

        if ( $queue === false ) {
            $attachments->clear();
        }
    }

    /**
     * Notify staff and/or administrators.
     *
     * @param Notification[] $notifications
     * @param Appointment           $appointment
     * @param Order          $order
     * @param Codes          $codes
     * @param array|bool     $queue
     */
    protected static function notifyStaffAndAdmins( array $notifications, Appointment $appointment, Codes $codes, &$queue = false )
    {
        WPML::switchToDefaultLang();

        // Reply to customer.
        $reply_to = null;

        $attachments = new Attachments( $codes );
        foreach ( $notifications as $notification ) {
            $send = $notification->matchesAppointmentForAdmin( $appointment, $appointment->getService() );
            if ( $send ) {
                static::sendToAdmins( $notification, $codes, $attachments, $reply_to, $queue );
                static::sendToCustom( $notification, $codes, $attachments, $reply_to, $queue );
            }
        }
        if ( $queue === false ) {
            $attachments->clear();
        }

        WPML::restoreLang();
    }
}