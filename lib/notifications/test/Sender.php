<?php
namespace ConnectpxBooking\Lib\Notifications\Test;

use ConnectpxBooking\Lib\Entities\Notification;
use ConnectpxBooking\Lib\Notifications\Base;
use ConnectpxBooking\Lib\Notifications\Assets\Item\Attachments;
use ConnectpxBooking\Lib\Notifications\Assets\Test\Codes;

/**
 * Class Sender
 * @package ConnectpxBooking\Lib\Notifications\Instant
 */
abstract class Sender extends Base\Sender
{
    /**
     * Send test notification emails.
     *
     * @param string $to_email
     * @param string $sender_name
     * @param string $sender_email
     * @param string $send_as
     * @param bool $reply_to_customers
     * @param array $notification_ids
     */
    public static function send( $to_email, $sender_name, $sender_email, $send_as, $reply_to_customers, array $notification_ids )
    {
        $codes = new Codes();
        $attachments  = new Attachments( $codes );
        $notification = new Notification();

        $from = array(
            'name'  => $sender_name,
            'email' => $sender_email,
        );
        $reply_to = $reply_to_customers ? array(
            'name'  => $codes->client_name,
            'email' => $codes->client_email,
        ) : null;

        foreach ( $notification_ids as $id ) {
            $notification->loadBy( array( 'id' => $id, 'gateway' => 'email' ) );

            switch ( $notification->getType() ) {
                case Notification::TYPE_APPOINTMENT_REMINDER:
                case Notification::TYPE_NEW_BOOKING:
                case Notification::TYPE_NEW_INVOICE:
                case Notification::TYPE_APPOINTMENT_STATUS_CHANGED:
                case Notification::TYPE_CUSTOMER_NEW_WP_USER:
                    if ( $notification->getToAdmin() ) {
                        static::_sendEmailTo(
                            self::RECIPIENT_ADMINS,
                            $to_email,
                            $notification,
                            $codes,
                            $attachments,
                            $reply_to,
                            $send_as,
                            $from
                        );
                    }
                    if ( $notification->getToCustomer() ) {
                        static::_sendEmailTo(
                            self::RECIPIENT_CLIENT,
                            $to_email,
                            $notification,
                            $codes,
                            $attachments,
                            null,
                            $send_as,
                            $from
                        );
                    }
                    break;
            }
        }
    }
}