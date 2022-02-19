<?php
namespace ConnectpxBooking\Lib;

use ConnectpxBooking\Lib;

/**
 * Class CartItem
 * @package ConnectpxBooking\Lib
 */
class CartItem
{
    // Step service
    /** @var  int */
    protected $service_id;
    /** @var  string */
    protected $sub_service_key;
    /** @var  array */
    protected $sub_service_data;
    /** @var  int */
    protected $route_distance;
    /** @var  int */
    protected $route_time;
    /** @var  string Y-m-d */
    protected $date_from;

    // Step time
    /** @var  array */
    protected $slot;

    // Step done
    /** @var  int */
    protected $appointment_id;

    // Add here the properties that don't need to be returned in getData

    /**
     * Constructor.
     */
    public function __construct() { }

    /**
     * Get data.
     *
     * @return array
     */
    public function getData()
    {
        return get_object_vars( $this );
    }

    /**
     * Set data.
     *
     * @param array $data
     * @return $this
     */
    public function setData( array $data )
    {
        foreach ( $data as $name => $value ) {
            $this->{$name} = $value;
        }

        return $this;
    }

    /**
     * Get appointment.
     *
     * @return Entities\Appointment|false
     */
    public function getAppointment()
    {
        return Entities\Appointment::find( $this->appointment_id );
    }

    /**
     * Get service.
     *
     * @return Entities\Service
     */
    public function getService()
    {
        return Entities\Service::find( $this->service_id );
    }

    /**
     * Get service price.
     *
     * @param int $nop
     * @return float
     */
    public function getServicePrice()
    {
        $subService = $this->getSubService();
        $price = $subService->getFlatRate();
        return $price;
    }

    /**************************************************************************
     * Getters & Setters                                                      *
     **************************************************************************/

    /**
     * Gets appointment_id
     *
     * @return int
     */
    public function getAppointmentId()
    {
        return $this->appointment_id;
    }

    /**
     * Sets appointment_id
     *
     * @param int $appointment_id
     * @return $this
     */
    public function setAppointmentId( $appointment_id )
    {
        $this->appointment_id = $appointment_id;

        return $this;
    }

    /**
     * Gets service_id
     *
     * @return int
     */
    public function getServiceId()
    {
        return $this->service_id;
    }

    /**
     * Sets service_id
     *
     * @param int $service_id
     * @return $this
     */
    public function setServiceId( $service_id )
    {
        $this->service_id = $service_id;

        return $this;
    }

    /**
     * Gets sub_service_key
     *
     * @return int
     */
    public function getSubServiceKey()
    {
        return $this->sub_service_key;
    }

    /**
     * Sets sub_service_key
     *
     * @param int $sub_service_key
     * @return $this
     */
    public function setSubServiceKey( $sub_service_key )
    {
        $this->sub_service_key = $sub_service_key;

        return $this;
    }

    /**
     * Gets sub_service_data
     *
     * @return int
     */
    public function getSubService()
    {
        return new Lib\Entities\SubService( $this->sub_service_key, $this->sub_service_data, 'cart_item' );
    }

    /**
     * Gets sub_service_data
     *
     * @return int
     */
    public function getSubServiceData()
    {
        return $this->sub_service_data;
    }

    /**
     * Sets sub_service_data
     *
     * @param int $sub_service_data
     * @return $this
     */
    public function setSubServiceData( $sub_service_data )
    {
        $this->sub_service_data = $sub_service_data;

        return $this;
    }

    /**
     * Gets route_distance
     *
     * @return int
     */
    public function getRouteDistance()
    {
        return $this->route_distance;
    }

    /**
     * Sets route_distance
     *
     * @param int $route_distance
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
     * @return int
     */
    public function getRouteTime()
    {
        return $this->route_time;
    }

    /**
     * Sets route_time
     *
     * @param int $route_time
     * @return $this
     */
    public function setRouteTime( $route_time )
    {
        $this->route_time = $route_time;

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
     * Gets slot
     *
     * @return array
     */
    public function getSlot()
    {
        return $this->slot;
    }

    /**
     * Sets slot
     *
     * @param array $slot
     * @return $this
     */
    public function setSlot( $slot )
    {
        $this->slot = $slot;

        return $this;
    }

}