<?php
namespace ConnectpxBooking\Lib;

/**
 * Class CartItem
 * @package ConnectpxBooking\Lib
 */
class CartItem
{
    // Step service
    /** @var  int */
    protected $service_id;
    /** @var  string Y-m-d */
    protected $date_from;

    // Step time
    /** @var  array */
    protected $slots;

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
        return $this->getService()->getCustomerPrice( null );
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

}