<?php
namespace ConnectpxBooking\Lib\Entities;

use ConnectpxBooking\Lib;
use ConnectpxBooking\Lib\Utils\DateTime;
use ConnectpxBooking\Backend;

/**
 * Class Schedule
 * @package ConnectpxBooking\Lib\Entities
 */
class Schedule extends Lib\Base\Entity
{
    const STATUS_CANCELLED  = 'cancelled';
    const STATUS_PENDING    = 'pending';

    /** @var int */
    protected $customer_id;
    /** @var int */
    protected $service_id;
    /** @var datetime */
    protected $start_date;
    /** @var datetime */
    protected $end_date;
    /** @var string */
    protected $status = self::STATUS_PENDING;
    /** @var string */
    protected $details;
    /** @var string */
    protected $created_at;
    /** @var string */
    protected $updated_at;
    /** @var string */
    protected $appointment;

    protected static $table = 'connectpx_booking_schedules';

    protected static $schema = array(
        'id'                       => array( 'format' => '%d' ),
        'customer_id'               => array( 'format' => '%d', 'reference' => array( 'entity' => 'Customer' ) ),
        'service_id'               => array( 'format' => '%d', 'reference' => array( 'entity' => 'Service' ) ),
        'start_date'               => array( 'format' => '%s' ),
        'end_date'                 => array( 'format' => '%s' ),
        'status'          => array( 'format' => '%s' ),
        'details'          => array( 'format' => '%s' ),
        'created_at'               => array( 'format' => '%s' ),
        'updated_at'               => array( 'format' => '%s' ),
    );

    /**
     * Get appointment statuses.
     *
     * @return array
     */
    public static function getStatuses()
    {
        if ( ! self::hasInCache( __FUNCTION__ ) ) {
            $statuses = array(
                self::STATUS_CANCELLED,
                self::STATUS_PENDING,
            );
            self::putInCache( __FUNCTION__, $statuses );
        }

        return self::getFromCache( __FUNCTION__ );
    }

    /**
     * Get status of payment.
     *
     * @param string $status
     * @return string
     */
    public static function statusToString( $status )
    {
        switch ( $status ) {
            case self::STATUS_CANCELLED:  return __( 'Cancelled', 'connectpx_booking' );
            case self::STATUS_PENDING:    return __( 'Pending',   'connectpx_booking' );
            default:                      return '';
        }
    }

    public static function statusToIcon( $status )
    {
        switch ( $status ) {
            case self::STATUS_CANCELLED:   return 'fas fa-times';
            case self::STATUS_PENDING:    return 'far fa-clock';
            default: return '';
        }
    }

    /**************************************************************************
     * Entity Fields Getters & Setters                                        *
     **************************************************************************/

    /**
     * Gets customer_id
     *
     * @return float
     */
    public function getCustomerId()
    {
        return $this->customer_id;
    }

    /**
     * Sets customer_id
     *
     * @param float $customer_id
     * @return $this
     */
    public function setCustomerId( $customer_id )
    {
        $this->customer_id = $customer_id;

        return $this;
    }

    /**
     * Gets customer_id
     *
     * @return float
     */
    public function getCustomer()
    {
        return Lib\Entities\Customer::find( $this->customer_id );
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
     * Gets customer_id
     *
     * @return float
     */
    public function getService()
    {
        return Lib\Entities\Service::find( $this->service_id );
    }

    /**
     * Gets start_date
     *
     * @return float
     */
    public function getStartDate()
    {
        return $this->start_date;
    }

    /**
     * Sets start_date
     *
     * @param float $start_date
     * @return $this
     */
    public function setStartDate( $start_date )
    {
        $this->start_date = $start_date;

        return $this;
    }

    /**
     * Gets end_date
     *
     * @return float
     */
    public function getEndDate()
    {
        return $this->end_date;
    }

    /**
     * Sets end_date
     *
     * @param float $end_date
     * @return $this
     */
    public function setEndDate( $end_date )
    {
        $this->end_date = $end_date;

        return $this;
    }

    /**
     * Gets status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets status
     *
     * @param string $status
     * @return $this
     */
    public function setStatus( $status )
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Gets details
     *
     * @return string
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * Sets details
     *
     * @param string $details
     * @return $this
     */
    public function setDetails( $details )
    {
        $this->details = $details;

        return $this;
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
     * Gets updated_at
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * Sets updated_at
     *
     * @param string $updated_at
     * @return $this
     */
    public function setUpdatedAt( $updated_at )
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /**************************************************************************
     * Overridden Methods                                                     *
     **************************************************************************/

    public function save()
    {
        if ( $this->getId() == null ) {
            $this
                ->setCreatedAt( current_time( 'mysql' ) )
                ->setUpdatedAt( current_time( 'mysql' ) );
        } elseif ( $this->getModified() ){
            $this->setUpdatedAt( current_time( 'mysql' ) );
        }

        return parent::save();
    }

    /**
     * Get HTML for pickup information
     *
     * @return string
     */
    public function getScheduleInfo()
    {
        $appointment = $this->loadFirstAppointment();

        $subService = $appointment->getSubService();

        // Determine display time zone
        $display_tz = Lib\Utils\Common::getCurrentUserTimeZone();
        $wp_tz = Lib\Config::getWPTimeZone();

        $pickupDatetime = $appointment->getPickupDateTime();
        $returnDatetime = $appointment->getReturnPickupDatetime();

        if ( $display_tz !== $wp_tz ) {
            $pickupDatetime = DateTime::convertTimeZone( $pickupDatetime, $wp_tz, $display_tz );
            $returnDatetime   = $returnDatetime ? DateTime::convertTimeZone( $returnDatetime, $wp_tz, $display_tz ) : null;
        }

        $items = [
            [
                'label' => __('Start Date', 'connectpx_booking'),
                'value' => DateTime::formatDate( $this->getStartDate() ),
            ],
            [
                'label' => __('End Date', 'connectpx_booking'),
                'value' => DateTime::formatDate( $this->getEndDate() ),
            ],
            [
                'label' => __('Pickup Time', 'connectpx_booking'),
                'value' => DateTime::formatTime( $pickupDatetime ),
            ],
        ];

        if( $subService->isRoundTrip() ) {
            $items[] = [
                'label' => __('Return Pickup Time', 'connectpx_booking'),
                'value' => $returnDatetime ? DateTime::formatTime( $returnDatetime ) : __('Not sure', 'connectpx_booking'),
            ];
        }

        return Lib\Utils\Common::formatedItemsList( $items );
    }

    /**
     * @param string $reason
     */
    public function cancel( $reason = '', $notification = true )
    {

        $this->setStatus( self::STATUS_CANCELLED );

        // Cancell all future pending appointments
        $start_date = new \DateTime( $this->getStartDate() );
        $rows = Lib\Entities\Appointment::query( 'a' )
            ->select( 'a.*' )
            ->where( 'a.schedule_id', $this->getId() )
            ->whereIn( 'a.status', [
                Lib\Entities\Appointment::STATUS_PENDING,
                Lib\Entities\Appointment::STATUS_APPROVED,
            ])
            ->whereGt( 'a.pickup_datetime', $start_date->format( 'Y-m-d H:i:s' ) )
            ->sortBy( 'a.pickup_datetime' )
            ->order( 'ASC' )
            ->fetchArray();

        foreach ( $rows as $row ) {
            $appointment = new Lib\Entities\Appointment();
            $appointment->setFields( $row, true );
            $appointment->cancel( $reason, false );
            $appointment->refund();
        }

        // if( $notification ) {
        //     Lib\Notifications\Schedule\Sender::send( $this, array( 'cancellation_reason' => $reason ) );
        // }

        return $this->save();
    }

    /**
     * Gets customer_id
     *
     * @return float
     */
    public function getAppointments()
    {
        return Lib\Entities\Appointment::query( 'a' )
            ->select( 'a.*' )
            ->where('a.schedule_id', $this->getId())
            ->sortBy('a.pickup_datetime')
            ->order('ASC')
            ->fetchArray();
    }

    /**
     * Gets customer_id
     *
     * @return float
     */
    public function loadAppointments()
    {
        $appointments = [];

        foreach ($this->getAppointments() as $key => $row) {
            $appointment = new Lib\Entities\Appointment();
            $appointment->setFields( $row, true );

            $appointments[] = $appointment;
        }

        return $appointments;
    }


    /**
     * Gets customer_id
     *
     * @return float
     */
    public function loadFirstAppointment()
    {
        if( ! $this->appointment ) {
            $this->appointment = Lib\Entities\Appointment::query( 'a' )
                ->select( 'a.*' )
                ->where('a.schedule_id', $this->getId())
                ->sortBy('a.id')
                ->order('ASC')
                ->findOne();
        }

        return $this->appointment;
    }
}