<?php
namespace ConnectpxBooking\Lib\Notifications\Invoice;

use ConnectpxBooking\Lib\Config;
use ConnectpxBooking\Lib\Entities\Invoice;
use ConnectpxBooking\Lib\Entities\Notification;
use ConnectpxBooking\Lib\Notifications\Base;
use ConnectpxBooking\Lib\Notifications\WPML;
use ConnectpxBooking\Lib\Notifications\Assets\Invoice\Attachments;
use ConnectpxBooking\Lib\Notifications\Assets\Invoice\Codes;

/**
 * Class Sender
 * @package ConnectpxBooking\Lib\Notifications\Instant\Backend
 */
abstract class Sender extends Base\Sender
{
    /**
     * Send notifications.
     *
     * @param Item       $invoice
     * @param array      $codes_data
     * @param bool       $force_new_booking
     * @param bool|array $queue
     */
    public static function send( Invoice $invoice, $codes_data = array(), $force_new_booking = false, &$queue = false )
    {
        $codes = new Codes( $invoice );
   
        $notifications = static::getNotifications( Notification::TYPE_NEW_INVOICE );

        // Notify client.
        self::notifyClient( $notifications['client'], $invoice, $codes, $queue );

        // Notify staff and admins.
        self::notifyStaffAndAdmins( $notifications['admin'], $invoice, $codes, $queue );
    }

    /**
     * Notify client.
     *
     * @param Notification[] $notifications
     * @param Invoice           $invoice
     * @param Order          $order
     * @param Codes          $codes
     * @param bool|array     $queue
     */
    protected static function notifyClient( array $notifications, Invoice $invoice, Codes $codes, &$queue = false )
    {
        $codes->prepareForInvoice( $invoice, 'client' );
        $attachments = new Attachments( $codes );

        foreach ( $notifications as $notification ) {
            static::sendToClient( $invoice->getCustomer(), $notification, $codes, $attachments, $queue );
        }

        if ( $queue === false ) {
            $attachments->clear();
        }
    }

    /**
     * Notify staff and/or administrators.
     *
     * @param Notification[] $notifications
     * @param Invoice           $invoice
     * @param Order          $order
     * @param Codes          $codes
     * @param array|bool     $queue
     */
    protected static function notifyStaffAndAdmins( array $notifications, Invoice $invoice, Codes $codes, &$queue = false )
    {
        WPML::switchToDefaultLang();

        // Reply to customer.
        $reply_to = null;

        $codes->prepareForInvoice( $invoice, 'staff' );
        $attachments = new Attachments( $codes );
        foreach ( $notifications as $notification ) {
            static::sendToAdmins( $notification, $codes, $attachments, $reply_to, $queue );
            static::sendToCustom( $notification, $codes, $attachments, $reply_to, $queue );
        }
        if ( $queue === false ) {
            $attachments->clear();
        }

        WPML::restoreLang();
    }
}