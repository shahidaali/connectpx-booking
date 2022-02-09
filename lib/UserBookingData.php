<?php
namespace ConnectpxBooking\Lib;

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
    protected $sub_service_id;
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
    /** @var array */
    protected $sub_services = array();
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

    // Private

    // Frontend expect variables
    private $properties = array(
        // Step 0
        'active_step',
        'time_zone',
        'time_zone_offset',
        // Step service
        'service_id',
        'sub_service_id',
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
        'sub_services',
        'repeated',
        'repeat_data',
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
        // $this->cart    = new Cart( $this );

        // If logged in then set name, email and if existing customer then also phone.
        $current_user = wp_get_current_user();
        if ( $current_user && $current_user->ID ) {
            $customer = new Entities\Customer();
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
                    ->setSubServices( json_decode( $customer->getSubServices(), true ) )
                ;
            } else {
                $this
                    ->setFullName( $current_user->display_name )
                    ->setFirstName( $current_user->user_firstname )
                    ->setLastName( $current_user->user_lastname )
                    ->setEmail( $current_user->user_email );
            }
        }
    }

    /**
     * Save data to session.
     */
    public function sessionSave()
    {
        Utils\Session::set( 'userdata', $this->getData() );
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
                    if ( array_key_exists( $field_name, Proxy\Pro::getDisplayedAddressFields() ) ) {
                        $validator->validateAddress( $field_name, $field_value, Config::addressRequired() );
                    }
                    break;
                case 'phone':
                    $validator->validatePhone( $field_name, $field_value, Config::phoneRequired() );
                    break;
                case 'sub_services':
                    $validator->validateSubServices( $field_value );
                    break;
                case 'cart':
                    $validator->validateCart( $field_value );
                    break;
                default:
            }
        }
        // Post validators.
        if ( isset ( $data['phone'] ) || isset ( $data['email'] ) ) {
            $validator->postValidateCustomer( $data, $this );
        }

        return $validator->getErrors();
    }

    /**
     * Save all data and create appointment.
     *
     * @param Entities\Payment $payment
     * @return DataHolders\Booking\Order
     */
    public function save( $payment = null )
    {
        // Customer.
        $customer = $this->getCustomer();

        // Overwrite only if value is not empty.
        if ( $this->getFacebookId() ) {
            $customer->setFacebookId( $this->getFacebookId() );
        }
        if ( $this->getFullName() != '' ) {
            $customer->setFullName( $this->getFullName() );
        }
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
        if ( $this->getBirthdayYmd() != '' ) {
            $customer->setBirthday( $this->getBirthdayYmd() );
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

        // Order.
        $order = DataHolders\Booking\Order::create( $customer );

        // Payment.
        if ( $payment ) {
            $order->setPayment( $payment );
            $this->payment_id = $payment->getId();
            $this->setPaymentType( $payment->getType() );
        }

        return $this->cart->save( $order, $this->getTimeZone(), $this->getTimeZoneOffset() );
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
     * Gets sub_services
     *
     * @return array
     */
    public function getSubServices()
    {
        return $this->sub_services;
    }

    /**
     * Sets sub_services
     *
     * @param array $sub_services
     * @return $this
     */
    public function setSubServices( $sub_services )
    {
        $this->sub_services = $sub_services;

        return $this;
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
     * Gets sub_service_id
     *
     * @return array
     */
    public function getSubServiceId()
    {
        return $this->sub_service_id;
    }

    /**
     * Sets sub_services
     *
     * @param array $sub_service_id
     * @return $this
     */
    public function setSubServiceId( $sub_service_id )
    {
        $this->sub_service_id = $sub_service_id;

        return $this;
    }

    /**
     * Gets sub_service_id
     *
     * @return array
     */
    public function isOneWay()
    {
        return $this->sub_service_id == 'oneway';
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
}