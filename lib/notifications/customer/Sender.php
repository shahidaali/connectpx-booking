<?php
namespace ConnectpxBooking\Lib\Notifications\Customer;

use ConnectpxBooking\Lib\Config;
use ConnectpxBooking\Lib\Entities\Notification;
use ConnectpxBooking\Lib\Entities\Customer;
use ConnectpxBooking\Lib\Notifications\Assets\Customer\Codes;
use ConnectpxBooking\Lib\Notifications\Base;
use ConnectpxBooking\Lib\Notifications\WPML;

/**
 * Class Sender
 * @package ConnectpxBooking\Lib\Notifications\Customer
 */
abstract class Sender extends Base\Sender
{
    /**
     * Send notifications for wc_order.
     *
     * @param WC_Order $wc_order
     */
    public static function send( Customer $customer, $username = null, $password = null )
    {
        $codes = new Codes( $customer, $username, $password );

        $notifications = static::getNotifications( Notification::TYPE_CUSTOMER_NEW_WP_USER );

        // Notify client.
        self::notifyAccountInformation( $notifications['client'], $customer, $codes );
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
    protected static function notifyAccountInformation( array $notifications, Customer $customer, Codes $codes, &$queue = false )
    {
        $attachments = [];

        foreach ( $notifications as $notification ) {
            static::sendToClient( $customer, $notification, $codes, $attachments, $queue );
        }
    }
}