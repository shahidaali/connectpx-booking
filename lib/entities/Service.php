<?php
namespace ConnectpxBooking\Lib\Entities;

use ConnectpxBooking\Lib;

/**
 * Class Service
 * @package ConnectpxBooking\Lib\Entities
 */
class Service extends Lib\Base\Entity
{
    /** @var  string */
    protected $title;
    /** @var  string */
    protected $description;
    /** @var  string */
    protected $enabled;
    /** @var  int */
    /** @var  string */
    protected $sub_services = '[]';

    protected static $table = 'connectpx_booking_services';

    protected static $schema = array(
        'id'                           => array( 'format' => '%d' ),
        'title'                        => array( 'format' => '%s' ),
        'description'                  => array( 'format' => '%s' ),
        'enabled'                      => array( 'format' => '%s' ),
        'sub_services'                 => array( 'format' => '%s' ),
    );

    /**
     * Check if given customer has reached the appointments limit for this service.
     *
     * @param int   $customer_id
     * @param array $appointment_dates format( 'Y-m-d H:i:s' )
     * @return bool
     */
    public function appointmentsLimitReached( $customer_id, array $appointment_dates )
    {
        if ( Lib\Config::proActive() && $this->getLimitPeriod() != 'off' && $this->getAppointmentsLimit() > 0 ) {
            if ( $this->isCompound() ) {
                // Compound service.
                $sub_services             = $this->getSubServices();
                $compound_service_id      = $this->getId();
                $collaborative_service_id = null;
                $service_id               = $sub_services[0]->getId();
            } elseif ( $this->isCollaborative() ) {
                // Collaborative service.
                $sub_services             = $this->getSubServices();
                $compound_service_id      = null;
                $collaborative_service_id = $this->getId();
                $service_id               = $sub_services[0]->getId();
            } else {
                // Simple service.
                $compound_service_id      = null;
                $collaborative_service_id = null;
                $service_id               = $this->getId();
            }
            $statuses = get_option( 'bookly_cst_limit_statuses', array() );
            switch ( $this->getLimitPeriod() ) {
                case 'upcoming':
                    $db_count = CustomerAppointment::query( 'ca' )
                        ->leftJoin( 'Appointment', 'a', 'ca.appointment_id = a.id' )
                        ->where( 'a.service_id', $service_id )
                        ->where( 'ca.compound_service_id', $compound_service_id )
                        ->where( 'ca.collaborative_service_id', $collaborative_service_id )
                        ->where( 'ca.customer_id', $customer_id )
                        ->whereGt( 'a.start_date', current_time( 'mysql' ) )
                        ->whereNotIn( 'ca.status', $statuses )
                        ->count();
                    if ( $db_count + count( $appointment_dates ) > $this->getAppointmentsLimit() ) {
                        return true;
                    }
                    break;
                default:
                    foreach ( $appointment_dates as $appointment_date ) {
                        $regarding_appointment = false;
                        switch ( $this->getLimitPeriod() ) {
                            case 'calendar_day':
                                $bound_start = date_create( $appointment_date )->format( 'Y-m-d 00:00:00' );
                                $bound_end   = date_create( $appointment_date )->format( 'Y-m-d 23:59:59' );
                                break;
                            case 'calendar_week':
                                $week_day    = date_create( $appointment_date )->format( 'w' );
                                $start_week  = (int) get_option( 'start_of_week' );
                                $delta       = $week_day < $start_week ? $start_week + $week_day - 7 : $start_week - $week_day;
                                $start_date  = date_create( $appointment_date )->modify( $delta . ' day' );
                                $bound_start = $start_date->format( 'Y-m-d 00:00:00' );
                                $bound_end   = $start_date->modify( '+6 day' )->format( 'Y-m-d 23:59:59' );
                                break;
                            case 'calendar_month':
                                $bound_start = date_create( $appointment_date )->modify( 'first day of this month' )->format( 'Y-m-d 00:00:00' );
                                $bound_end   = date_create( $appointment_date )->modify( 'last day of this month' )->format( 'Y-m-d 23:59:59' );
                                break;
                            case 'calendar_year':
                                $bound_start = date_create( $appointment_date )->modify( 'first day of January' )->format( 'Y-m-d 00:00:00' );
                                $bound_end   = date_create( $appointment_date )->modify( 'last day of December' )->format( 'Y-m-d 23:59:59' );
                                break;

                            case 'day':
                                $bound_start = date_create( $appointment_date )->modify( '-1 day' )->format( 'Y-m-d H:i:s' );
                                $bound_end   = $appointment_date;
                                $regarding_appointment = true;
                                break;
                            case 'week':
                                $bound_start = date_create( $appointment_date )->modify( '-1 week' )->format( 'Y-m-d H:i:s' );
                                $bound_end   = $appointment_date;
                                $regarding_appointment = true;
                                break;
                            case 'month':
                                $bound_start = date_create( $appointment_date )->modify( '-30 days' )->format( 'Y-m-d H:i:s' );
                                $bound_end   = $appointment_date;
                                $regarding_appointment = true;
                                break;
                            case 'year':
                                $bound_start = date_create( $appointment_date )->modify( '-365 days' )->format( 'Y-m-d H:i:s' );
                                $bound_end   = $appointment_date;
                                $regarding_appointment = true;
                                break;
                        }
                        $query = CustomerAppointment::query( 'ca' )
                            ->leftJoin( 'Appointment', 'a', 'ca.appointment_id = a.id' )
                            ->where( 'a.service_id', $service_id )
                            ->where( 'ca.compound_service_id', $compound_service_id )
                            ->where( 'ca.customer_id', $customer_id )
                            ->whereNotIn( 'ca.status', $statuses );

                        if ( $regarding_appointment ) {
                            $query
                                ->whereGt( 'a.start_date', $bound_start )
                                ->whereLte( 'a.start_date', $bound_end );
                        } else {
                            $query
                                ->whereGte( 'a.start_date', $bound_start )
                                ->whereLt( 'a.start_date', $bound_end );
                        }

                        $db_count = $query->count();
                        $cart_count  = 0;
                        $bound_start = strtotime( $bound_start );
                        $bound_end   = strtotime( $bound_end );
                        foreach ( $appointment_dates as $date ) {
                            $cur_date = strtotime( $date );
                            if ( $cur_date <= $bound_end && $cur_date >= $bound_start ) {
                                $cart_count ++;
                            }
                        }
                        if ( $db_count + $cart_count > $this->getAppointmentsLimit() ) {
                            return true;
                        }
                    }
                    break;
            }
        }

        return false;
    }

    /**
     * Get sub services.
     *
     * @return Service[]
     */
    public function loadSubService( $key )
    {
        $list = $this->loadSubServices();
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
    public function loadSubServices()
    {
        $list = [];

        $sub_services = $this->sub_services ? json_decode($this->getSubServices(), true) : [];
        foreach ($sub_services as $key => $sub_service) {
            $entity = new SubService( $key, $sub_service, 'service' );
            $list[ $key ] = $entity;
        }

        return $list;
    }

    /**
     * Get sub services.
     *
     * @return Service[]
     */
    public function loadEnabledSubServices()
    {
        $list = [];

        foreach ($this->loadSubServices() as $subService) {
            if( $subService->isEnabled() ) {
                $list[ $subService->getKey() ] = $subService;
            }
        }

        return $list;
    }

    /**
     * Get sub services.
     *
     * @return Service[]
     */
    public function getSubServices()
    {
        return $this->sub_services;
    }

    /**
     * Get sub services.
     *
     * @return Service[]
     */
    public function setSubServices()
    {
        $this->sub_services = $sub_services;

        return $this;
    }

    /**
     * Gets title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle( $title )
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Gets description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription( $description )
    {
        $this->description = $description;

        return $this;
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
     * Gets description
     *
     * @return string
     */
    public function getCustomerPrice( $customer )
    {
        return 500;
    }

    /**************************************************************************
     * Overridden Methods                                                     *
     **************************************************************************/

    /**
     * Save service.
     *
     * @return false|int
     */
    public function save()
    {
        $return = parent::save();
        return $return;
    }
}