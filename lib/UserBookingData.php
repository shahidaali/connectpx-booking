<?php
namespace ConnectpxBooking\Lib;

use ConnectpxBooking\Lib;

/**
 * Class UserBookingData
 * @package ConnectpxBooking\Frontend\Modules\Booking\Lib
 */
class UserBookingData
{
    // Protected properties
    protected $active_step = 'service';

    // Step 0
    /** @var string */
    protected $time_zone;
    /** @var int */
    protected $time_zone_offset;

    // Step service
    /** @var int */
    protected $service_id;
    /** @var string */
    protected $sub_service_key;
    /** @var string Y-m-d */
    protected $date_from;

    // Step time
    protected $slots = array();

    // Step details
    /** @var string */
    protected $full_name;
    /** @var string */
    protected $first_name;
    /** @var string */
    protected $last_name;
    /** @var string */
    protected $email;
    /** @var string */
    protected $country;
    /** @var string */
    protected $state;
    /** @var string */
    protected $postcode;
    /** @var string */
    protected $city;
    /** @var string */
    protected $street;
    /** @var string */
    protected $street_number;
    /** @var string */
    protected $additional_address;
    /** @var string */
    protected $phone;
    /** @var  string */
    protected $notes;
    /** @var array for WC checkout */
    protected $address_iso = array();

    // Cart item keys being edited
    /** @var array */
    protected $edit_cart_keys = array();
    /** @var bool */
    protected $repeated = 0;
    /** @var array */
    protected $repeat_data = array();

    /** @var string */
    protected $order_id;

    /** @var string */
    protected $is_contract_customer = false;

    /** @var string */
    protected $route_distance;
    protected $route_time;
    protected $pickup_patient_name;
    protected $pickup_room_no;
    protected $pickup_contact_person;
    protected $pickup_contact_no;
    protected $pickup_address;
    protected $destination_hospital;
    protected $destination_contact_no;
    protected $destination_dr_name;
    protected $destination_dr_contact_no;
    protected $destination_room_no;
    protected $destination_address;

    // Private

    // Frontend expect variables
    private $properties = array(
        // Step 0
        'active_step',
        'time_zone',
        'time_zone_offset',
        // Step service
        'service_id',
        'sub_service_key',
        'date_from',
        // Step time
        'slots',
        // Step details
        'full_name',
        'first_name',
        'last_name',
        'email',
        'phone',
        'additional_address',
        'country',
        'state',
        'postcode',
        'city',
        'street',
        'street_number',
        'address_iso',
        'notes',
        // Cart item keys being edited
        'edit_cart_keys',
        'repeated',
        'repeat_data',
        // Route Details
        'route_distance',
        'route_time',
        'pickup_patient_name',
        'pickup_room_no',
        'pickup_contact_person',
        'pickup_contact_no',
        'pickup_address',
        'destination_hospital',
        'destination_contact_no',
        'destination_dr_name',
        'destination_dr_contact_no',
        'destination_room_no',
        'destination_address',
    );

    /** @var Entities\Customer */
    private $customer;
    /** @var integer|null */
    private $payment_id;
    /** @var string */
    private $payment_type = Entities\Payment::TYPE_LOCAL;

    // Public

    /** @var Cart */
    public $cart;

    /**
     * Constructor.
     *
     * @param
     */
    public function __construct()
    {
        $this->cart    = new Cart( $this );
        $customer = new Entities\Customer();

        // If logged in then set name, email and if existing customer then also phone.
        $current_user = wp_get_current_user();
        if ( $current_user && $current_user->ID ) {
            if ( $customer->loadBy( array( 'wp_user_id' => $current_user->ID ) ) ) {
                $this
                    ->setFullName( $customer->getFullName() )
                    ->setFirstName( $customer->getFirstName() )
                    ->setLastName( $customer->getLastName() )
                    ->setEmail( $customer->getEmail() )
                    ->setPhone( $customer->getPhone() )
                    ->setCountry( $customer->getCountry() )
                    ->setState( $customer->getState() )
                    ->setPostcode( $customer->getPostcode() )
                    ->setCity( $customer->getCity() )
                    ->setStreet( $customer->getStreet() )
                    ->setStreetNumber( $customer->getStreetNumber() )
                    ->setAdditionalAddress( $customer->getAdditionalAddress() )
                    ->setIsContractCustomer( true );
                ;
            } else {
                $this
                    ->setFullName( $current_user->display_name )
                    ->setFirstName( $current_user->user_firstname )
                    ->setLastName( $current_user->user_lastname )
                    ->setEmail( $current_user->user_email )
                    ->setIsContractCustomer( false );
            }
        } else {
            $this->setIsContractCustomer( false );
        }
    }

    /**
     * Save data to session.
     */
    public function sessionSave()
    {
        Utils\Session::set( 'userdata', $this->getData() );
        Utils\Session::set( 'cart', $this->cart->getItemsData() );
    }

    /**
     * @return array
     */
    public function getData()
    {
        $data = array();
        foreach ( $this->properties as $variable_name ) {
            $data[ $variable_name ] = $this->{$variable_name};
        }

        return $data;
    }

    /**
     * Load data from session.
     *
     * @return bool
     */
    public function load()
    {
        $userdata = Utils\Session::get( 'userdata' );
        if ( $userdata !== null ) {
            // Restore data.
            $this->fillData( $userdata );
            $this->cart->setItemsData( Utils\Session::get( 'cart' ) );
            $this->applyTimeZone();

            return true;
        }

        return false;
    }

    /**
     * Partially update data in session.
     *
     * @param array $data
     */
    public function fillData( array $data )
    {
        foreach ( $data as $name => $value ) {
            if ( in_array( $name, $this->properties ) ) {
                $this->{$name} = $value;
            } elseif ( $name == 'cart' ) {
                foreach ( $value as $key => $_data ) {
                    $this->cart->get( $key )
                        ->setData( $_data );
                }
            } elseif ( $name === 'repeat' ) {
                if( !$this->getRepeatData() ) {
                    $this->setRepeated( 0 );
                } else {
                    $this->setRepeated( $value );
                }
            } elseif ( $name === 'unrepeat' ) {
                $this
                    ->setRepeated( 0 )
                    ->setRepeatData( array() );
            }
        }
    }

    /**
     * Add slot items to cart.
     *
     * @return $this
     */
    public function addSlotsToCart()
    {
        $cart_items     = array();
        $edit_cart_keys = $this->getEditCartKeys();
        $slots          = $this->getSlots();
        foreach ($slots as $key => $slot) {
            $cart_item = new CartItem();

            $cart_item
                ->setDateFrom( $this->getDateFrom() )
                ->setServiceId( $this->getServiceId() )
                ->setSubServiceKey( $this->getSubServiceKey() )
                ->setSubServiceData( $this->getSubServiceData() )
                ->setRouteDistance( $this->getRouteDistance() )
                ->setRouteTime( $this->getRouteTime() )
                ->setSlot( $slot );

            $cart_items[] = $cart_item;
        }

        $count = count( $edit_cart_keys );
        $inserted_keys = array();

        if ( $count ) {
            $replace_key = array_shift( $edit_cart_keys );
            foreach ( $edit_cart_keys as $key ) {
                $this->cart->drop( $key );
            }
            $inserted_keys = $this->cart->replace( $replace_key, $cart_items );
        } else {
            foreach ( $cart_items as $cart_item ) {
                $inserted_keys[] = $this->cart->add( $cart_item );
            }
        }

        $this->setEditCartKeys( $inserted_keys );

        return $this;
    }

    /**
     * Validate fields.
     *
     * @param $data
     * @return array
     */
    public function validate( $data )
    {
        $validator = new Validator();
        foreach ( $data as $field_name => $field_value ) {
            switch ( $field_name ) {
                case 'service_id':
                    $validator->validateNumber( $field_name, $field_value );
                    break;
                case 'date_from':
                    $validator->validateDate( $field_name, $field_value, true );
                    break;
                case 'pickup_time':
                case 'return_pickup_time':
                    $validator->validateTime( $field_name, $field_value, false );
                    break;
                case 'pickup_patient_name':
                case 'pickup_room_no':
                case 'pickup_contact_person':
                case 'pickup_contact_no':
                    $validator->validateRequired( $field_name, $field_value, true );
                    break;
                case 'destination_hospital':
                case 'destination_contact_no':
                case 'destination_room_no':
                    $validator->validateRequired( $field_name, $field_value, true );
                    break;
                case 'pickup_address':
                case 'destination_address':
                    $validator->validateRouteAddress( $field_name, $field_value, true );
                    break;
                case 'route_distance':
                    $validator->validateDistance( $field_name, $field_value, true );
                    break;
                case 'sub_service_key':
                    $validator->validateSubServices( $this->getCustomer(), $this->getService(), $field_value );
                    break;
                case 'cart':
                    $validator->validateCart( $field_value );
                    break;
                default:
            }
        }

        if( ! $this->isContractCustomer() ) {
            foreach ( $data as $field_name => $field_value ) {
                switch ( $field_name ) {
                    case 'full_name':
                    case 'first_name':
                    case 'last_name':
                        $validator->validateName( $field_name, $field_value );
                        break;
                    case 'email':
                        $validator->validateEmail( $field_name, $data );
                        break;
                    case 'country':
                    case 'state':
                    case 'postcode':
                    case 'city':
                    case 'street':
                    case 'street_number':
                    case 'additional_address':
                        $validator->validateAddress( $field_name, $field_value, true );
                        break;
                    case 'phone':
                        $validator->validatePhone( $field_name, $field_value, true );
                        break;
                    default:
                }
            }
        }

        // Post validators.
        if ( isset ( $data['phone'] ) || isset ( $data['email'] ) ) {
            // $validator->postValidateCustomer( $data, $this );
        }

        return $validator->getErrors();
    }

    /**
     * Save all data and create appointment.
     *
     * @param Entities\Payment $payment
     * @return DataHolders\Booking\Order
     */
    public function save( $wc_order = null )
    {
        // Customer.
        $customer = $this->getCustomer();

        // Overwrite only if value is not empty.
        if( ! $customer->isContractCustomer() ) {
            if ( $this->getFirstName() != '' ) {
                $customer->setFirstName( $this->getFirstName() );
            }
            if ( $this->getLastName() != '' ) {
                $customer->setLastName( $this->getLastName() );
            }
            if ( $this->getPhone() != '' ) {
                $customer->setPhone( $this->getPhone() );
            }
            if ( $this->getEmail() != '' ) {
                $customer->setEmail( trim( $this->getEmail() ) );
            }
            if ( $this->getCountry() != '' ) {
                $customer->setCountry( $this->getCountry() );
            }
            if ( $this->getState() != '' ) {
                $customer->setState( $this->getState() );
            }
            if ( $this->getPostcode() != '' ) {
                $customer->setPostcode( $this->getPostcode() );
            }
            if ( $this->getCity() != '' ) {
                $customer->setCity( $this->getCity() );
            }
            if ( $this->getStreet() != '' ) {
                $customer->setStreet( $this->getStreet() );
            }
            if ( $this->getStreetNumber() != '' ) {
                $customer->setStreetNumber( $this->getStreetNumber() );
            }
            if ( $this->getAdditionalAddress() != '' ) {
                $customer->setAdditionalAddress( $this->getAdditionalAddress() );
            }

            $customer->save();
        }

        return $this->cart->save( $wc_order, $this->getTimeZone(), $this->getTimeZoneOffset() );
    }

    /**
     * Get array with address iso codes.
     *
     * @return array
     */
    public function getAddressIso()
    {
        return $this->address_iso;
    }

    /**
     * Get customer.
     *
     * @return Entities\Customer
     */
    public function getCustomer()
    {
        if ( $this->customer === null ) {
            // Find or create customer.
            $this->customer = new Entities\Customer();
            $user_id = get_current_user_id();
            if ( $user_id > 0 ) {
                // Try to find customer by WP user ID.
                $this->customer->loadBy( array( 'wp_user_id' => $user_id ) );
            }
            if ( ! $this->customer->isLoaded() ) {
                if ( ! $this->customer->isLoaded() ) {
                    // Try to find customer by phone or email.
                    $params = Config::phoneRequired()
                        ? ( $this->getPhone() ? array( 'phone' => $this->getPhone() ) : array() )
                        : ( $this->getEmail() ? array( 'email' => $this->getEmail() ) : array() );
                    if ( ! empty ( $params ) && ! $this->customer->loadBy( $params ) ) {
                        $params = Config::phoneRequired()
                            ? ( $this->getEmail() ? array( 'email' => $this->getEmail(), 'phone' => '' ) : array() )
                            : ( $this->getPhone() ? array( 'phone' => $this->getPhone(), 'email' => '' ) : array() );
                        if ( ! empty( $params ) ) {
                            // Try to find customer by 'secondary' identifier, otherwise return new customer.
                            $this->customer->loadBy( $params );
                        }
                    }
                }
            }
        }

        return $this->customer;
    }

    /**
     * Set payment ( PayPal, 2Checkout, PayU Latam, Mollie ) transaction status.
     *
     * @param string $gateway
     * @param string $status
     * @param mixed  $data
     * @return $this
     * @todo use $status as const
     */
    public function setPaymentStatus( $gateway, $status, $data = null )
    {
        Utils\Session::setFormVar( $this->form_id, 'payment', compact( 'gateway', 'status', 'data' ) );

        return $this;
    }

    /**
     * @param string $gateway
     * @param string $status
     * @param null   $data
     * @return $this
     */
    public function setFailedPaymentStatus( $gateway, $status, $data = null )
    {
        $payment = new Entities\Payment();
        $payment->loadBy( array(
            'type' => $gateway,
            'id' => $this->getPaymentId(),
        ) );
        if ( $payment->isLoaded() ) {
            /** @var Entities\CustomerAppointment $ca */
            foreach ( Entities\CustomerAppointment::query()->where( 'payment_id', $payment->getId() )->find() as $ca ) {
                Utils\Log::deleteEntity( $ca, __METHOD__ );
                $ca->deleteCascade();
            }
            $payment->delete();
        }
        foreach ( $this->cart->getItems() as $cart_item ) {
            // Appointment was deleted
            $cart_item->setAppointmentId( null );
        }

        return $this->setPaymentStatus( $gateway, $status, $data );
    }

    /**
     * Get and clear ( PayPal, 2Checkout, PayU Latam, Payson ) transaction status.
     *
     * @return array|false
     */
    public function extractPaymentStatus()
    {
        if ( $status = Utils\Session::getFormVar( $this->form_id, 'payment' ) ) {
            Utils\Session::destroyFormVar( $this->form_id, 'payment' );

            return $status;
        }

        return false;
    }

    /**
     * Get payment ID.
     *
     * @return int|null
     */
    public function getPaymentId()
    {
        return $this->payment_id;
    }

    /**
     * Apply client time zone.
     *
     * @return $this
     */
    public function applyTimeZone()
    {
        if ( $this->getTimeZoneOffset() !== null ) {
            Slots\TimePoint::$client_timezone_offset = - $this->getTimeZoneOffset() * MINUTE_IN_SECONDS;
            $timezone = $this->getTimeZone() ?: Utils\DateTime::formatOffset( Slots\TimePoint::$client_timezone_offset );
            Slots\DatePoint::$client_timezone = date_create( $timezone ) === false ? null : $timezone;
        }

        return $this;
    }

    /**************************************************************************
     * UserData Getters & Setters                                             *
     **************************************************************************/


    /**
     * Gets time_zone
     *
     * @return string
     */
    public function getTimeZone()
    {
        return $this->time_zone;
    }

    /**
     * Sets time_zone
     *
     * @param string $time_zone
     * @return $this
     */
    public function setTimeZone( $time_zone )
    {
        $this->time_zone = $time_zone;

        return $this;
    }

    /**
     * Gets time_zone_offset
     *
     * @return int
     */
    public function getTimeZoneOffset()
    {
        return $this->time_zone_offset;
    }

    /**
     * Sets time_zone_offset
     *
     * @param int $time_zone_offset
     * @return $this
     */
    public function setTimeZoneOffset( $time_zone_offset )
    {
        $this->time_zone_offset = $time_zone_offset;

        return $this;
    }

    /**
     * Gets date_from
     *
     * @return string
     */
    public function getDateFrom()
    {
        return $this->date_from;
    }

    /**
     * Sets date_from
     *
     * @param string $date_from
     * @return $this
     */
    public function setDateFrom( $date_from )
    {
        $this->date_from = $date_from;

        return $this;
    }

    /**
     * Gets pickup_time
     *
     * @return string|null
     */
    public function getPickupTime()
    {
        return $this->pickup_time;
    }

    /**
     * Sets pickup_time
     *
     * @param string|null $pickup_time
     * @return $this
     */
    public function setPickupTime( $pickup_time )
    {
        $this->pickup_time = $pickup_time;

        return $this;
    }

    /**
     * Gets return_pickup_time
     *
     * @return string|null
     */
    public function getReturnPickupTime()
    {
        return $this->return_pickup_time;
    }

    /**
     * Sets return_pickup_time
     *
     * @param string|null $return_pickup_time
     * @return $this
     */
    public function setReturnPickupTime( $return_pickup_time )
    {
        $this->return_pickup_time = $return_pickup_time;

        return $this;
    }

    /**
     * Gets slots
     *
     * @return array
     */
    public function getSlots()
    {
        return $this->slots;
    }

    /**
     * Sets slots
     *
     * @param array $slots
     * @return $this
     */
    public function setSlots( $slots )
    {
        $this->slots = $slots;

        return $this;
    }

    /**
     * Gets full_name
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->full_name;
    }

    /**
     * Sets full_name
     *
     * @param string $full_name
     * @return $this
     */
    public function setFullName( $full_name )
    {
        $this->full_name = $full_name;

        return $this;
    }

    /**
     * Gets first_name
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Sets first_name
     *
     * @param string $first_name
     * @return $this
     */
    public function setFirstName( $first_name )
    {
        $this->first_name = $first_name;

        return $this;
    }

    /**
     * Gets last_name
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * Sets last_name
     *
     * @param string $last_name
     * @return $this
     */
    public function setLastName( $last_name )
    {
        $this->last_name = $last_name;

        return $this;
    }

    /**
     * Gets email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Sets email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail( $email )
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     * @return $this
     */
    public function setCountry( $country )
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     * @return $this
     */
    public function setState( $state )
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return string
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * @param string $postcode
     * @return $this
     */
    public function setPostcode( $postcode )
    {
        $this->postcode = $postcode;

        return $this;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     * @return $this
     */
    public function setCity( $city )
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param string $street
     * @return $this
     */
    public function setStreet( $street )
    {
        $this->street = $street;

        return $this;
    }

    /**
     * @return string
     */
    public function getStreetNumber()
    {
        return $this->street_number;
    }

    /**
     * @param string $street_number
     * @return $this
     */
    public function setStreetNumber( $street_number )
    {
        $this->street_number = $street_number;

        return $this;
    }

    /**
     * @return string
     */
    public function getAdditionalAddress()
    {
        return $this->additional_address;
    }

    /**
     * @param string $additional_address
     * @return $this
     */
    public function setAdditionalAddress( $additional_address )
    {
        $this->additional_address = $additional_address;

        return $this;
    }

    /**
     * Gets phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Sets phone
     *
     * @param string $phone
     * @return $this
     */
    public function setPhone( $phone )
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @param string $field_name
     * @return string
     */
    public function getAddressField( $field_name )
    {
        switch ( $field_name ) {
            case 'additional_address':
                return $this->additional_address;
            case 'country':
                return $this->country;
            case 'state':
                return $this->state;
            case 'postcode':
                return $this->postcode;
            case 'city':
                return $this->city;
            case 'street':
                return $this->street;
            case 'street_number':
                return $this->street_number;
        }

        return '';
    }

    /**
     * @return string
     */
    public function getFormatedAddress()
    {
        return Utils\Common::getFullAddressByCustomerData( $this->getAddress() );
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return array(
            'country'            => $this->getCountry(),
            'state'              => $this->getState(),
            'postcode'           => $this->getPostcode(),
            'city'               => $this->getCity(),
            'street'             => $this->getStreet(),
            'street_number'      => $this->getStreetNumber(),
            'additional_address' => $this->getAdditionalAddress(),
        );
    }

    /**
     * Gets service_id
     *
     * @return array
     */
    public function getService()
    {
        return Entities\Service::find( $this->service_id );
    }

    /**
     * Gets service_id
     *
     * @return array
     */
    public function getServiceId()
    {
        return $this->service_id;
    }

    /**
     * Sets sub_services
     *
     * @param array $service_id
     * @return $this
     */
    public function setServiceId( $service_id )
    {
        $this->service_id = $service_id;

        return $this;
    }

    /**
     * Sets sub_services
     *
     * @param array $service_id
     * @return $this
     */
    public function getCustomerSubServices()
    {
        return Lib\Entities\SubService::customerSubServices( 
            $this->getService(), 
            $this->getCustomer()
        );
    }

    /**
     * Sets sub_services
     *
     * @param array $service_id
     * @return $this
     */
    public function getSubService()
    {
        $subService = Lib\Entities\SubService::findSubService( 
            $this->getService(), 
            $this->getCustomer(), 
            $this->getSubServiceKey() 
        );

        return $subService;
    }

    /**
     * Sets sub_services
     *
     * @param array $service_id
     * @return $this
     */
    public function getSubServiceData()
    {
        $subService = $this->getSubService();
        return $subService ? $subService->getData() : [];
    }

    /**
     * Gets sub_service_key
     *
     * @return array
     */
    public function getSubServiceKey()
    {
        return $this->sub_service_key;
    }

    /**
     * Sets sub_services
     *
     * @param array $sub_service_key
     * @return $this
     */
    public function setSubServiceKey( $sub_service_key )
    {
        $this->sub_service_key = $sub_service_key;

        return $this;
    }

    /**
     * Gets notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Sets notes
     *
     * @param string $notes
     * @return $this
     */
    public function setNotes( $notes )
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Gets edit_cart_keys
     *
     * @return array
     */
    public function getEditCartKeys()
    {
        return $this->edit_cart_keys;
    }

    /**
     * Sets edit_cart_keys
     *
     * @param array $edit_cart_keys
     * @return $this
     */
    public function setEditCartKeys( $edit_cart_keys )
    {
        $this->edit_cart_keys = $edit_cart_keys;

        return $this;
    }

    /**
     * Gets repeated
     *
     * @return bool
     */
    public function getRepeated()
    {
        return $this->repeated;
    }

    /**
     * Sets repeated
     *
     * @param bool $repeated
     * @return $this
     */
    public function setRepeated( $repeated )
    {
        $this->repeated = $repeated;

        return $this;
    }

    /**
     * Gets repeat_data
     *
     * @return array
     */
    public function getRepeatData()
    {
        return $this->repeat_data;
    }

    /**
     * Sets repeat_data
     *
     * @param array $repeat_data
     * @return $this
     */
    public function setRepeatData( $repeat_data )
    {
        $this->repeat_data = $repeat_data;

        return $this;
    }

    /**
     * Gets payment_type
     *
     * @return string
     */
    public function getPaymentType()
    {
        return $this->payment_type;
    }

    /**
     * Sets payment_type
     *
     * @param string $payment_type
     * @return $this
     */
    public function setPaymentType( $payment_type )
    {
        $this->payment_type = $payment_type;

        return $this;
    }

    /**
     * Gets order_id
     *
     * @return string
     */
    public function getOrderId()
    {
        return $this->order_id;
    }

    /**
     * Sets order_id
     *
     * @param string $order_id
     * @return $this
     */
    public function setOrderId( $order_id )
    {
        $this->order_id = $order_id;

        return $this;
    }

    /**
     * Gets active_step
     *
     * @return string
     */
    public function getActiveStep()
    {
        return $this->active_step;
    }

    /**
     * Sets active_step
     *
     * @param string $active_step
     * @return $this
     */
    public function setActiveStep( $active_step )
    {
        $this->active_step = $active_step;

        return $this;
    }

    /**
     * Sets customer
     *
     * @param string $customer
     * @return $this
     */
    public function setCustomer( $customer )
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Gets is_contract_customer
     *
     * @return string
     */
    public function isContractCustomer()
    {
        return $this->is_contract_customer;
    }

    /**
     * Sets is_contract_customer
     *
     * @param string $is_contract_customer
     * @return $this
     */
    public function setIsContractCustomer( $is_contract_customer )
    {
        $this->is_contract_customer = $is_contract_customer;

        return $this;
    }

    /**
     * Gets route_distance
     *
     * @return string
     */
    public function getRouteDistance()
    {
        return $this->route_distance;
    }

    /**
     * Gets route_distance
     *
     * @return string
     */
    public function getDistanceInMiles()
    {
        return Lib\Utils\Common::getDistanceInMiles($this->route_distance);
    }

    /**
     * Sets route_distance
     *
     * @param string $route_distance
     * @return $this
     */
    public function setRouteDistance( $route_distance )
    {
        $this->route_distance = $route_distance;

        return $this;
    }

    /**
     * Gets route_time
     *
     * @return string
     */
    public function getRouteTime()
    {
        return $this->route_time;
    }

    /**
     * Sets route_time
     *
     * @param string $route_time
     * @return $this
     */
    public function setRouteTime( $route_time )
    {
        $this->route_time = $route_time;

        return $this;
    }

    /**
     * Gets pickup_patient_name
     *
     * @return string
     */
    public function getPickupPatientName()
    {
        return $this->pickup_patient_name;
    }

    /**
     * Sets pickup_patient_name
     *
     * @param string $pickup_patient_name
     * @return $this
     */
    public function setPickupPatientName( $pickup_patient_name )
    {
        $this->pickup_patient_name = $pickup_patient_name;

        return $this;
    }

    /**
     * Gets pickup_room_no
     *
     * @return string
     */
    public function getPickupRoomNo()
    {
        return $this->pickup_room_no;
    }

    /**
     * Sets pickup_room_no
     *
     * @param string $pickup_room_no
     * @return $this
     */
    public function setPickupRoomNo( $pickup_room_no )
    {
        $this->pickup_room_no = $pickup_room_no;

        return $this;
    }

    /**
     * Gets pickup_contact_person
     *
     * @return string
     */
    public function getPickupContactPerson()
    {
        return $this->pickup_contact_person;
    }

    /**
     * Sets pickup_contact_person
     *
     * @param string $pickup_contact_person
     * @return $this
     */
    public function setPickupContactPerson( $pickup_contact_person )
    {
        $this->pickup_contact_person = $pickup_contact_person;

        return $this;
    }

    /**
     * Gets pickup_contact_no
     *
     * @return string
     */
    public function getPickupContactNo()
    {
        return $this->pickup_contact_no;
    }

    /**
     * Sets pickup_contact_no
     *
     * @param string $pickup_contact_no
     * @return $this
     */
    public function setPickupContactNo( $pickup_contact_no )
    {
        $this->pickup_contact_no = $pickup_contact_no;

        return $this;
    }

    /**
     * Gets pickup_address
     *
     * @return string
     */
    public function getPickupAddress()
    {
        return $this->pickup_address;
    }

    /**
     * Sets pickup_address
     *
     * @param string $pickup_address
     * @return $this
     */
    public function setPickupAddress( $pickup_address )
    {
        $this->pickup_address = $pickup_address;

        return $this;
    }

    /**
     * Gets destination_hospital
     *
     * @return string
     */
    public function getDestinationHospital()
    {
        return $this->destination_hospital;
    }

    /**
     * Sets destination_hospital
     *
     * @param string $destination_hospital
     * @return $this
     */
    public function setDestinationHospital( $destination_hospital )
    {
        $this->destination_hospital = $destination_hospital;

        return $this;
    }

    /**
     * Gets destination_contact_no
     *
     * @return string
     */
    public function getDestinationContactNo()
    {
        return $this->destination_contact_no;
    }

    /**
     * Sets destination_contact_no
     *
     * @param string $destination_contact_no
     * @return $this
     */
    public function setDestinationContactNo( $destination_contact_no )
    {
        $this->destination_contact_no = $destination_contact_no;

        return $this;
    }

    /**
     * Gets destination_dr_name
     *
     * @return string
     */
    public function getDestinationDrName()
    {
        return $this->destination_dr_name;
    }

    /**
     * Sets destination_dr_name
     *
     * @param string $destination_dr_name
     * @return $this
     */
    public function setDestinationDrName( $destination_dr_name )
    {
        $this->destination_dr_name = $destination_dr_name;

        return $this;
    }

    /**
     * Gets destination_dr_contact_no
     *
     * @return string
     */
    public function getDestinationDrContactNo()
    {
        return $this->destination_dr_contact_no;
    }

    /**
     * Sets destination_dr_contact_no
     *
     * @param string $destination_dr_contact_no
     * @return $this
     */
    public function setDestinationDrContactNo( $destination_dr_contact_no )
    {
        $this->destination_dr_contact_no = $destination_dr_contact_no;

        return $this;
    }

    /**
     * Gets destination_room_no
     *
     * @return string
     */
    public function getDestinationRoomNo()
    {
        return $this->destination_room_no;
    }

    /**
     * Sets destination_room_no
     *
     * @param string $destination_room_no
     * @return $this
     */
    public function setDestinationRoomNo( $destination_room_no )
    {
        $this->destination_room_no = $destination_room_no;

        return $this;
    }

    /**
     * Gets destination_address
     *
     * @return string
     */
    public function getDestinationAddress()
    {
        return $this->destination_address;
    }

    /**
     * Sets destination_address
     *
     * @param string $destination_address
     * @return $this
     */
    public function setDestinationAddress( $destination_address )
    {
        $this->destination_address = $destination_address;

        return $this;
    }

    /**
     * Gets pickup_address
     *
     * @return string
     */
    public function getPickupDetail()
    {
        return [
            'patient_name' => $this->getPickupPatientName(),
            'room_no' => $this->getPickupRoomNo(),
            'contact_person' => $this->getPickupContactPerson(),
            'contact_no' => $this->getPickupContactNo(),
            'address' => Lib\Utils\Common::mergeFromCustomerAddress( json_decode( $this->getPickupAddress(), true ), $this->getAddress() ),
        ];
    }

    /**
     * Gets pickup_address
     *
     * @return string
     */
    public function getDestinationDetail()
    {
        return [
            'hospital' => $this->getDestinationHospital(),
            'contact_no' => $this->getDestinationContactNo(),
            'dr_name' => $this->getDestinationDrName(),
            'dr_contact_no' => $this->getDestinationDrContactNo(),
            'room_no' => $this->getDestinationRoomNo(),
            'address' => json_decode( $this->getDestinationAddress(), true ),
        ];
    }
}