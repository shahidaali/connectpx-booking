<?php
namespace ConnectpxBooking\Lib\Notifications\Assets\Order;

use ConnectpxBooking\Lib\Config;
use ConnectpxBooking\Lib\Entities;
use ConnectpxBooking\Lib\Entities\Customer;
use ConnectpxBooking\Lib\Notifications\Assets\Base;
use ConnectpxBooking\Lib\UserBookingData;
use ConnectpxBooking\Lib\Utils;
use ConnectpxBooking\Lib\Base\Component;
use ConnectpxBooking\Lib\Notifications\Assets\Customer\Codes as CustomerCodes;
use ConnectpxBooking\Lib\Notifications\Assets\Appointment\Codes as AppointmentCodes;
use ConnectpxBooking\Lib\Notifications\Assets\Schedule\Codes as ScheduleCodes;

/**
 * Class Codes
 * @package ConnectpxBooking\Lib\Notifications\Assets\Order
 */
class Codes extends Base\Codes
{
    // Core
    public $payment_type;
    public $payment_status;
    public $amount_due;
    public $amount_paid;
    public $amount_total;
    public $appointments_table;
    public $service_description;
    public $service_name;

    /** @var Schedule */
    protected $schedule;
    /** @var Appointment */
    protected $appointment;
    /** @var Appointment */
    protected $appointments;
    /** @var Customer */
    protected $customer;

    /**
     * Constructor.
     *
     * @param Order $order
     */
    public function __construct( $schedule, array $appointments, Customer $customer )
    {
        $first_appointment = $appointments[0];
        $this->schedule = $schedule;
        $this->appointment = $first_appointment;
        $this->appointments = $appointments;
        $this->customer = $customer;

        $service = $this->appointment->getService();
        $this->payment_type = $this->appointment->getPaymentType();
        $this->payment_status = $this->appointment->getPaymentStatus();
        
        $appointments_rows = [];

        $total_amount = 0;
        $paid_amount = 0;

        foreach ($appointments as $appointment) {
            $total_amount += $appointment->getTotalAmount();
            $paid_amount += $appointment->getPaidAmount();
            $appointments_rows[] = $appointment->getAppointmentData( $customer );
        }

        $datatables = Utils\Tables::getSettings( 'booking_email' );

        $appointments_data['datatables'] = $datatables['booking_email']; 
        $appointments_data['appointments'] = $appointments_rows;
        $appointments_data['total_amount'] = Utils\Price::format( $total_amount ); 
        $appointments_data['paid_amount'] = Utils\Price::format( $paid_amount ); 

        $this->appointments_table = Component::renderTemplate( 'lib/notifications/cart/templates/appointments_table', $appointments_data, false );
        $this->amount_total = $total_amount;
        $this->amount_paid = $paid_amount;
        $this->amount_due = $total_amount - $paid_amount;
        $this->service_description           = $service->getDescription();
        $this->service_name           = $service->getTitle();
    }

    /**
     * @inheritDoc
     */
    protected function getReplaceCodes( $format )
    {
        $replace_codes = parent::getReplaceCodes( $format );
        // $replace_codes += (new CustomerCodes($this->customer))->getReplaceCodes( $format );
        $replace_codes += (new AppointmentCodes($this->appointment))->getReplaceCodes( $format );

        if( $this->schedule ) {
            $replace_codes += (new ScheduleCodes($this->schedule))->getReplaceCodes( $format );
        }

        // Add replace codes.
        $replace_codes += array(
            'payment_type' => Entities\Appointment::paymentTypeToString( $this->payment_type ),
            'payment_status' => Entities\Appointment::paymentStatusToString( $this->payment_status ),
            'amount_due' => Utils\Price::format( $this->amount_due ),
            'amount_paid' => Utils\Price::format( $this->amount_paid ),
            'amount_total' => Utils\Price::format( $this->amount_total ),
            'service_description' => $this->service_description,
            'service_name' => $this->service_name,
            'appointments_table' => $this->appointments_table,
        );

        return $replace_codes;
    }

    /**
     * Apply client time zone to given datetime string in WP time zone.
     *
     * @param string $datetime
     * @param Appointment $ppointment
     * @return mixed
     */
    public function applyAppointmentTz( $datetime, Appointment $ppointment )
    {
        if ( $datetime != '' ) {
            $time_zone = $ppointment->getTimeZone();
            $time_zone_offset = $ppointment->getTimeZoneOffset();

            if ( $time_zone !== null ) {
                $datetime = date_create( $datetime . ' ' . Config::getWPTimeZone() );

                return date_format( date_timestamp_set( date_create( $time_zone ), $datetime->getTimestamp() ), 'Y-m-d H:i:s' );
            } else if ( $time_zone_offset !== null ) {
                return Utils\DateTime::applyTimeZoneOffset( $datetime, $time_zone_offset );
            }
        }

        return $datetime;
    }

    /**
     * Get order.
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }
}