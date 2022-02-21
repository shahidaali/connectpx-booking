<?php
namespace ConnectpxBooking\Lib;

use ConnectpxBooking\Lib;
use ConnectpxBooking\Lib\Entities\Appointment;
use ConnectpxBooking\Lib\Entities\Payment;

/**
 * Class Cart
 * @package ConnectpxBooking\Lib
 */
class Cart
{
    /**
     * @var CartItem[]
     */
    private $items = array();

    /**
     * @var UserBookingData
     */
    private $userData;

    /**
     * Constructor.
     *
     * @param UserBookingData $userData
     */
    public function __construct( UserBookingData $userData )
    {
        $this->userData = $userData;
    }

    /**
     * Get cart item.
     *
     * @param integer $key
     * @return CartItem|false
     */
    public function get( $key )
    {
        if ( isset ( $this->items[ $key ] ) ) {
            return $this->items[ $key ];
        }

        return false;
    }

    /**
     * Add cart item.
     *
     * @param CartItem $item
     * @return integer
     */
    public function add( CartItem $item )
    {
        $this->items[] = $item;
        end( $this->items );

        return key( $this->items );
    }

    /**
     * Replace given item with other items.
     *
     * @param integer $key
     * @param CartItem[] $items
     * @return array
     */
    public function replace( $key, array $items )
    {
        $new_items = array();
        $new_keys  = array();
        $new_key   = 0;
        foreach ( $this->items as $cart_key => $cart_item ) {
            if ( $cart_key == $key ) {
                foreach ( $items as $item ) {
                    $new_items[ $new_key ] = $item;
                    $new_keys[] = $new_key;
                    ++ $new_key;
                }
            } else {
                $new_items[ $new_key ++ ] = $cart_item;
            }
        }
        $this->items = $new_items;

        return $new_keys;
    }

    /**
     * Drop cart item.
     *
     * @param integer $key
     */
    public function drop( $key )
    {
        unset ( $this->items[ $key ] );
    }

    /**
     * Get cart items.
     *
     * @return CartItem[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Get items data as array.
     *
     * @return array
     */
    public function getItemsData()
    {
        $data = array();
        foreach ( $this->items as $key => $item ) {
            $data[ $key ] = $item->getData();
        }

        return $data;
    }

    /**
     * Set items data from array.
     *
     * @param array $data
     */
    public function setItemsData( array $data )
    {
        foreach ( $data as $key => $item_data ) {
            $item = new CartItem();
            $item->setData( $item_data );
            $this->items[ $key ] = $item;
        }
    }

    /**
     * Save all cart items (customer appointments).
     *
     * @param DataHolders\Order $order
     * @param string $time_zone
     * @param int    $time_zone_offset
     * @return DataHolders\Order
     */
    public function save( $wc_order, $time_zone, $time_zone_offset )
    {
        $customer = $this->userData->getCustomer();
        $service = $this->userData->getService();
        $subService = $this->userData->getSubService();
        $subServiceKey = $this->userData->getSubServiceKey();
        $distanceMiles = $this->userData->getDistanceInMiles();
        $pickupDetail = $this->userData->getPickupDetail();
        $destinationDetail = $this->userData->getDestinationDetail();

        $appointment_ids = [];

        foreach ( $this->getItems() as $cart_item ) {

            $slot = $cart_item->getSlot();

            list ( $date, $pickupTime, $returnTime ) = $slot;

            $returnDateTime = $returnTime ? $date . " " . $returnTime : null;
            $isOffTimeService = Lib\Utils\Common::isOffTimeService( $slot );

            $itemPrice = $subService->calculatePrice( 
                $distanceMiles,
                0,
                $isOffTimeService 
            );

            /*
             * Get appointment with the same params.
             * If it exists -> create connection to this appointment,
             * otherwise create appointment and connect customer to new appointment
             */
            $appointment = new Appointment();
            $appointment
                ->setServiceId( $service->getId() )
                ->setWcOrderId( $wc_order->get_id() )
                ->setSubServiceKey( $subServiceKey )
                ->setSubServiceData( json_encode( $subService->getData() ) )
                ->setPickupDateTime( $date . " " . $pickupTime )
                ->setReturnPickupDatetime( $returnDateTime )
                ->setCustomer( $customer )
                ->setDistance( $this->userData->getDistanceInMiles() )
                ->setIsAfterHours( $isOffTimeService )
                ->setPickupDetail( json_encode( $pickupDetail ) )
                ->setDestinationDetail( json_encode( $destinationDetail ) )
                ->setTimeZone( $time_zone )
                ->setTimeZoneOffset( $time_zone_offset )
                ->setStatus( Config::getDefaultAppointmentStatus() )
                ->setNotes( $this->userData->getNotes() )
                ->setPaymentStatus( Appointment::PAYMENT_COMPLETED )
                ->setPaymentType( Appointment::PAYMENT_TYPE_WOOCOMMERCE )
                ->setTotalAmount( $itemPrice )
                ->setCreatedAt( current_time( 'mysql' ) )
                ->save();

            $appointment_ids[] = $appointment->getId();
        }

        return $appointment_ids;
    }

    /**
     * @param string $gateway
     * @param bool   $apply_coupon
     * @return CartInfo
     */
    public function getInfo()
    {
        $cart_info = new CartInfo( $this->userData );
        return $cart_info;
    }


    /**
     * Generate title of cart items (used in payments).
     *
     * @param int  $max_length
     * @param bool $multi_byte
     * @return string
     */
    public function getItemsTitle( $max_length = 255, $multi_byte = true )
    {
        reset( $this->items );
        $title = $this->get( key( $this->items ) )->getService()->getTitle();
        $tail  = '';
        $more  = count( $this->items ) - 1;
        if ( $more > 0 ) {
            $tail = sprintf( _n( ' and %d more item', ' and %d more items', $more, 'bookly' ), $more );
        }

        if ( $multi_byte ) {
            if ( preg_match_all( '/./su', $title . $tail, $matches ) > $max_length ) {
                $length_tail = preg_match_all( '/./su', $tail, $matches );
                $title       = preg_replace( '/^(.{' . ( $max_length - $length_tail - 3 ) . '}).*/su', '$1', $title ) . '...';
            }
        } else {
            if ( strlen( $title . $tail ) > $max_length ) {
                while ( strlen( $title . $tail ) + 3 > $max_length ) {
                    $title = preg_replace( '/.$/su', '', $title );
                }
                $title .= '...';
            }
        }

        return $title . $tail;
    }

    /**
     * Return cart_key for not available appointment or NULL.
     *
     * @return int|null
     */
    public function getFailedKey()
    {
        // $max_date = Slots\DatePoint::now()
        //     ->modify( ( 1 + Config::getMaximumAvailableDaysForBooking() ) * DAY_IN_SECONDS )
        //     ->modify( '00:00:00' );

        // foreach ( $this->items as $cart_key => $cart_item ) {
        //     if ( $cart_item->getService() ) {
        //         $service = $cart_item->getService();
        //         $with_sub_services = $service->withSubServices();
        //         foreach ( $cart_item->getSlots() as $slot ) {
        //             list ( $datetime, $pickup_time, $return_pickup_time ) = $slot;
        //             if ( $datetime === null ) {
        //                 // Booking is always available for tasks.
        //                 continue;
        //             }

        //             $bound_start = Slots\DatePoint::fromStr( $datetime );
        //             $bound_end = Slots\DatePoint::fromStr( $datetime )->modify( ( (int) $service->getDuration() ) . ' sec' );

        //             if ( !$bound_end->lte( $max_date ) ) {
        //                 return $cart_key;
        //             }
        //         }
        //     }
        // }

        return null;
    }

}