<?php
namespace ConnectpxBooking\Lib\Notifications\Cart;

use ConnectpxBooking\Lib\Config;
use ConnectpxBooking\Lib\DataHolders\Booking\Order;
use ConnectpxBooking\Lib\Entities\Notification;
use ConnectpxBooking\Lib\Notifications\Assets\Item\Codes;
use ConnectpxBooking\Lib\Notifications\Booking;

/**
 * Class Sender
 * @package ConnectpxBooking\Lib\Notifications\Cart
 */
abstract class Sender extends Booking\BaseSender
{
    /**
     * Send notifications for order.
     *
     * @param Order $order
     */
    public static function send( Order $order )
    {
        if ( Config::proActive() ) {
            Proxy\Pro::sendCombinedToClient( false, $order );
        }

        $codes = new Codes( $order );

        $notifications           = static::getNotifications( Notification::TYPE_NEW_BOOKING );
        $notifications_recurring = static::getNotifications( Notification::TYPE_NEW_BOOKING_RECURRING );

        foreach ( $order->getItems() as $item ) {
            if ( $item->isSeries() ) {
                // Notify client.
                static::notifyClient( $notifications_recurring['client'], $item, $order, $codes );

                // Notify staff and admins.
                static::notifyStaffAndAdmins( $notifications_recurring['staff'], $item, $order, $codes );
            } else {
                // Notify client.
                static::notifyClient( $notifications['client'], $item, $order, $codes );

                // Notify staff and admins.
                static::notifyStaffAndAdmins( $notifications['staff'], $item, $order, $codes );
            }
        }
    }
}