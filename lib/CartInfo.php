<?php
namespace ConnectpxBooking\Lib;

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

        foreach ( $userData->cart->getItems() as $key => $item ) {

            // Cart contains a service that was already removed/deleted from ConnectpxBooking (WooCommerce)
            if ( $item->getService() ) {
                $item_price = $item->getServicePrice();
                $this->subtotal += $item_price;
            }
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