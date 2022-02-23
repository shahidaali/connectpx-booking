<?php
namespace ConnectpxBooking\Lib;

use ConnectpxBooking\Lib;

/**
 * Class CartInfo
 * @package ConnectpxBooking\Lib\Booking
 */
class CartInfo
{
    /** @var UserBookingData $userData */
    protected $userData;
    /** @var float  cost of services before discounts */
    protected $subtotal = 0;
    /** @var float  cost of services including coupon and group discount */
    protected $total = 0;

    /**
     * CartInfo constructor.
     *
     * @param bool $apply_coupon
     * @param UserBookingData $userData
     */
    public function __construct( UserBookingData $userData )
    {
        $this->userData = $userData;

        $subService = $this->userData->getSubService();
        $distanceMiles = $this->userData->getDistanceInMiles();

        foreach ( $userData->cart->getItems() as $key => $item ) {

            $isOffTimeService = Lib\Utils\Common::isOffTimeService( $item->getSlot() );

            $itemPrice = $subService->paymentLineItems( 
                $distanceMiles,
                0,
                $isOffTimeService 
            );

            // Cart contains a service that was already removed/deleted from ConnectpxBooking (WooCommerce)
            $this->subtotal += $itemPrice['totals'];
        }

        $this->total = $this->subtotal;
    }

    /**
     * @return float
     */
    public function getSubtotal()
    {
        return $this->subtotal;
    }

    /**
     * @return UserBookingData
     */
    public function getUserData()
    {
        return $this->userData;
    }


    /**************************************************************************
     * Amounts dependent on taxes                                             *
     **************************************************************************/

    /**
     * Get paying amount.
     *
     * @return float
     */
    public function getPayNow()
    {
        return $this->getTotal();
    }

    /**
     * Get total price.
     *
     * @return float
     */
    public function getTotal()
    {
        return $this->subtotal;
    }

}