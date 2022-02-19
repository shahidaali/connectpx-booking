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
    /** @var UserBookingData $userData */
    protected $subService;
    /** @var float  cost of services before discounts */
    protected $miles_price = 0;
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
        $this->subService = $this->userData->getSubService();

        $distanceMiles = Lib\Utils\Common::getDistanceInMiles( $this->userData->getRouteDistance() );
        $this->setMilesPrice( $this->subService->getMilesPrice( $distanceMiles ) );

        foreach ( $userData->cart->getItems() as $key => $item ) {
            // Cart contains a service that was already removed/deleted from ConnectpxBooking (WooCommerce)
            $this->subtotal += $this->getItemPrice();
        }

        $this->total = $this->subtotal;
    }

    /**
     * @return float
     */
    public function getItemPrice()
    {
        $price = $this->subService->getFlatRate();
        $price += $this->getMilesPrice();

        return $price;
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

    /**
     * Get total price.
     *
     * @return float
     */
    public function getMilesPrice()
    {
        return $this->miles_price;
    }

    /**
     * Get total price.
     *
     * @return float
     */
    public function setMilesPrice( $miles_price )
    {
        $this->miles_price = $miles_price;

        return $miles_price;
    }

}