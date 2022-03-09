<?php
namespace ConnectpxBooking\Lib\Notifications\Cart;

use ConnectpxBooking\Lib\Config;
use ConnectpxBooking\Lib\Entities\Notification;
use ConnectpxBooking\Lib\Entities\Appointment;
use ConnectpxBooking\Lib\Entities\Customer;
use ConnectpxBooking\Lib\Notifications\Assets\Order\Attachments;
use ConnectpxBooking\Lib\Notifications\Assets\Order\Codes;
use ConnectpxBooking\Lib\Notifications\Base;
use ConnectpxBooking\Lib\Notifications\WPML;
use WC_Order;

/**
 * Class Sender
 * @package ConnectpxBooking\Lib\Notifications\Cart
 */
abstract class Sender extends Base\Sender
{
    /**
     * Send notifications for wc_order.
     *
     * @param WC_Order $wc_order
     */
    public static function send( WC_Order $wc_order )
    {
        $customer = null;
        $appointments = self::loadAppointments( $wc_order );

        if( !empty($appointments) ) {
            $customer = $appointments[0]->getCustomer();
        }

        $codes = new Codes( $appointments, $customer );

        $notifications = static::getNotifications( Notification::TYPE_NEW_BOOKING );

        // Notify client.
        self::notifyClient( $notifications['client'], $appointments, $customer, $codes );

        // Notify staff and admins.
        static::notifyStaffAndAdmins( $notifications['admin'], $appointments, $customer, $codes );
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
    protected static function notifyClient( array $notifications, array $appointments, Customer $customer, Codes $codes, &$queue = false )
    {
        $attachments = new Attachments( $codes );

        foreach ( $notifications as $notification ) {
            static::sendToClient( $customer, $notification, $codes, $attachments, $queue );
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
    protected static function notifyStaffAndAdmins( array $notifications, array $appointments, Customer $customer, Codes $codes, &$queue = false )
    {
        WPML::switchToDefaultLang();

        // Reply to customer.
        $reply_to = null;

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

    /**
     * Send notifications for order.
     *
     * @param Order $order
     */
    public static function loadAppointments( WC_Order $wc_order )
    {
        $appointments = [];
        foreach ( $wc_order->get_items() as $item_id => $order_item ) {
            $data = wc_get_order_item_meta( $item_id, 'connectpx_booking' );
            if ( $data && isset ( $data['processed'] ) && $data['processed'] ) {
                $appointment_ids = $data['appointment_ids'] ?? [];

                $items = Appointment::query( 'a' )
                    ->select( 'a.*' )
                    ->whereIn('a.id', $appointment_ids)
                    ->order('DESC')
                    ->fetchArray();

                foreach ($items as $key => $item) {
                    $appointments[] = new Appointment($item);
                }
            }
        }
        return $appointments;
    }
}