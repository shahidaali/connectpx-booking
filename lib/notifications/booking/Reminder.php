<?php
namespace ConnectpxBooking\Lib\Notifications\Booking;

use ConnectpxBooking\Lib\Entities\Notification;
use ConnectpxBooking\Lib\Notifications\Assets\Item\Attachments;
use ConnectpxBooking\Lib\Notifications\Assets\Item\Codes;
use ConnectpxBooking\Lib\Notifications\Base;
use ConnectpxBooking\Lib\Notifications\WPML;

/**
 * Class Reminder
 * @package ConnectpxBooking\Lib\Notifications\Booking
 */
abstract class Reminder extends Base\Reminder
{
    /**
     * Send booking/appointment notifications.
     *
     * @param Notification $notification
     * @param Item $item
     * @return bool
     */
    public static function send( Notification $notification, Item $item )
    {
        $order = Order::createFromItem( $item );
        $codes = new Codes( $order );
        $attachments = new Attachments( $codes );

        $result = false;

        if ( $item->getCA()->getLocale() ) {
            WPML::switchLang( $item->getCA()->getLocale() );
        } else {
            WPML::switchToDefaultLang();
        }
        $codes->prepareForItem( $item, 'client' );

        // Notify client.
        if ( static::sendToClient( $order->getCustomer(), $notification, $codes, $attachments ) ) {
            $result = true;
        }

        WPML::switchToDefaultLang();
        foreach ( $item->getItems() as $i ) {
            $attachments->clear();
            $codes->prepareForItem( $i, 'staff' );

            // Reply to customer.
            $reply_to = null;

            // Notify admins.
            if ( static::sendToAdmins( $notification, $codes, $attachments, $reply_to ) ) {
                $result = true;
            }

            // Notify customs.
            if ( static::sendToCustom( $notification, $codes, $attachments, $reply_to ) ) {
                $result = true;
            }
        }
        WPML::restoreLang();

        $attachments->clear();

        return $result;
    }
}