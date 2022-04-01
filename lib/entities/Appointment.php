<?php
namespace ConnectpxBooking\Lib\Entities;

use ConnectpxBooking\Lib;
use ConnectpxBooking\Lib\Utils\DateTime;
use ConnectpxBooking\Frontend\WooCommerce;

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
    const STATUS_NOSHOW       = 'noshow';

    const PAYMENT_COMPLETED  = 'completed';
    const PAYMENT_PENDING    = 'pending';
    const PAYMENT_REFUNDED    = 'refunded';

    const PAYMENT_TYPE_LOCAL  = 'cod';
    const PAYMENT_TYPE_SQUARE  = 'square_credit_card';

    /** @var int */
    protected $service_id;
    /** @var string */
    protected $customer_id;
    /** @var string */
    protected $wc_order_id;
    /** @var string */
    protected $schedule_id;
    /** @var string */
    protected $sub_service_key;
    /** @var string */
    protected $sub_service_data;
    /** @var string */
    protected $pickup_datetime;
    /** @var string */
    protected $return_pickup_datetime;
    /** @var int */
    protected $distance = 0;
    /** @var int */
    protected $estimated_time = 0;
    /** @var int */
    protected $waiting_time = 0;
    /** @var string */
    protected $notes;
    /** @var string */
    protected $admin_notes;
    /** @var string */
    protected $is_after_hours = 0;
    /** @var string */
    protected $time_zone;
    /** @var string */
    protected $time_zone_offset;
    /** @var string */
    protected $patient_name;
    /** @var string */
    protected $pickup_detail;
    /** @var string */
    protected $pickup_address;
    /** @var string */
    protected $status;
    /** @var string */
    protected $cancellation_reason;
    /** @var  string Y-m-d H:i:s */
    protected $status_changed_at;
    /** @var string */
    protected $total_amount = 0;
    /** @var string */
    protected $paid_amount = 0;
    /** @var string */
    protected $payment_status;
    /** @var string */
    protected $payment_type;
    /** @var string */
    protected $payment_date;
    /** @var string */
    protected $payment_details;
    /** @var decimal */
    protected $refunded_amount = 0;
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
        'estimated_time'          => array( 'format' => '%d' ),
        'waiting_time'          => array( 'format' => '%d' ),
        'notes'            => array( 'format' => '%s' ),
        'admin_notes'            => array( 'format' => '%s' ),
        'customer_id'          => array( 'format' => '%d', 'reference' => array( 'entity' => 'Customer' ) ),
        'wc_order_id'        => array( 'format' => '%d' ),
        'schedule_id'        => array( 'format' => '%d', 'reference' => array( 'entity' => 'Schedule' ) ),
        'sub_service_key'         => array( 'format' => '%s' ),
        'sub_service_data' => array( 'format' => '%s' ),
        'is_after_hours'  => array( 'format' => '%s' ),
        'time_zone'  => array( 'format' => '%s' ),
        'time_zone_offset'        => array( 'format' => '%s' ),
        'patient_name'        => array( 'format' => '%s' ),
        'pickup_detail'        => array( 'format' => '%s' ),
        'destination_detail'        => array( 'format' => '%s' ),
        'status'      => array( 'format' => '%s' ),
        'cancellation_reason'      => array( 'format' => '%s' ),
        'status_changed_at'        => array( 'format' => '%s' ),
        'total_amount'             => array( 'format' => '%s' ),
        'paid_amount'             => array( 'format' => '%s' ),
        'payment_status'             => array( 'format' => '%s' ),
        'payment_type'             => array( 'format' => '%s' ),
        'payment_date'             => array( 'format' => '%s' ),
        'payment_details'             => array( 'format' => '%s' ),
        'refunded_amount'             => array( 'format' => '%s' ),
        'created_at'               => array( 'format' => '%s' ),
        'updated_at'               => array( 'format' => '%s' ),
    );

    /** @var Customer */
    public $customer;

    /** @var  string */
    private $last_status;
    /** @var bool  */
    private $just_created = false;

    /**
     * Delete entity and appointment if there are no more customers.
     *
     * @param bool $compound_collaborative
     */
    public function deleteCascade( $compound_collaborative = false )
    {
        $this->delete();
    }

    /**
     * @return string
     */
    public function getStatusTitle()
    {
        return self::statusToString( $this->getStatus() );
    }

    /**
     * Check if cancel allowed
     *
     * @return bool
     */
    public function cancelAllowed()
    {
        $allow_cancel_time = current_time( 'timestamp' ) + (int) Lib\Config::getMinimumTimePriorCancel( $this->getServiceId() );
        $allow_cancel = 'blank';
        if ( ! in_array( $this->getStatus(), array(
            self::STATUS_NOSHOW,
            self::STATUS_CANCELLED,
            self::STATUS_REJECTED,
            self::STATUS_DONE,
        ) ) ) {
            if ( in_array( $this->getStatus(), array(
                    self::STATUS_APPROVED,
                    self::STATUS_PENDING,
                )  ) && $this->getPickupDateTime() === null ) {
                $allow_cancel = 'allow';
            } else {
                if ( $this->getPickupDateTime() > current_time( 'mysql' ) ) {
                    if ( $allow_cancel_time < strtotime( $this->getPickupDateTime() ) ) {
                        $allow_cancel = 'allow';
                    } else {
                        $allow_cancel = 'deny';
                    }
                } else {
                    $allow_cancel = 'expired';
                }
            }
        }

        return $allow_cancel;
    }

    /**
     * Check if cancel allowed
     *
     * @return bool
     */
    public function rescheduleAllowed()
    {
        $allow_cancel_time = current_time( 'timestamp' ) + (int) Lib\Config::getMinimumTimePriorCancel( $this->getServiceId() );

        $allow_reschedule = 'blank';
        if ( ! in_array( $this->getStatus(), array(
                self::STATUS_NOSHOW,
                self::STATUS_CANCELLED,
                self::STATUS_REJECTED,
                self::STATUS_DONE,
            ) ) && $this->getPickupDateTime() !== null ) {
            if ( $this->getPickupDateTime() > current_time( 'mysql' ) ) {
                if ( $allow_cancel_time < strtotime( $this->getPickupDateTime() )  ) {
                    $allow_reschedule = 'allow';
                } else {
                    $allow_reschedule = 'deny';
                }
            } else {
                $allow_reschedule = 'expired';
            }
        }
        return $allow_reschedule;
    }

    /**
     * @param string $reason
     */
    public function cancel( $reason = '', $notification = true )
    {
        $this->setStatus( self::STATUS_CANCELLED );
        $this->setCancellationReason( $reason );
        $this->save();

        if( $notification ) {
            Lib\Notifications\Appointment\Sender::send( $this, array( 'cancellation_reason' => $reason ) );
        }
    }

    /**
     * @param string $reason
     */
    public function noshow( $reason = '', $notification = true )
    {
        $this->setStatus( self::STATUS_NOSHOW );
        $this->setCancellationReason( $reason );
        $this->save();

        if( $notification ) {
            Lib\Notifications\Appointment\Sender::send( $this, array( 'cancellation_reason' => $reason ) );
        }
    }

    /**
     * @param string $reason
     */
    public function isRefundAble()
    {
        if( 
            $this->getPaymentType() == self::PAYMENT_TYPE_SQUARE && 
            $this->getWcOrderId() && 
            $this->getPaidAmount() &&
            ! $this->isRefunded()
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param string $reason
     */
    public function refund()
    {
        if( ! $this->isRefundAble() ) {
            return false;
        }

        $order_id = $this->getWcOrderId();
        $refund_amount = $this->getPaidAmount();

        $response = WooCommerce::refundAppointments( $order_id, $refund_amount );

        if( $response && $response['success'] ) {
            $this
                ->setPaidAmount( 0 )
                ->setRefundedAmount( $refund_amount )
                ->setPaymentStatus( self::PAYMENT_REFUNDED )
                ->save();
        }

        return $response ?: $response;
    }

    /**
     * @return bool
     */
    public function isJustCreated()
    {
        return $this->just_created;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setJustCreated( $value )
    {
        $this->just_created = $value;

        return $this;
    }

    /**
     * @return bool
     */
    public function isStatusChanged()
    {
        return $this->status != $this->last_status;
    }


    /**************************************************************************
     * Entity Fields Getters & Setters                                        *
     **************************************************************************/

    /**
     * Gets service_id
     *
     * @return int
     */
    public function getService()
    {
        return Service::find( $this->getServiceId() );
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
     * Gets schedule_id
     *
     * @return int
     */
    public function getScheduleId()
    {
        return $this->schedule_id;
    }

    /**
     * Sets schedule_id
     *
     * @param int $schedule_id
     * @return $this
     */
    public function setScheduleId( $schedule_id )
    {
        $this->schedule_id = $schedule_id;

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
        return (int) $this->distance;
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
     * Gets estimated_time
     *
     * @return int
     */
    public function getEstimatedTime()
    {
        return $this->estimated_time;
    }

    /**
     * Sets estimated_time
     *
     * @param int $estimated_time
     * @return $this
     */
    public function setEstimatedTime( $estimated_time )
    {
        $this->estimated_time = $estimated_time;

        return $this;
    }

    /**
     * Gets estimated_time
     *
     * @return int
     */
    public function getEstimatedTimeInMins()
    {
        return Lib\Utils\Common::getTimeInMinutes( $this->estimated_time );
    }

    /**
     * Gets waiting_time
     *
     * @return int
     */
    public function getWaitingTime()
    {
        return (int) $this->waiting_time;
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
     * Gets admin_notes
     *
     * @return string
     */
    public function getAdminNotes()
    {
        return $this->admin_notes;
    }

    /**
     * Sets admin_notes
     *
     * @param string $admin_notes
     * @return $this
     */
    public function setAdminNotes( $admin_notes )
    {
        $this->admin_notes = $admin_notes;

        return $this;
    }

    /**
     * Gets customer
     * @param Customer $customer
     * @return $this
     */
    public function getCustomer()
    {
        return Customer::find( $this->getCustomerId() );
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
     * Gets sub_service_data
     *
     * @return string
     */
    public function getSubService()
    {
        return new SubService( $this->sub_service_key, json_decode($this->sub_service_data) );
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
     * Gets is_no_show
     *
     * @return string
     */
    public function getIsNoShow()
    {
        return $this->status == self::STATUS_NOSHOW;
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
     * Gets patient_name
     *
     * @return string
     */
    public function getPatientName()
    {
        return $this->patient_name;
    }

    /**
     * Sets patient_name
     *
     * @param string $patient_name
     * @return $this
     */
    public function setPatientName( $patient_name )
    {
        $this->patient_name = $patient_name;

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
     * Gets cancellation_reason
     *
     * @return string
     */
    public function getCancellationReason()
    {
        return $this->cancellation_reason;
    }

    /**
     * Sets cancellation_reason
     *
     * @param string $cancellation_reason
     * @return $this
     */
    public function setCancellationReason( $cancellation_reason )
    {
        $this->cancellation_reason = $cancellation_reason;

        return $this;
    }

    /**
     * Gets status_changed_at
     *
     * @return string
     */
    public function getStatusChangedAt()
    {
        return $this->status_changed_at;
    }

    /**
     * Sets status_changed_at
     *
     * @param string $status_changed_at
     * @return $this
     */
    public function setStatusChangedAt( $status_changed_at )
    {
        $this->status_changed_at = $status_changed_at;

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
     * Gets paid_amount
     *
     * @return string
     */
    public function getPaidAmount()
    {
        return $this->paid_amount;
    }

    /**
     * Sets paid_amount
     *
     * @param string $paid_amount
     * @return $this
     */
    public function setPaidAmount( $paid_amount )
    {
        $this->paid_amount = $paid_amount;

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
     * Gets payment_date
     *
     * @return string
     */
    public function getPaymentDate()
    {
        return $this->payment_date;
    }

    /**
     * Sets payment_date
     *
     * @param string $payment_date
     * @return $this
     */
    public function setPaymentDate( $payment_date )
    {
        $this->payment_date = $payment_date;

        return $this;
    }

    /**
     * Gets payment_details
     *
     * @return string
     */
    public function getPaymentDetails()
    {
        return $this->payment_details;
    }

    /**
     * Sets payment_details
     *
     * @param string $payment_details
     * @return $this
     */
    public function setPaymentDetails( $payment_details )
    {
        $this->payment_details = $payment_details;

        return $this;
    }

    /**
     * Gets payment_details
     *
     * @return string
     */
    public function getPaymentAdjustments()
    {
        $payment_details = !empty($this->payment_details) ? json_decode($this->payment_details, true) : [];
        return isset($payment_details['adjustments']) ? $payment_details['adjustments'] : [];
    }

    /**
     * Gets is_refunded
     *
     * @return string
     */
    public function isRefunded()
    {
        return ( $this->getPaymentStatus() == self::PAYMENT_REFUNDED );
    }

    /**
     * Gets refunded_amount
     *
     * @return string
     */
    public function getRefundedAmount()
    {
        return $this->refunded_amount;
    }

    /**
     * Sets refunded_amount
     *
     * @param string $refunded_amount
     * @return $this
     */
    public function setRefundedAmount( $refunded_amount )
    {
        $this->refunded_amount = $refunded_amount;

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

    /**
     * Get HTML for pickup information
     *
     * @return string
     */
    public function getScheduleDetail()
    {
        $subService = $this->getSubService();

        // Determine display time zone
        $display_tz = Lib\Utils\Common::getCurrentUserTimeZone();
        $wp_tz = Lib\Config::getWPTimeZone();

        $pickupDatetime = $this->getPickupDateTime();
        $returnDatetime = $this->getReturnPickupDatetime();

        if ( $display_tz !== $wp_tz ) {
            $pickupDatetime = DateTime::convertTimeZone( $pickupDatetime, $wp_tz, $display_tz );
            $returnDatetime   = $returnDatetime ? DateTime::convertTimeZone( $returnDatetime, $wp_tz, $display_tz ) : null;
        }

        $items = [
            [
                'label' => __('Pickup Time', 'connectpx_booking'),
                'value' => DateTime::formatDateTime( $pickupDatetime ),
            ]
        ];

        if( $subService->isRoundTrip() ) {
            $items[] = [
                'label' => __('Return Pickup Time', 'connectpx_booking'),
                'value' => $returnDatetime ? DateTime::formatDateTime( $returnDatetime ) : __('Not sure', 'connectpx_booking'),
            ];
        }

        return $items;
    }

    /**
     * Get HTML for pickup information
     *
     * @return string
     */
    public function getScheduleInfo()
    {
        return Lib\Utils\Common::formatedItemsList( $this->getScheduleDetail() );
    }

    /**
     * Get HTML for pickup information
     *
     * @return string
     */
    public function getPickupInfo()
    {
        $info = json_decode( $this->getPickupDetail(), true );
        $info['patient_name'] = $this->getPatientName();
        return $info ? Lib\Utils\Common::formatedPickupInfo( $info ) : null;
    }

    /**
     * Get HTML for pickup information
     *
     * @return string
     */
    public function getDestinationInfo()
    {
        $info = json_decode( $this->getDestinationDetail(), true );
        return $info ? Lib\Utils\Common::formatedDestinationInfo( $info ) : null;
    }

    /**
     * Get HTML for pickup information
     *
     * @return string
     */
    public function getServiceInfo()
    {
        $service = $this->getService();
        $subService = $this->getSubService();

        return Lib\Utils\Common::formatedServiceInfo($this, $service, $subService);
    }

    /**
     * Get HTML for pickup information
     *
     * @return string
     */
    public function getMapLink( $width = "100%", $height = "300" )
    {
        $pickup = json_decode( $this->getPickupDetail(), true );
        $destination = json_decode( $this->getDestinationDetail(), true );

        if( empty($pickup) || empty($destination) ) {
            return;
        }

        $info = [
            'from' => [
                'lat' => $pickup['address']['lat'],
                'lng' => $pickup['address']['lng'],
            ],
            'to' => [
                'lat' => $destination['address']['lat'],
                'lng' => $destination['address']['lng'],
            ],
        ];

        return Lib\Utils\Common::getGoogleMapLink( $info, $width = "100%", $height = "300" );
    }

    /**
     * Get HTML for payment info displayed in a popover in the edit appointment form
     *
     * @param float $paid
     * @param float $total
     * @param string $type
     * @param string $status
     * @return string
     */
    public static function paymentInfo( $total, $type, $status )
    {
        $result = Lib\Utils\Price::format( $total );
        $result .= sprintf(
            ' %s <span%s>%s</span>',
            self::paymentTypeToString( $type ),
            $status == self::PAYMENT_PENDING ? ' class="text-danger"' : '',
            self::paymentStatusToString( $status )
        );

        return $result;
    }

    /**************************************************************************
     * Overridden Methods                                                     *
     **************************************************************************/

    /**
     * Delete entity from database
     *(and delete event in Google Calendar if it exists).
     *
     * @return bool|false|int
     */
    public function delete()
    {
        $result = parent::delete();
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
                self::STATUS_DONE,
                self::STATUS_NOSHOW,
            );
            self::putInCache( __FUNCTION__, $statuses );
        }

        return self::getFromCache( __FUNCTION__ );
    }

    /**
     * Get appointment statuses.
     *
     * @return array
     */
    public static function getCompletedStatuses()
    {
        if ( ! self::hasInCache( __FUNCTION__ ) ) {
            $completedStatuses = array(
                self::STATUS_DONE,
                self::STATUS_NOSHOW,
            );
            self::putInCache( __FUNCTION__, $completedStatuses );
        }

        return self::getFromCache( __FUNCTION__ );
    }

    public static function statusToString( $status )
    {
        switch ( $status ) {
            case self::STATUS_PENDING:    return __( 'Pending',   'connectpx_booking' );
            case self::STATUS_APPROVED:   return __( 'Approved',  'connectpx_booking' );
            case self::STATUS_CANCELLED:  return __( 'Cancelled', 'connectpx_booking' );
            case self::STATUS_REJECTED:   return __( 'Rejected',  'connectpx_booking' );
            case self::STATUS_DONE:       return __( 'Completed', 'connectpx_booking' );
            case self::STATUS_NOSHOW:     return __( 'No Show', 'connectpx_booking' );
            case 'mixed':                 return __( 'Mixed', 'connectpx_booking' );
            default: return '';
        }
    }

    public static function statusToIcon( $status )
    {
        switch ( $status ) {
            case self::STATUS_PENDING:    return 'far fa-clock';
            case self::STATUS_APPROVED:   return 'fas fa-check';
            case self::STATUS_CANCELLED:  return 'fas fa-times';
            case self::STATUS_REJECTED:   return 'fas fa-ban';
            case self::STATUS_DONE:       return 'far fa-check-square';
            case self::STATUS_NOSHOW:       return 'fas fa-user-slash';
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
            case self::PAYMENT_TYPE_LOCAL:       return 'Local';
            case self::PAYMENT_TYPE_SQUARE:       return 'Credit Card';
            default:                      return '';
        }
    }

    /**
     * Get appointment statuses.
     *
     * @return array
     */
    public static function getPaymentStatuses()
    {
        if ( ! self::hasInCache( __FUNCTION__ ) ) {
            $statuses = array(
                self::PAYMENT_COMPLETED,
                self::PAYMENT_PENDING,
                self::PAYMENT_REFUNDED,
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
    public static function paymentStatusToString( $status )
    {
        switch ( $status ) {
            case self::PAYMENT_COMPLETED:  return __( 'Paid', 'connectpx_booking' );
            case self::PAYMENT_PENDING:    return __( 'Unpaid',   'connectpx_booking' );
            case self::PAYMENT_REFUNDED:    return __( 'Refunded',   'connectpx_booking' );
            default:                      return '';
        }
    }

    public static function paymentStatusToIcon( $status )
    {
        switch ( $status ) {
            case self::PAYMENT_PENDING:    return 'far fa-clock';
            case self::PAYMENT_COMPLETED:   return 'fas fa-check';
            case self::PAYMENT_REFUNDED:   return 'fas fa-arrow-left';
            default: return '';
        }
    }

    /**
     * @param array|\stdClass $data
     * @param bool            $overwrite_loaded_values
     * @return $this
     */
    public function setFields( $data, $overwrite_loaded_values = false )
    {
        if ( $data = (array) $data ) {
            if ( $this->last_status === null && array_key_exists( 'status', $data ) ) {
                $this->last_status = $data['status'];
            }
        }

        return parent::setFields( $data, $overwrite_loaded_values );
    }

    /**
     * Save entity to database.
     * Generate token before saving.
     *
     * @return int|false
     */
    public function save()
    {

        if ( $this->getId() == null ) {
            $this
                ->setCreatedAt( current_time( 'mysql' ) )
                ->setUpdatedAt( current_time( 'mysql' ) );
        } elseif ( $this->getModified() ) {
            $this->setUpdatedAt( current_time( 'mysql' ) );
        }

        if ( $this->status != $this->last_status ) {
            $this->setStatusChangedAt( current_time( 'mysql' ) );
        }

        $this->just_created = $this->getId() === null;

        return parent::save();
    }

    /**
     * @param array|\stdClass $data
     * @param bool            $overwrite_loaded_values
     * @return $this
     */
    public function getLineItems( $subService = null )
    {
        $subService = $subService ?: $this->getSubService();
        $payment_details = !empty($this->getPaymentDetails()) ? json_decode($this->getPaymentDetails(), true) : null;
        $payment_adjustments = $payment_details && isset($payment_details['adjustments']) ? $payment_details['adjustments'] : [];

        $lineItems = $subService->paymentLineItems(
            $this->getDistance(),
            $this->getWaitingTime(),
            $this->getIsAfterHours(),
            $this->getIsNoShow(),
            $payment_adjustments
        );

        return $lineItems;
    }

    /**
     * @param array|\stdClass $data
     * @param bool            $overwrite_loaded_values
     * @return $this
     */
    public function getAppointmentData( $customer = null, $subService = null, $lineItems = null )
    {
        $customer = $customer ?: $this->getCustomer();
        $subService = $subService ?: $this->getSubService();
        $lineItems = $lineItems ?: $this->getLineItems( $subService );
        $milesToCharge = $subService->getMilesToCharge( $this->getDistance() );
        $perMilePrice = $subService->getRatePerMile();
        $pickup_details = $this->getPickupDetail() ? json_decode($this->getPickupDetail(), true) : [];
        $destination_details = $this->getDestinationDetail() ? json_decode($this->getDestinationDetail(), true) : [];

        $data['id'] = $this->getId();
        $data['date'] = Lib\Utils\DateTime::formatDate($this->getPickupDateTime(), 'm/d/Y');
        $data['patient'] = $this->getPatientName();
        $data['pickup_time'] = Lib\Utils\DateTime::formatTime($this->getPickupDateTime());
        $data['clinic'] = $destination_details['hospital'] ?? 'N/A';
        $data['address'] = $destination_details['address']['address'] ?? 'N/A';
        // $data['address'] = 'N/A';
        $data['city'] = sprintf("%s, %s", $destination_details['address']['city'], $destination_details['address']['state']);
        $data['zip'] = $destination_details['address']['postcode'] ?: ($customer ? $customer->getPostcode() : 'N/A');
        $data['trip_type'] = $subService->isRoundTrip() ? 'RT' : 'O';
        $data['status'] = self::statusToString($this->getStatus());
        $data['flat_rate'] = isset($lineItems['items']['flat_rate']) 
            ? Lib\Utils\Price::format( $lineItems['items']['flat_rate']['total'] ) 
            : Lib\Utils\Price::format( 0 );
        $data['mileage'] = $milesToCharge;
        $data['mileage_fee'] = Lib\Utils\Price::format( $perMilePrice );
        $data['total_mileage_fee'] = isset($lineItems['items']['milage']) 
            ? Lib\Utils\Price::format( $lineItems['items']['milage']['total'] ) 
            : Lib\Utils\Price::format( 0 );
        $data['after_hours_fee'] = isset($lineItems['items']['after_hours']) 
            ? Lib\Utils\Price::format( $lineItems['items']['after_hours']['total'] ) 
            : Lib\Utils\Price::format( 0 );
        $data['waiting_fee'] = isset($lineItems['items']['waiting_time']) 
            ? Lib\Utils\Price::format( $lineItems['items']['waiting_time']['total'] ) 
            : Lib\Utils\Price::format( 0 );
        $data['no_show_fee'] = isset($lineItems['items']['no_show']) 
            ? Lib\Utils\Price::format( $lineItems['items']['no_show']['total'] ) 
            : Lib\Utils\Price::format( 0 );
        $data['extras'] = Lib\Utils\Price::format( $lineItems['total_adjustments'] );
        $data['total'] = Lib\Utils\Price::format( $lineItems['totals'] );

        return $data;
    }
}