<?php
namespace ConnectpxBooking\Lib\Notifications\Verification;

use ConnectpxBooking\Lib\Entities\Customer;
use ConnectpxBooking\Lib\Entities\Notification;
use ConnectpxBooking\Lib\Notifications\Base;
use ConnectpxBooking\Lib\Notifications\Assets\Verification\Codes;

/**
 * Class Sender
 * @package ConnectpxBooking\Lib\Notifications\Verification
 */
abstract class Sender extends Base\Sender
{
    /**
     * Send email/sms with username and password for newly created WP user.
     *
     * @param Customer $customer
     * @param string $verification_code
     * @param string $identifier
     */
    public static function send( Customer $customer, $verification_code, $identifier )
    {
        $codes = new Codes( $customer, $verification_code );
        switch ( $identifier ) {
            case 'email':
                $notifications = static::getNotifications( Notification::TYPE_VERIFY_EMAIL );
                break;
            default:
                $notifications = static::getNotifications( Notification::TYPE_VERIFY_PHONE );
                break;
        }

        // Notify client.
        foreach ( $notifications['client'] as $notification ) {
            static::sendToClient( $customer, $notification, $codes );
        }
    }
}