<?php
namespace ConnectpxBooking\Lib\Notifications\Appointment;

use ConnectpxBooking\Lib\Entities\Notification;
use ConnectpxBooking\Lib\Entities\Appointment;
use ConnectpxBooking\Lib\Notifications\Assets\Appointment\Attachments;
use ConnectpxBooking\Lib\Notifications\Assets\Appointment\Codes;
use ConnectpxBooking\Lib\Notifications\Base;
use ConnectpxBooking\Lib\Notifications\WPML;

/**
 * Class BaseSender
 * @package ConnectpxBooking\Lib\Notifications\Base
 */
abstract class BaseSender extends Base\Sender
{
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
        if ( $appointment->getLocale() ) {
            WPML::switchLang( $appointment->getLocale() );
        } else {
            WPML::switchToDefaultLang();
        }

        $codes->prepareForAppointment( $appointment, 'client' );
        $attachments = new Attachments( $codes );

        foreach ( $notifications as $notification ) {
            if ( $notification->matchesAppointmentForClient( $appointment ) ) {
                static::sendToClient( $order->getCustomer(), $notification, $codes, $attachments, $queue );
            }
        }

        if ( $queue === false ) {
            $attachments->clear();
        }

        WPML::restoreLang();
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

        $codes->prepareForAppointment( $appointment, 'staff' );
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