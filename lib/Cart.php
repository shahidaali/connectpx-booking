<?php
namespace ConnectpxBooking\Lib;

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
    public function save( DataHolders\Order $order, $time_zone, $time_zone_offset )
    {
        $item_key = 0;
        $orders_entity = new Entities\Order();
        $orders_entity
            ->setToken( Common::generateToken( get_class( $orders_entity ), 'token' ) )
            ->save();

        $this->userData->setOrderId( $orders_entity->getId() );

        foreach ( $this->getItems() as $cart_item ) {
            // Init.
            $payment_id = $order->hasPayment() ? $order->getPayment()->getId() : null;
            $service = $cart_item->getService();
            $series = null;
            $collaborative = null;
            $compound = null;

            // Whether to put this item on waiting list.
            $put_on_waiting_list = Config::waitingListActive() && get_option( 'bookly_waiting_list_enabled' ) && $cart_item->toBePutOnWaitingList();

            if ( $service->isCompound() ) {
                // Compound.
                $compound = DataHolders\Compound::create( $service )
                    ->setToken( Utils\Common::generateToken(
                        '\ConnectpxBooking\Lib\Entities\CustomerAppointment',
                        'compound_token'
                    ) );
            } elseif ( $service->isCollaborative() ) {
                // Collaborative.
                $collaborative = DataHolders\Collaborative::create( $service )
                    ->setToken( Utils\Common::generateToken(
                        '\ConnectpxBooking\Lib\Entities\CustomerAppointment',
                        'collaborative_token'
                    ) );
            }

            // Series.
            if ( $series_unique_id = $cart_item->getSeriesUniqueId() ) {
                if ( $order->hasItem( $series_unique_id ) ) {
                    $series = $order->getItem( $series_unique_id );
                } else {
                    $series_entity = new Entities\Series();
                    $series_entity
                        ->setRepeat( '{}' )
                        ->setToken( Common::generateToken( get_class( $series_entity ), 'token' ) )
                        ->save();

                    $series = DataHolders\Series::create( $series_entity );
                    $order->addItem( $series_unique_id, $series );
                }
                if ( get_option( 'bookly_recurring_appointments_payment' ) == 'first' && ! $cart_item->getFirstInSeries() ) {
                    // Link payment with the first item only.
                    $payment_id = null;
                }
            }

            $extras = $cart_item->distributeExtrasAcrossSlots();
            $custom_fields = $cart_item->getCustomFields();

            // For collaborative services we may need to find max duration to make all appointments of the same size.
            $collaborative_max_duration = null;
            $collaborative_extras_durations = array();
            if ( $collaborative && $service->getCollaborativeEqualDuration() ) {
                $consider_extras_duration = (bool) Proxy\ServiceExtras::considerDuration();
                foreach ( $cart_item->getSlots() as $key => $slot ) {
                    list ( $service_id ) = $slot;
                    $service = Entities\Service::find( $service_id );
                    $collaborative_extras_durations[ $key ] = $consider_extras_duration
                        ? (int) Proxy\ServiceExtras::getTotalDuration( $extras[ $key ] )
                        : 0;
                    $duration = $cart_item->getUnits() * $service->getDuration() + $collaborative_extras_durations[ $key ];
                    if ( $duration > $collaborative_max_duration ) {
                        $collaborative_max_duration = $duration;
                    }
                }
            }

            foreach ( $cart_item->getSlots() as $key => $slot ) {
                list ( $service_id, $staff_id, $start_datetime ) = $slot;

                $service = Entities\Service::find( $service_id );
                $item_duration = $collaborative_max_duration !== null
                    ? $collaborative_max_duration - $collaborative_extras_durations[ $key ]
                    : $cart_item->getUnits() * $service->getDuration();

                $end_datetime = $start_datetime !== null ? date( 'Y-m-d H:i:s', strtotime( $start_datetime ) + $item_duration ) : null;

                /*
                 * Get appointment with the same params.
                 * If it exists -> create connection to this appointment,
                 * otherwise create appointment and connect customer to new appointment
                 */
                $appointment = new Entities\Appointment();
                // Do not try to find appointment for tasks
                if ( $start_datetime !== null ) {
                    $appointment->loadBy( array(
                        'service_id' => $service_id,
                        'staff_id' => $staff_id,
                        'start_date' => $start_datetime,
                        'end_date' => $end_datetime,
                    ) );
                }
                if ( $appointment->isLoaded() ) {
                    $update = false;
                    if ( ! $appointment->getLocationId() && $cart_item->getLocationId() ) {
                        // Set location if it was not set previously.
                        $appointment->setLocationId( $cart_item->getLocationId() );
                        $update = true;
                    }
                    if ( $appointment->getStaffAny() == 1 && count( $cart_item->getStaffIds() ) == 1 ) {
                        // Remove marker Any for staff
                        $appointment->setStaffAny( 0 );
                        $update = true;
                    }
                    if ( $update ) {
                        $appointment->save();
                    }
                } else {
                    // Create new appointment.
                    $appointment
                        ->setLocationId( $cart_item->getLocationId() ?: null )
                        ->setServiceId( $service_id )
                        ->setStaffId( $staff_id )
                        ->setStaffAny( count( $cart_item->getStaffIds() ) > 1 )
                        ->setStartDate( $start_datetime )
                        ->setEndDate( $end_datetime )
                        ->save();
                    Log::createEntity( $appointment, __METHOD__, $order->getCustomer()->getFullName() );
                }

                // Connect appointment with the cart item.
                $cart_item->setAppointmentId( $appointment->getId() );

                if ( $compound || $collaborative ) {
                    $service_custom_fields = Proxy\CustomFields::filterForService( $custom_fields, $service_id );
                } else {
                    $service_custom_fields = $custom_fields;
                }

                // Create CustomerAppointment record.
                $customer_appointment = new Entities\CustomerAppointment();
                $customer_appointment
                    ->setSeriesId( $series ? $series->getSeries()->getId() : null )
                    ->setCustomer( $order->getCustomer() )
                    ->setAppointment( $appointment )
                    ->setPaymentId( $payment_id )
                    ->setOrderId( $orders_entity->getId() )
                    ->setNumberOfPersons( $cart_item->getNumberOfPersons() )
                    ->setUnits( $cart_item->getUnits() )
                    ->setNotes( $this->userData->getNotes() )
                    ->setExtras( json_encode( $extras[ $key ] ) )
                    ->setExtrasConsiderDuration( Proxy\ServiceExtras::considerDuration( true ) )
                    ->setCustomFields( json_encode( $service_custom_fields ) )
                    ->setStatus( $put_on_waiting_list
                        ? CustomerAppointment::STATUS_WAITLISTED
                        : Proxy\CustomerGroups::takeDefaultAppointmentStatus( Config::getDefaultAppointmentStatus(), $order->getCustomer()->getGroupId() ) )
                    ->setTimeZone( $time_zone )
                    ->setTimeZoneOffset( $time_zone_offset )
                    ->setCollaborativeServiceId( $collaborative ? $collaborative->getService()->getId() : null )
                    ->setCollaborativeToken( $collaborative ? $collaborative->getToken() : null )
                    ->setCompoundServiceId( $compound ? $compound->getService()->getId() : null )
                    ->setCompoundToken( $compound ? $compound->getToken() : null )
                    ->setCreatedFrom( 'frontend' )
                    ->setCreatedAt( current_time( 'mysql' ) )
                    ->save();
                Log::createEntity( $customer_appointment, __METHOD__, $order->getCustomer()->getFullName() );

                Proxy\Files::attachFiles( $cart_item->getCustomFields(), $customer_appointment );

                // Handle extras duration.
                if ( Proxy\ServiceExtras::considerDuration() ) {
                    $appointment
                        ->setExtrasDuration( $appointment->getMaxExtrasDuration() )
                        ->save();
                }

                // Online meeting.
                Proxy\Shared::syncOnlineMeeting( array(), $appointment, $service );
                // Google Calendar.
                Proxy\Pro::syncGoogleCalendarEvent( $appointment );
                // Outlook Calendar.
                Proxy\OutlookCalendar::syncEvent( $appointment );

                // Add entities to result.
                $item = DataHolders\Simple::create( $customer_appointment )
                    ->setService( $service )
                    ->setAppointment( $appointment );

                if ( $compound ) {
                    $item = $compound->addItem( $item );
                } elseif ( $collaborative ) {
                    $item = $collaborative->addItem( $item );
                }
                if ( count( $item->getItems() ) === 1 ) {
                    if ( $series ) {
                        $series->addItem( $item_key ++, $item );
                    } else {
                        $order->addItem( $item_key ++, $item );
                    }
                }
            }
        }

        return $order;
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