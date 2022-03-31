<?php
namespace ConnectpxBooking\Lib\Entities;

use ConnectpxBooking\Lib;

/**
 * Class Customer
 * @package ConnectpxBooking\Lib\Entities
 */
class Customer extends Lib\Base\Entity
{
    const REMOTE_LIMIT  = 100;

    /** @var int */
    protected $wp_user_id;
    /** @var string */
    protected $first_name = '';
    /** @var string */
    protected $last_name = '';
    /** @var string */
    protected $phone = '';
    /** @var string */
    protected $email = '';
    /** @var string */
    protected $country = '';
    /** @var string */
    protected $state = '';
    /** @var string */
    protected $postcode = '';
    /** @var string */
    protected $city = '';
    /** @var string */
    protected $street = '';
    /** @var string */
    protected $street_number = '';
    /** @var string */
    protected $additional_address = '';
    /** @var string */
    protected $pickup_lat;
    /** @var string */
    protected $pickup_lng;
    /** @var string */
    protected $destination_lat;
    /** @var string */
    protected $destination_lng;
    /** @var string */
    protected $notes = '';
    /** @var string */
    protected $birthday;
    /** @var  string */
    protected $services = '[]';
    /** @var  string */
    protected $enabled;
    /** @var string */
    protected $created_at;

    protected static $table = 'connectpx_booking_customers';

    protected static $schema = array(
        'id'                 => array( 'format' => '%d' ),
        'wp_user_id'         => array( 'format' => '%d' ),
        'first_name'         => array( 'format' => '%s' ),
        'last_name'          => array( 'format' => '%s' ),
        'phone'              => array( 'format' => '%s' ),
        'email'              => array( 'format' => '%s' ),
        'country'            => array( 'format' => '%s' ),
        'state'              => array( 'format' => '%s' ),
        'postcode'           => array( 'format' => '%s' ),
        'city'               => array( 'format' => '%s' ),
        'street'             => array( 'format' => '%s' ),
        'street_number'      => array( 'format' => '%s' ),
        'additional_address' => array( 'format' => '%s' ),
        'pickup_lat' => array( 'format' => '%s' ),
        'pickup_lng' => array( 'format' => '%s' ),
        'destination_lat' => array( 'format' => '%s' ),
        'destination_lng' => array( 'format' => '%s' ),
        'notes'              => array( 'format' => '%s' ),
        'services'           => array( 'format' => '%s' ),
        'enabled'           => array( 'format' => '%s' ),
        'created_at'         => array( 'format' => '%s' ),
    );

    /**
     * Delete customer and associated WP user if requested.
     *
     * @param bool $with_wp_user
     */
    public function deleteWithWPUser( $with_wp_user )
    {
        if ( $with_wp_user && $this->getWpUserId()
             // Can't delete your WP account
             && ( $this->getWpUserId() != get_current_user_id() ) ) {
            wp_delete_user( $this->getWpUserId() );
        }

        /** @var Appointment[] $appointments */
        $appointments = Appointment::query( 'a' )
            ->leftJoin( 'CustomerAppointment', 'ca', 'ca.appointment_id = a.id' )
            ->where( 'ca.customer_id', $this->getId() )
            ->groupBy( 'a.id' )
            ->find()
        ;

        $this->delete();

        foreach ( $appointments as $appointment ) {
            // Google Calendar.
            Lib\Proxy\Pro::syncGoogleCalendarEvent( $appointment );
            // Waiting list.
            Lib\Proxy\WaitingList::handleParticipantsChange( false, $appointment );
        }
    }

    /**
     * Get upcoming appointments.
     *
     * @return array
     */
    public function getUpcomingAppointments()
    {
        return $this->_buildQueryForAppointments()
            ->whereRaw( 'a.start_date >= "%s" OR (a.start_date IS NULL AND ca.status != "%s")', array( current_time( 'Y-m-d 00:00:00' ), CustomerAppointment::STATUS_DONE ) )
            ->fetchArray();
    }

    /**
     * Get past appointments.
     *
     * @param $page
     * @param $limit
     * @return array
     */
    public function getPastAppointments( $page, $limit )
    {
        $result = array( 'more' => true, 'appointments' => array() );

        $records = $this->_buildQueryForAppointments()
            ->whereRaw( 'a.start_date < "%s" OR (a.start_date IS NULL AND ca.status = "%s")', array( current_time( 'Y-m-d 00:00:00' ), CustomerAppointment::STATUS_DONE ) )
            ->limit( $limit + 1 )
            ->offset( ( $page - 1 ) * $limit )
            ->fetchArray();

        $result['more'] = count( $records ) > $limit;
        if ( $result['more'] ) {
            array_pop( $records );
        }

        $result['appointments'] = $records;

        return $result;
    }

    /**
     * Build query for getUpcomingAppointments and getPastAppointments methods.
     *
     * @return Lib\Query
     */
    private function _buildQueryForAppointments()
    {
        $client_diff = get_option( 'gmt_offset' ) * MINUTE_IN_SECONDS;

        return Appointment::query( 'a' )
            ->select( 'ca.id AS ca_id,
                    c.name AS category,
                    COALESCE(s.title, a.custom_service_name) AS service,
                    st.full_name AS staff,
                    a.staff_id,
                    a.staff_any,
                    a.service_id,
                    s.category_id,
                    ca.status AS appointment_status,
                    ca.extras,
                    ca.collaborative_service_id,
                    ca.compound_token,
                    ca.number_of_persons,
                    ca.custom_fields,
                    ca.appointment_id,
                    IF (ca.compound_service_id IS NULL AND ca.collaborative_service_id IS NULL, COALESCE(ss.price, ss_no_location.price, a.custom_service_price), s.price) AS price,
                    IF (ca.time_zone_offset IS NULL,
                        a.start_date,
                        DATE_SUB(a.start_date, INTERVAL ' . $client_diff . ' + ca.time_zone_offset MINUTE)
                    ) AS start_date,
                    ca.token' )
            ->leftJoin( 'Staff', 'st', 'st.id = a.staff_id' )
            ->leftJoin( 'Customer', 'customer', 'customer.wp_user_id = ' . $this->getWpUserId() )
            ->innerJoin( 'CustomerAppointment', 'ca', 'ca.appointment_id = a.id AND ca.customer_id = customer.id' )
            ->leftJoin( 'Service', 's', 's.id = COALESCE(ca.compound_service_id, ca.collaborative_service_id, a.service_id)' )
            ->leftJoin( 'Category', 'c', 'c.id = s.category_id' )
            ->leftJoin( 'StaffService', 'ss', 'ss.staff_id = a.staff_id AND ss.service_id = a.service_id AND ss.location_id <=> a.location_id' )
            ->leftJoin( 'StaffService', 'ss_no_location', 'ss_no_location.staff_id = a.staff_id AND ss_no_location.service_id = a.service_id AND ss_no_location.location_id IS NULL' )
            ->leftJoin( 'Payment', 'p', 'p.id = ca.payment_id' )
            ->groupBy( 'COALESCE(compound_token, collaborative_token, ca.id)' )
            ->sortBy( 'start_date' )
            ->order( 'DESC' );
    }

    /**************************************************************************
     * Entity Fields Getters & Setters                                        *
     **************************************************************************/

    /**
     * Gets wp_user_id
     *
     * @return int
     */
    public function isContractCustomer()
    {
        return $this->wp_user_id ? 1 : 0;
    }

    /**
     * Gets wp_user_id
     *
     * @return int
     */
    public function getWpUserId()
    {
        return $this->wp_user_id;
    }

    /**
     * Associate WP user with customer.
     *
     * @param int $wp_user_id
     * @return $this
     */
    public function setWpUserId( $wp_user_id )
    {
        $this->wp_user_id = $wp_user_id;

        return $this;
    }

    /**
     * Gets wp_user_id
     *
     * @return int
     */
    public function getWpUser()
    {
        return $this->wp_user_id ? get_user_by( 'id', $this->wp_user_id ) : null;
    }

    /**
     * Gets full_name
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->first_name . " " . $this->last_name;
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
     * @return string
     */
    public function getPickupLat()
    {
        return (float) $this->pickup_lat;
    }

    /**
     * @param string $pickup_lat
     * @return $this
     */
    public function setPickupLat( $pickup_lat )
    {
        $this->pickup_lat = $pickup_lat;

        return $this;
    }

    /**
     * @return string
     */
    public function getPickupLng()
    {
        return (float) $this->pickup_lng;
    }

    /**
     * @param string $pickup_lng
     * @return $this
     */
    public function setPickupLng( $pickup_lng )
    {
        $this->pickup_lng = $pickup_lng;

        return $this;
    }

    /**
     * @return string
     */
    public function getDestinationLat()
    {
        return (float) $this->destination_lat;
    }

    /**
     * @param string $destination_lat
     * @return $this
     */
    public function setDestinationLat( $destination_lat )
    {
        $this->destination_lat = $destination_lat;

        return $this;
    }

    /**
     * @return string
     */
    public function getDestinationLng()
    {
        return (float) $this->destination_lng;
    }

    /**
     * @param string $destination_lng
     * @return $this
     */
    public function setDestinationLng( $destination_lng )
    {
        $this->destination_lng = $destination_lng;

        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultLatLngs()
    {
        return [
            'pickup' => [
                'lat' => (float) $this->pickup_lat,
                'lng' => (float) $this->pickup_lng,
            ],
            'destination' => [
                'lat' => (float) $this->destination_lat,
                'lng' => (float) $this->destination_lng,
            ],
        ];
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
     * Sets services
     *
     * @param string $services
     * @return $this
     */
    public function setServices( $services )
    {
        $this->services = $services;

        return $this;
    }

    /**
     * Gets services
     *
     * @return string
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * Gets enabled
     *
     * @return string
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Sets enabled
     *
     * @param string $enabled
     * @return $this
     */
    public function setEnabled( $enabled )
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Gets enabled
     *
     * @return string
     */
    public function isEnabled()
    {
        return $this->enabled == 'yes';
    }

    /**
     * Get sub services.
     *
     * @return Service[]
     */
    public function loadSubService( $service_id, $key )
    {
        $list = $this->loadSubServices( $service_id );
        if( !isset($list[ $key ]) ) {
            return;
        }

        return $list[ $key ];
    }

    /**
     * Get sub services.
     *
     * @return Service[]
     */
    public function loadSubServices( $service_id )
    {
        $list = [];

        $services = $this->services ? json_decode($this->getServices(), true) : [];
        $sub_services = $services[$service_id]['sub_services'] ?? [];
        foreach ($sub_services as $key => $sub_service) {
            $entity = new SubService( $key, $sub_service, 'customer' );
            $list[ $key ] = $entity;
        }

        return $list;
    }

    /**
     * Get sub services.
     *
     * @return Service[]
     */
    public function loadEnabledSubServices( $service_id )
    {
        $list = [];

        foreach ($this->loadSubServices( $service_id ) as $subService) {
            if( $subService->isEnabled() ) {
                $list[ $subService->getKey() ] = $subService;
            }
        }

        return $list;
    }

    /**
     * Gets created_at
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Sets created_at
     *
     * @param string $created_at
     * @return $this
     */
    public function setCreatedAt( $created_at )
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return Lib\Utils\Common::getFullAddressByCustomerData( array(
            'country'            => $this->getCountry(),
            'state'              => $this->getState(),
            'postcode'           => $this->getPostcode(),
            'city'               => $this->getCity(),
            'street'             => $this->getStreet(),
            'street_number'      => $this->getStreetNumber(),
            'additional_address' => $this->getAdditionalAddress(),
        ) );
    }

    /**************************************************************************
     * Overridden Methods                                                     *
     **************************************************************************/

    /**
     * Save entity to database.
     * Fill name, first_name, last_name before save
     *
     * @return int|false
     */
    public function save()
    {
        if ( $this->getCreatedAt() === null ) {
            $this->setCreatedAt( current_time( 'mysql' ) );
        }

        $return = parent::save();

        return $return;
    }

    /**
     * @inheritDoc
     */
    public function getCodes()
    {
        $codes['{client_email}'] = $this->getEmail();
        $codes['{client_first_name}'] = $this->getFirstName();
        $codes['{client_last_name}'] = $this->getLastName();
        $codes['{client_name}'] = $this->getFullName();
        $codes['{client_phone}'] = $this->getPhone();
        $codes['{client_address}'] = $this->getAddress();
        $codes['{client_note}'] = $this->getNotes();
        return $codes;
    }
}