<?php
namespace ConnectpxBooking\Lib\Entities;

use ConnectpxBooking\Lib;

/**
 * Class Appointment
 * @package ConnectpxBooking\Lib\Entities
 */
class Appointment extends Lib\Base\Entity
{
    const STATUS_PENDING    = 'pending';
    const STATUS_APPROVED   = 'approved';
    const STATUS_CANCELLED  = 'cancelled';
    const STATUS_REJECTED   = 'rejected';
    const STATUS_DONE       = 'done';

    const PAYMENT_COMPLETED  = 'completed';
    const PAYMENT_PENDING    = 'pending';
    const PAYMENT_REJECTED   = 'rejected';

    const PAYMENT_TYPE_LOCAL        = 'local';
    const PAYMENT_TYPE_FREE         = 'free';
    const PAYMENT_TYPE_WOOCOMMERCE  = 'woocommerce';

    /** @var int */
    protected $service_id;
    /** @var string */
    protected $pickup_datetime;
    /** @var string */
    protected $return_pickup_datetime;
    /** @var int */
    protected $distance = 0;
    /** @var int */
    protected $waiting_time = 0;
    /** @var string */
    protected $notes;
    /** @var string */
    protected $customer_id;
    /** @var string */
    protected $wc_order_id;
    /** @var string */
    protected $sub_service_key;
    /** @var string */
    protected $sub_service_data;
    /** @var string */
    protected $is_after_hours;
    /** @var string */
    protected $time_zone;
    /** @var string */
    protected $time_zone_offset;
    /** @var string */
    protected $pickup_detail;
    /** @var string */
    protected $pickup_address;
    /** @var string */
    protected $status;
    /** @var string */
    protected $total_amount = 0;
    /** @var string */
    protected $payment_status;
    /** @var string */
    protected $payment_type;
    /** @var string */
    protected $created_at;
    /** @var string */
    protected $updated_at;

    protected static $table = 'connectpx_booking_appointments';

    protected static $schema = array(
        'id'                       => array( 'format' => '%d' ),
        'service_id'               => array( 'format' => '%d', 'reference' => array( 'entity' => 'Service' ) ),
        'pickup_datetime'               => array( 'format' => '%s' ),
        'return_pickup_datetime'                 => array( 'format' => '%s' ),
        'distance'          => array( 'format' => '%d' ),
        'waiting_time'          => array( 'format' => '%d' ),
        'notes'            => array( 'format' => '%s' ),
        'customer_id'          => array( 'format' => '%s' ),
        'wc_order_id'        => array( 'format' => '%s' ),
        'sub_service_key'         => array( 'format' => '%s' ),
        'sub_service_data' => array( 'format' => '%s' ),
        'is_after_hours'  => array( 'format' => '%s' ),
        'time_zone'  => array( 'format' => '%s' ),
        'time_zone_offset'        => array( 'format' => '%s' ),
        'pickup_detail'        => array( 'format' => '%s' ),
        'destination_detail'        => array( 'format' => '%s' ),
        'status'      => array( 'format' => '%s' ),
        'total_amount'             => array( 'format' => '%s' ),
        'created_at'               => array( 'format' => '%s' ),
        'updated_at'               => array( 'format' => '%s' ),
    );

    /**
     * Get color of service
     *
     * @param string $default
     * @return string
     */
    public function getColor( $default = '#DDDDDD' )
    {
        if ( ! $this->isLoaded() ) {
            return $default;
        }

        $service = new Service();

        if ( $service->load( $this->getServiceId() ) ) {
            return $service->getColor();
        }

        return $default;
    }

    /**
     * Get CustomerAppointment entities associated with this appointment.
     *
     * @param bool $with_cancelled
     * @return CustomerAppointment[]   Array of entities
     */
    public function getCustomerAppointments( $with_cancelled = false )
    {
        $result = array();

        if ( $this->getId() ) {
            $appointments = CustomerAppointment::query( 'ca' )
                ->select( 'ca.*, c.full_name, c.first_name, c.last_name, c.phone, c.email, c.notes AS customer_notes' )
                ->leftJoin( 'Customer', 'c', 'c.id = ca.customer_id' )
                ->where( 'ca.appointment_id', $this->getId() );
            if ( ! $with_cancelled ) {
                $appointments->whereIn( 'ca.status', Lib\Proxy\CustomStatuses::prepareBusyStatuses( array(
                    Lib\Entities\CustomerAppointment::STATUS_PENDING,
                    Lib\Entities\CustomerAppointment::STATUS_APPROVED
                ) ) );
            }

            foreach ( $appointments->fetchArray() as $data ) {
                $ca = new CustomerAppointment( $data );

                // Inject Customer entity.
                $ca->customer = new Customer();
                $data['id']   = $data['customer_id'];
                $ca->customer
                    ->setFullName( $data['full_name'] )
                    ->setLastName( $data['last_name'] )
                    ->setPhone( $data['phone'] )
                    ->setEmail( $data['email'] )
                    ->setNotes( $data['customer_notes'] );

                $result[ $ca->getId() ] = $ca;
            }
        }

        return $result;
    }

    /**
     * Set array of customers associated with this appointment.
     *
     * @param array  $cst_data  Array of customer IDs, custom_fields, number_of_persons, extras and status
     * @param int    $series_id
     * @return CustomerAppointment[] Array of customer_appointment with changed status
     */
    public function saveCustomerAppointments( array $cst_data, $series_id = null )
    {
        $ca_status_changed = array();
        $ca_data = array();
        foreach ( $cst_data as $item ) {
            if ( isset( $item['ca_id'] ) ) {
                $ca_id = $item['ca_id'];
            } else do {
                // New CustomerAppointment.
                $ca_id = 'new-' . mt_rand( 1, 999 );
            } while ( array_key_exists( $ca_id, $ca_data ) === true );
            $ca_data[ $ca_id ] = $item;
        }

        // Retrieve customer appointments IDs currently associated with this appointment.
        $current_ids   = array_map( function( CustomerAppointment $ca ) { return $ca->getId(); }, $this->getCustomerAppointments( true ) );
        $ids_to_delete = array_diff( $current_ids, array_keys( $ca_data ) );
        if ( ! empty ( $ids_to_delete ) ) {
            // Remove redundant customer appointments.
            foreach ($ids_to_delete as $id) {
                Lib\Utils\Log::common( Lib\Utils\Log::ACTION_DELETE, CustomerAppointment::getTableName(), $id, null, __METHOD__ );
            }
            CustomerAppointment::query()->delete()->whereIn( 'id', $ids_to_delete )->execute();
        }
        // Calculate units for custom duration services
        $service = Lib\Entities\Service::find( $this->getServiceId() );
        $units   = ( $service && $service->getUnitsMax() > 1 ) ? ceil( Lib\Slots\DatePoint::fromStr( $this->getReturnPickupDatetime() )->diff( Lib\Slots\DatePoint::fromStr( $this->getPickupDateTime() ) ) / $service->getDuration() ) : 1;

        // Add new customer appointments.
        foreach ( array_diff( array_keys( $ca_data ), $current_ids ) as $id ) {
            $time_zone = isset( $ca_data[ $id ]['timezone'] ) ? Lib\Proxy\Pro::getTimeZoneOffset( $ca_data[ $id ]['timezone'] ) : null;

            $customer_appointment = new CustomerAppointment();
            $customer_appointment
                ->setSeriesId( $series_id )
                ->setAppointmentId( $this->getId() )
                ->setCustomerId( $ca_data[ $id ]['id'] )
                ->setCustomFields( json_encode( $ca_data[ $id ]['custom_fields'] ) )
                ->setExtras( json_encode( $ca_data[ $id ]['extras'] ) )
                ->setStatus( $ca_data[ $id ]['status'] )
                ->setNumberOfPersons( $ca_data[ $id ]['number_of_persons'] )
                ->setNotes( $ca_data[ $id ]['notes'] )
                ->setTotalAmount( $ca_data[ $id ]['total_amount'] )
                ->setPaymentId( $ca_data[ $id ]['payment_id'] )
                ->setUnits( $units )
                ->setCreatedAt( current_time( 'mysql' ) )
                ->setTimeZone( is_array( $time_zone ) ? $time_zone['time_zone'] : $time_zone )
                ->setTimeZoneOffset( is_array( $time_zone ) ? $time_zone['time_zone_offset'] : $time_zone )
                ->setExtrasConsiderDuration( $ca_data[ $id ]['extras_consider_duration'] )
                ->save();
            Lib\Utils\Log::createEntity( $customer_appointment, __METHOD__ );
            Lib\Proxy\Files::attachFiles( $ca_data[ $id ]['custom_fields'], $customer_appointment );
            Lib\Proxy\Pro::createBackendPayment( $ca_data[ $id ], $customer_appointment );
            $customer_appointment->setJustCreated( true );
            $ca_status_changed[] = $customer_appointment;
        }

        // Update existing customer appointments.
        foreach ( array_intersect( $current_ids, array_keys( $ca_data ) ) as $id ) {
            $time_zone = Lib\Proxy\Pro::getTimeZoneOffset( $ca_data[ $id ]['timezone'] );

            $customer_appointment = new CustomerAppointment();
            $customer_appointment->load( $id );

            if ( $customer_appointment->getStatus() != $ca_data[ $id ]['status'] ) {
                $ca_status_changed[] = $customer_appointment;
                $customer_appointment->setStatus( $ca_data[ $id ]['status'] );
            }
            if ( $customer_appointment->getPaymentId() != $ca_data[ $id ]['payment_id'] ) {
                $customer_appointment->setPaymentId( $ca_data[ $id ]['payment_id'] );
            }
            Lib\Proxy\Files::attachFiles( $ca_data[ $id ]['custom_fields'], $customer_appointment );
            $customer_appointment
                ->setNumberOfPersons( $ca_data[ $id ]['number_of_persons'] )
                ->setNotes( $ca_data[ $id ]['notes'] )
                ->setUnits( $units )
                ->setCustomFields( json_encode( $ca_data[ $id ]['custom_fields'] ) )
                ->setExtras( json_encode( $ca_data[ $id ]['extras'] ) )
                ->setTimeZone( $time_zone['time_zone'] )
                ->setTimeZoneOffset( $time_zone['time_zone_offset'] );
            Lib\Utils\Log::updateEntity( $customer_appointment, __METHOD__ );
            $customer_appointment
                ->save();
            Lib\Proxy\Files::attachFiles( $ca_data[ $id ]['custom_fields'], $customer_appointment );
            Lib\Proxy\Pro::createBackendPayment( $ca_data[ $id ], $customer_appointment );
        }

        return $ca_status_changed;
    }

    /**
     * Check whether this appointment has an associated event in Google Calendar.
     *
     * @return bool
     */
    public function hasGoogleCalendarEvent()
    {
        return $this->customer_id != '';
    }

    /**
     * Check whether this appointment has an associated event in Outlook Calendar.
     *
     * @return bool
     */
    public function hasOutlookCalendarEvent()
    {
        return $this->sub_service_key != '';
    }

    /**
     * Get max sum of extras duration of associated customer appointments.
     *
     * @return int
     */
    public function getMaxDistance()
    {
        $duration = 0;
        // Calculate extras duration for appointments with duration < 1 day.
        if ( Lib\Config::serviceExtrasActive() && ( strtotime( $this->getReturnPickupDatetime() ) - strtotime( $this->getPickupDateTime() ) < DAY_IN_SECONDS ) ) {
            $customer_appointments = CustomerAppointment::query()
                ->select( 'extras' )
                ->where( 'appointment_id', $this->getId() )
                ->whereIn( 'status', Lib\Proxy\CustomStatuses::prepareBusyStatuses( array(
                    CustomerAppointment::STATUS_PENDING,
                    CustomerAppointment::STATUS_APPROVED
                ) ) )
                ->where( 'extras_consider_duration', 1 )
                ->fetchArray();
            foreach ( $customer_appointments as $customer_appointment ) {
                if ( $customer_appointment['extras'] != '[]' ) {
                    $distance = Lib\Proxy\ServiceExtras::getTotalDuration( (array) json_decode( $customer_appointment['extras'], true ) );
                    if ( $distance > $duration ) {
                        $duration = $distance;
                    }
                }
            }
        }

        return $duration;
    }

    /**
     * Get information about number of persons grouped by status.
     *
     * @return array
     */
    public function getNopInfo()
    {
        $res = self::query( 'a' )
           ->select( sprintf(
               'SUM(IF(ca.status = "%s", ca.number_of_persons, 0)) AS pending,
                SUM(IF(ca.status IN("%s"), ca.number_of_persons, 0)) AS approved,
                SUM(IF(ca.status IN("%s"), ca.number_of_persons, 0)) AS cancelled,
                SUM(IF(ca.status = "%s", ca.number_of_persons, 0)) AS rejected,
                SUM(IF(ca.status = "%s", ca.number_of_persons, 0)) AS waitlisted,
                ss.capacity_max',
                CustomerAppointment::STATUS_PENDING,
                implode( '","', Lib\Proxy\CustomStatuses::prepareBusyStatuses( array( CustomerAppointment::STATUS_APPROVED ) ) ),
                implode( '","', Lib\Proxy\CustomStatuses::prepareFreeStatuses( array( CustomerAppointment::STATUS_CANCELLED ) ) ),
                CustomerAppointment::STATUS_REJECTED,
                CustomerAppointment::STATUS_WAITLISTED
           ) )
           ->leftJoin( 'CustomerAppointment', 'ca', 'ca.appointment_id = a.id' )
           ->leftJoin( 'StaffService', 'ss', 'ss.staff_id = a.staff_id AND ss.service_id = a.service_id' )
           ->where( 'a.id', $this->getId() )
           ->groupBy( 'a.id' )
           ->fetchRow()
        ;

        $res['total_nop'] = $res['pending'] + $res['approved'];

        return $res;
    }

    /**************************************************************************
     * Entity Fields Getters & Setters                                        *
     **************************************************************************/

    /**
     * Gets location_id
     *
     * @return int
     */
    public function getLocationId()
    {
        return $this->location_id;
    }

    /**
     * Sets location_id
     *
     * @param int $location_id
     * @return $this
     */
    public function setLocationId( $location_id )
    {
        $this->location_id = $location_id;

        return $this;
    }

    /**
     * Gets staff_id
     *
     * @return int
     */
    public function getStaffId()
    {
        return $this->staff_id;
    }

    /**
     * Sets staff
     *
     * @param Staff $staff
     * @return $this
     */
    public function setStaff( Staff $staff )
    {
        return $this->setStaffId( $staff->getId() );
    }
    /**
     * Sets staff_id
     *
     * @param int $staff_id
     * @return $this
     */
    public function setStaffId( $staff_id )
    {
        $this->staff_id = $staff_id;

        return $this;
    }

    /**
     * Gets staff_any
     *
     * @return int
     */
    public function getStaffAny()
    {
        return $this->staff_any;
    }

    /**
     * Sets staff_any
     *
     * @param int $staff_any
     * @return $this
     */
    public function setStaffAny( $staff_any )
    {
        $this->staff_any = $staff_any;

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
     * Sets service
     *
     * @param Service $service
     * @return $this
     */
    public function setService( Service $service )
    {
        return $this->setServiceId( $service->getId() );
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
     * Gets custom_service_name
     *
     * @return string
     */
    public function getCustomServiceName()
    {
        return $this->custom_service_name;
    }

    /**
     * Sets custom_service_name
     *
     * @param int $custom_service_name
     * @return $this
     */
    public function setCustomServiceName( $custom_service_name )
    {
        $this->custom_service_name = $custom_service_name;

        return $this;
    }

    /**
     * Gets custom_service_price
     *
     * @return string
     */
    public function getCustomServicePrice()
    {
        return $this->custom_service_price;
    }

    /**
     * Sets custom_service_price
     *
     * @param int $custom_service_price
     * @return $this
     */
    public function setCustomServicePrice( $custom_service_price )
    {
        $this->custom_service_price = $custom_service_price;

        return $this;
    }

    /**
     * Gets pickup_datetime
     *
     * @return string
     */
    public function getPickupDateTime()
    {
        return $this->pickup_datetime;
    }

    /**
     * Sets pickup_datetime
     *
     * @param string $pickup_datetime
     * @return $this
     */
    public function setPickupDateTime( $pickup_datetime )
    {
        $this->pickup_datetime = $pickup_datetime;

        return $this;
    }

    /**
     * Gets return_pickup_datetime
     *
     * @return string
     */
    public function getReturnPickupDatetime()
    {
        return $this->return_pickup_datetime;
    }

    /**
     * Sets return_pickup_datetime
     *
     * @param string $return_pickup_datetime
     * @return $this
     */
    public function setReturnPickupDatetime( $return_pickup_datetime )
    {
        $this->return_pickup_datetime = $return_pickup_datetime;

        return $this;
    }

    /**
     * Gets distance
     *
     * @return int
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * Sets distance
     *
     * @param int $distance
     * @return $this
     */
    public function setDistance( $distance )
    {
        $this->distance = $distance;

        return $this;
    }

    /**
     * Gets waiting_time
     *
     * @return int
     */
    public function getWaitingTime()
    {
        return $this->waiting_time;
    }

    /**
     * Sets waiting_time
     *
     * @param int $waiting_time
     * @return $this
     */
    public function setWaitingTime( $waiting_time )
    {
        $this->waiting_time = $waiting_time;

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
     * Sets customer
     * @param Customer $customer
     * @return $this
     */
    public function setCustomer( Customer $customer )
    {
        return $this->setCustomerId( $customer->getId() );
    }

    /**
     * Gets customer_id
     *
     * @return string
     */
    public function getCustomerId()
    {
        return $this->customer_id;
    }

    /**
     * Sets customer_id
     *
     * @param string $customer_id
     * @return $this
     */
    public function setCustomerId( $customer_id )
    {
        $this->customer_id = $customer_id;

        return $this;
    }

    /**
     * Gets wc_order_id
     *
     * @return string
     */
    public function getWcOrderId()
    {
        return $this->wc_order_id;
    }

    /**
     * Sets wc_order_id
     *
     * @param string $wc_order_id
     * @return $this
     */
    public function setWcOrderId( $wc_order_id )
    {
        $this->wc_order_id = $wc_order_id;

        return $this;
    }

    /**
     * Gets sub_service_key
     *
     * @return string
     */
    public function getSubServiceKey()
    {
        return $this->sub_service_key;
    }

    /**
     * Sets sub_service_key
     *
     * @param string $sub_service_key
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
     * @return string
     */
    public function getSubServiceData()
    {
        return $this->sub_service_data;
    }

    /**
     * Sets sub_service_data
     *
     * @param string $sub_service_data
     * @return $this
     */
    public function setSubServiceData( $sub_service_data )
    {
        $this->sub_service_data = $sub_service_data;

        return $this;
    }

    /**
     * Gets is_after_hours
     *
     * @return string
     */
    public function getIsAfterHours()
    {
        return $this->is_after_hours;
    }

    /**
     * Sets is_after_hours
     *
     * @param string $is_after_hours
     * @return $this
     */
    public function setIsAfterHours( $is_after_hours )
    {
        $this->is_after_hours = $is_after_hours;

        return $this;
    }

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
    public function setTimeZone( $time_zone)
    {
        $this->time_zone = $time_zone;

        return $this;
    }

    /**
     * Gets time_zone_offset
     *
     * @return string
     */
    public function getTimeZoneOffset()
    {
        return $this->time_zone_offset;
    }

    /**
     * Sets time_zone_offset
     *
     * @param string $time_zone_offset
     * @return $this
     */
    public function setTimeZoneOffset( $time_zone_offset )
    {
        $this->time_zone_offset = $time_zone_offset;

        return $this;
    }

    /**
     * Gets pickup_detail
     *
     * @return string
     */
    public function getPickupDetail()
    {
        return $this->pickup_detail;
    }

    /**
     * Sets pickup_detail
     *
     * @param string $pickup_detail
     * @return $this
     */
    public function setPickupDetail( $pickup_detail )
    {
        $this->pickup_detail = $pickup_detail;

        return $this;
    }

    /**
     * Gets destination_detail
     *
     * @return string
     */
    public function getDestinationDetail()
    {
        return $this->destination_detail;
    }

    /**
     * Sets destination_detail
     *
     * @param string $destination_detail
     * @return $this
     */
    public function setDestinationDetail( $destination_detail )
    {
        $this->destination_detail = $destination_detail;

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
     * Gets total_amount
     *
     * @return string
     */
    public function getTotalAmount()
    {
        return $this->total_amount;
    }

    /**
     * Sets total_amount
     *
     * @param string $total_amount
     * @return $this
     */
    public function setTotalAmount( $total_amount )
    {
        $this->total_amount = $total_amount;

        return $this;
    }

    /**
     * Gets payment_status
     *
     * @return string
     */
    public function getPaymentStatus()
    {
        return $this->payment_status;
    }

    /**
     * Sets payment_status
     *
     * @param string $payment_status
     * @return $this
     */
    public function setPaymentStatus( $payment_status )
    {
        $this->payment_status = $payment_status;

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

    /**
     * Save appointment to database
     *(and delete event in Google Calendar if staff changes).
     *
     * @return false|int
     */
    public function save()
    {
        // Google and Outlook calendars.
        if ( $this->isLoaded() && ( $this->hasGoogleCalendarEvent() || $this->hasOutlookCalendarEvent() ) ) {
            $modified = $this->getModified();
            if ( array_key_exists( 'staff_id', $modified ) ) {
                // Delete event from Google and Outlook calendars of the old staff if the staff was changed.
                $staff_id = $this->getStaffId();
                $this->setStaffId( $modified['staff_id'] );
                Lib\Proxy\Pro::deleteGoogleCalendarEvent( $this );
                Lib\Proxy\OutlookCalendar::deleteEvent( $this );
                $this
                    ->setStaffId( $staff_id )
                    ->setCustomerId( null )
                    ->setWcOrderId( null )
                    ->setSubServiceKey( null )
                    ->setSubServiceData( null )
                ;
            }
        }

        if ( $this->getId() == null ) {
            $this
                ->setCreatedAt( current_time( 'mysql' ) )
                ->setUpdatedAt( current_time( 'mysql' ) );
        } elseif ( $this->getModified() ) {
            $this->setUpdatedAt( current_time( 'mysql' ) );
        }

        return parent::save();
    }

    /**
     * Delete entity from database
     *(and delete event in Google Calendar if it exists).
     *
     * @return bool|false|int
     */
    public function delete()
    {
        // Delete all CustomerAppointments for current appointments
        $ca_list = Lib\Entities\CustomerAppointment::query()
            ->where( 'appointment_id', $this->getId() )
            ->find();
        /** @var Lib\Entities\CustomerAppointment $ca */
        foreach ( $ca_list as $ca ) {
            Lib\Utils\Log::deleteEntity( $ca, __METHOD__, 'Delete customer appointments on delete appointment' );
            $ca->delete();
        }

        $result = parent::delete();
        if ( $result ) {
            Lib\Proxy\Pro::deleteOnlineMeeting( $this );
            Lib\Proxy\Pro::deleteGoogleCalendarEvent( $this );
            Lib\Proxy\OutlookCalendar::deleteEvent( $this );
        }

        return $result;
    }

    /**
     * Get appointment statuses.
     *
     * @return array
     */
    public static function getStatuses()
    {
        if ( ! self::hasInCache( __FUNCTION__ ) ) {
            $statuses = array(
                self::STATUS_PENDING,
                self::STATUS_APPROVED,
                self::STATUS_CANCELLED,
                self::STATUS_REJECTED,
            );
            self::putInCache( __FUNCTION__, $statuses );
        }

        return self::getFromCache( __FUNCTION__ );
    }

    public static function statusToString( $status )
    {
        switch ( $status ) {
            case self::STATUS_PENDING:    return __( 'Pending',   'bookly' );
            case self::STATUS_APPROVED:   return __( 'Approved',  'bookly' );
            case self::STATUS_CANCELLED:  return __( 'Cancelled', 'bookly' );
            case self::STATUS_REJECTED:   return __( 'Rejected',  'bookly' );
            case self::STATUS_DONE:       return __( 'Done', 'bookly' );
            case 'mixed':                 return __( 'Mixed', 'bookly' );
            default: return '';
        }
    }

    /**
     * Get display name for given payment type.
     *
     * @param string $type
     * @return string
     */
    public static function paymentTypeToString( $type )
    {
        switch ( $type ) {
            case self::PAYMENT_TYPE_LOCAL:        return __( 'Local', 'connectpx_booking' );
            case self::PAYMENT_TYPE_FREE:         return __( 'Free', 'connectpx_booking' );
            case self::PAYMENT_TYPE_WOOCOMMERCE:  return 'WooCommerce';
            default:                      return '';
        }
    }

    /**
     * Get status of payment.
     *
     * @param string $status
     * @return string
     */
    public static function paymentStatusToString( $status )
    {
        switch ( $status ) {
            case self::PAYMENT_COMPLETED:  return __( 'Completed', 'connectpx_booking' );
            case self::PAYMENT_PENDING:    return __( 'Pending',   'connectpx_booking' );
            case self::PAYMENT_REJECTED:   return __( 'Rejected',  'connectpx_booking' );
            default:                      return '';
        }
    }

}