<?php
namespace ConnectpxBooking\Lib\Notifications\Assets\Item;

use ConnectpxBooking\Lib;
use ConnectpxBooking\Lib\Entities\Notification;
use ConnectpxBooking\Lib\Notifications\Assets\Order;

/**
 * Class Attachments
 * @package ConnectpxBooking\Lib\Notifications\Assets\Item
 *
 * @property Codes $codes
 */
class Attachments extends Order\Attachments
{
    /**
     * @inheritDoc
     */
    public function createFor( Notification $notification )
    {
        $result = array();

        if ( $notification->getAttachIcs() ) {
            if ( ! isset( $this->files['ics'] ) ) {
                // ICS.
                if ( $this->codes instanceof \ConnectpxBookingPro\Lib\Notifications\Assets\Combined\Codes ) {
                    $ics = Proxy\Pro::createICS( $this->codes );
                } elseif ( $this->codes->getItem()->isSeries() && Lib\Config::recurringAppointmentsActive() ) {
                    $ics = Proxy\RecurringAppointments::createICS( $this->codes );
                } else {
                    $ics = new ICS( $this->codes );
                }
                $file = $ics->create();
                if ( $file ) {
                    $this->files['ics'] = $file;
                }
            }
            $result = isset( $this->files['ics'] ) ? array( $this->files['ics'] ) : array();
        }

        return array_merge( parent::createFor( $notification ), $result );
    }
}