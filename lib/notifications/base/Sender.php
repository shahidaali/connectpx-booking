<?php
namespace ConnectpxBooking\Lib\Notifications\Base;

use ConnectpxBooking\Lib\Config;
use ConnectpxBooking\Lib\Entities\Notification;

/**
 * Class Sender
 * @package ConnectpxBooking\Lib\Notifications\Base
 */
abstract class Sender extends Reminder
{
    /**
     * Get instant notifications of given type.
     *
     * @param string $type
     * @return array
     */
    protected static function getNotifications( $type )
    {
        $result = array(
            'client' => array(),
            'admin' => array(),
        );

        $query = Notification::query( 'n' )
            ->where( 'n.type', $type )
            ->where( 'n.active', '1' )
        ;
        $notifications = array( 'email' => Notification::getTypes( 'email' ) );

        /** @var Notification $notification */
        foreach ( $query->find() as $notification ) {
            if ( in_array( $notification->getType(), $notifications[ $notification->getGateway() ] ) ) {
                $settings = $notification->getSettingsObject();
                if ( $settings->getInstant() ) {
                    if ( $notification->getToCustomer() ) {
                        $result['client'][] = $notification;
                    }
                    if ( $notification->getToAdmin() ) {
                        $result['admin'][] = $notification;
                    }
                }
            }
        }

        return $result;
    }
}