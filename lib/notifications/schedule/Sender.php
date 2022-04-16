<?php
namespace ConnectpxBooking\Lib\Notifications\Schedule;

use ConnectpxBooking\Lib\Config;
use ConnectpxBooking\Lib\Entities\Schedule;
use ConnectpxBooking\Lib\Entities\Notification;
use ConnectpxBooking\Lib\Notifications\Base;
use ConnectpxBooking\Lib\Notifications\WPML;
use ConnectpxBooking\Lib\Notifications\Assets\Schedule\Codes;

/**
 * Class Sender
 * @package ConnectpxBooking\Lib\Notifications\Instant\Backend
 */
abstract class Sender extends Base\Sender
{
    /**
     * Send notifications.
     *
     * @param Item       $schedule
     * @param array      $codes_data
     * @param bool       $force_new_booking
     * @param bool|array $queue
     */
    public static function send( Schedule $schedule, $appointments = array(), $codes_data = array(), $force_new_booking = false, &$queue = false )
    {
        $codes = new Codes( $schedule, $appointments );
        if ( isset ( $codes_data['cancellation_reason'] ) ) {
            $codes->cancellation_reason = $codes_data['cancellation_reason'];
        }
   
        $notifications = static::getNotifications( Notification::TYPE_SCHEDULE_STATUS_CHANGED );

        // Notify client.
        self::notifyClient( $notifications['client'], $schedule, $codes, $queue );

        // Notify staff and admins.
        self::notifyStaffAndAdmins( $notifications['admin'], $schedule, $codes, $queue );
    }

    /**
     * Notify client.
     *
     * @param Notification[] $notifications
     * @param Schedule           $schedule
     * @param Order          $order
     * @param Codes          $codes
     * @param bool|array     $queue
     */
    protected static function notifyClient( array $notifications, Schedule $schedule, Codes $codes, &$queue = false )
    {
        $attachments = [];

        foreach ( $notifications as $notification ) {
            if ( $notification->matchesScheduleForClient( $schedule ) ) {
                static::sendToClient( $schedule->getCustomer(), $notification, $codes, $attachments, $queue );
            }
        }
    }

    /**
     * Notify staff and/or administrators.
     *
     * @param Notification[] $notifications
     * @param Schedule           $schedule
     * @param Order          $order
     * @param Codes          $codes
     * @param array|bool     $queue
     */
    protected static function notifyStaffAndAdmins( array $notifications, Schedule $schedule, Codes $codes, &$queue = false )
    {
        WPML::switchToDefaultLang();

        // Reply to customer.
        $reply_to = null;

        $attachments = [];
        foreach ( $notifications as $notification ) {
            $send = $notification->matchesScheduleForAdmin( $schedule, $schedule->getService() );
            if ( $send ) {
                static::sendToAdmins( $notification, $codes, $attachments, $reply_to, $queue );
                static::sendToCustom( $notification, $codes, $attachments, $reply_to, $queue );
            }
        }

        WPML::restoreLang();
    }
}