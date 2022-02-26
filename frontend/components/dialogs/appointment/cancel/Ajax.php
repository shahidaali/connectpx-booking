<?php
namespace ConnectpxBooking\Frontend\Components\Dialogs\Appointment\Cancel;

use ConnectpxBooking\Lib;

/**
 * Class Ajax
 * @package ConnectpxBooking\Frontend\Components\Dialogs\Appointment\Cancel
 */
class Ajax extends Lib\Base\Ajax
{
    /** @var Lib\Entities\Customer */
    protected static $customer;

    /**
     * @inheritDoc
     */
    protected static function permissions()
    {
        return array( '_default' => 'customer', );
    }

    /**
     * Cancel appointment
     */
    public static function customerCancelAppointment()
    {
        $id = self::parameter( 'id' );

        $appointment = Lib\Entities\Appointment::find( $id );
        if ( $appointment->getCustomerId() == self::$customer->getId() ) {
            $allow_cancel_time = strtotime( $appointment->getPickupDateTime() ) - (int) Lib\Config::getMinimumTimePriorCancel( $appointment->getServiceId() );
            if ( $appointment->getPickupDateTime() === null || current_time( 'timestamp' ) <= $allow_cancel_time ) {
                $appointment->cancel( self::parameter( 'reason', '' ) );

                wp_send_json_success();
            }
        }

        wp_send_json_error();
    }

    /**
     * @inheritDoc
     */
    protected static function hasAccess( $action )
    {
        if ( parent::hasAccess( $action ) ) {
            self::$customer = Lib\Entities\Customer::query()->where( 'wp_user_id', get_current_user_id() )->findOne();

            return self::$customer->isLoaded();
        }

        return false;
    }
}