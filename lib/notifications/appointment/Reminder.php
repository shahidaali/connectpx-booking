<?php
namespace ConnectpxBooking\Lib\Notifications\Appointment;

use ConnectpxBooking\Lib\Entities\Notification;
use ConnectpxBooking\Lib\Entities\Appointment;
use ConnectpxBooking\Lib\Notifications\Assets\Appointment\Attachments;
use ConnectpxBooking\Lib\Notifications\Assets\Appointment\Codes;
use ConnectpxBooking\Lib\Notifications\Base;
use ConnectpxBooking\Lib\Notifications\WPML;

/**
 * Class Reminder
 * @package ConnectpxBooking\Lib\Notifications\Appointment
 */
abstract class Reminder extends Base\Reminder
{
    /**
     * Send booking/appointment notifications.
     *
     * @param Notification $notification
     * @param Appointment $appointment
     * @return bool
     */
    public static function send( Notification $notification, Appointment $appointment )
    {
        $codes = new Codes( $appointment );
        $attachments = new Attachments( $codes );

        $result = false;
        $reply_to = null;
        
        // Notify client.
        if ( static::sendToClient( $appointment->getCustomer(), $notification, $codes, $attachments ) ) {
            $result = true;
        }

        // Notify admins.
        if ( static::sendToAdmins( $notification, $codes, $attachments, $reply_to ) ) {
            $result = true;
        }

        // Notify customs.
        if ( static::sendToCustom( $notification, $codes, $attachments, $reply_to ) ) {
            $result = true;
        }

        $attachments->clear();

        return $result;
    }
}