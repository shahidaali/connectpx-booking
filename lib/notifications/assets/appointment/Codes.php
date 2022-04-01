<?php
namespace ConnectpxBooking\Lib\Notifications\Assets\Appointment;

use ConnectpxBooking\Lib;
use ConnectpxBooking\Lib\Config;
use ConnectpxBooking\Lib\Entities\Appointment;
use ConnectpxBooking\Lib\Notifications\Base\Reminder;
use ConnectpxBooking\Lib\Notifications\Assets\Base;
use ConnectpxBooking\Lib\Notifications\WPML;
use ConnectpxBooking\Lib\Utils;
use ConnectpxBooking\Lib\Notifications\Assets\Customer\Codes as CustomerCodes;

/**
 * Class Codes
 * @package ConnectpxBooking\Lib\Notifications\Assets\Appointment
 */
class Codes extends Base\Codes
{
    // Core
    public $appointment_return_pickup;
    public $appointment_notes;
    public $appointment_pickup;
    public $cancellation_time_limit;
    public $booking_number;
    public $cancellation_reason;
    public $status;
    public $admin_notes;
    public $service_description;
    public $service_name;
    public $sub_service_name;
    public $trip_type;
    public $flat_rate;
    public $mileage;
    public $mileage_fee;
    public $total_mileage_fee;
    public $waiting_fee;
    public $after_hours_fee;
    public $no_show_fee;
    public $extras_fee;
    public $total_price;
    public $payment_status;
    public $payment_type;
    // Files
    public $files_count;

    /** @var Appointment */
    protected $appointment;
    protected $customer;
    protected $service;
    protected $sub_service;
    /** @var string */
    protected $lang;
    /** @var string */
    protected $recipient;

    /**
     * Prepare codes for given order appointment.
     *
     * @param Appointment $appointment
     * @param string $recipient  "client" or "staff"
     */
    public function __construct( Appointment $appointment, $recipient = null )
    {
        $lang = WPML::getLang();
        $time_prior_cancel = Config::getMinimumTimePriorCancel();

        $subService = $appointment->getSubService();
        $service = $appointment->getService();
        $customer = $appointment->getCustomer();

        $this->appointment = $appointment;
        $this->customer = $customer;
        $this->service = $service;
        $this->sub_service = $subService;
        $this->lang = $lang;
        $this->recipient = $recipient;

        $pickup_details = $appointment->getPickupDetail() ? json_decode($appointment->getPickupDetail(), true) : [];
        $destination_details = $appointment->getDestinationDetail() ? json_decode($appointment->getDestinationDetail(), true) : [];
        $payment_details = !empty($appointment->getPaymentDetails()) ? json_decode($appointment->getPaymentDetails(), true) : null;
        $payment_adjustments = $payment_details && isset($payment_details['adjustments']) ? $payment_details['adjustments'] : [];
        $lineAppointments = $subService->paymentLineItems(
            $appointment->getDistance(),
            $appointment->getWaitingTime(),
            $appointment->getIsAfterHours(),
            $appointment->getIsNoShow(),
            $payment_adjustments
        );
        $milesToCharge = $subService->getMilesToCharge( $appointment->getDistance() );
        $perMilePrice = $subService->getRatePerMile();

        $this->appointment_return_pickup        = $appointment->getReturnPickupDatetime() ? $this->tz( $appointment->getReturnPickupDatetime() ) : null;
        $this->appointment_notes      = $appointment->getNotes();
        $this->appointment_pickup     = $this->tz( $appointment->getPickupDatetime() );
        $this->booking_number         = $appointment->getId();
        $this->client_timezone        = $appointment->getTimeZone() ?: (
            $appointment->getTimeZoneOffset() !== null
                ? 'UTC' . Utils\DateTime::formatOffset( - $appointment->getTimeZoneOffset() * 60 )
                : ''
        );
        $this->service_description           = $service->getDescription();
        $this->service_name           = $service->getTitle();
        $this->admin_notes            = $appointment->getAdminNotes();
        $this->cancellation_time_limit = $time_prior_cancel
            ? $this->tz( Lib\Slots\DatePoint::fromStr( $appointment->getPickupDatetime() )->modify( -$time_prior_cancel )->format( 'Y-m-d H:i:s' ) )
            : null;
        $this->status = Appointment::statusToString( $appointment->getStatus() );;
        $this->sub_service_name = $subService->getTitle();
        $this->appointment_patient = $appointment->getPatientName();
        $this->appointment_clinic = $destination_details['hospital'] ?? 'N/A';
        $this->appointment_address = $destination_details['address']['address'] ?? 'N/A';
        $this->appointment_city_state = sprintf("%s, %s", $destination_details['address']['city'], $destination_details['address']['state']);
        $this->appointment_zip = $destination_details['address']['postcode'] ?: ($customer ? $customer->getPostcode() : 'N/A');
        $this->trip_type = $subService->isRoundTrip() ? 'RT' : 'O';
        $this->flat_rate = isset($lineAppointments['items']['flat_rate']) 
            ? Lib\Utils\Price::format( $lineAppointments['items']['flat_rate']['total'] ) 
            : Lib\Utils\Price::format( 0 );
        $this->mileage = $milesToCharge;
        $this->mileage_fee = Lib\Utils\Price::format( $perMilePrice );
        $this->total_mileage_fee = isset($lineAppointments['items']['milage']) 
            ? Lib\Utils\Price::format( $lineAppointments['items']['milage']['total'] ) 
            : Lib\Utils\Price::format( 0 );
        $this->after_hours_fee = isset($lineAppointments['items']['after_hours']) 
            ? Lib\Utils\Price::format( $lineAppointments['items']['after_hours']['total'] ) 
            : Lib\Utils\Price::format( 0 );
        $this->waiting_fee = isset($lineAppointments['items']['waiting_time']) 
            ? Lib\Utils\Price::format( $lineAppointments['items']['waiting_time']['total'] ) 
            : Lib\Utils\Price::format( 0 );
        $this->no_show_fee = isset($lineAppointments['items']['no_show']) 
            ? Lib\Utils\Price::format( $lineAppointments['items']['no_show']['total'] ) 
            : Lib\Utils\Price::format( 0 );
        $this->extras_fee = Lib\Utils\Price::format( $lineAppointments['total_adjustments'] );
        $this->total_price = Lib\Utils\Price::format( $lineAppointments['totals'] );
        $this->payment_status = Appointment::paymentStatusToString( $appointment->getPaymentStatus() );
        $this->payment_type = Appointment::paymentTypeToString( $appointment->getPaymentType() );
    }

    /**
     * @param array $replace_codes
     * @param string $format
     * @return array
     */
    public function prepareReplaceCodes( $replace_codes, $format )
    {
        // Add replace codes.
        $replace_codes += array(
            'appointment_pickup_date'               => $this->appointment_pickup === null ? __( 'N/A', 'connectpx_booking' ) : Utils\DateTime::formatDate( $this->appointment_pickup ),
            'appointment_pickup_time'               => $this->appointment_pickup === null ? __( 'N/A', 'connectpx_booking' ) : Utils\DateTime::formatTime( $this->appointment_pickup ),
            'appointment_return_pickup_date'           => $this->appointment_return_pickup === null ? __( 'N/A', 'connectpx_booking' ) : Utils\DateTime::formatDate( $this->appointment_return_pickup ),
            'appointment_return_pickup_time'           => $this->appointment_return_pickup === null ? __( 'N/A', 'connectpx_booking' ) : Utils\DateTime::formatTime( $this->appointment_return_pickup ),
            'appointment_notes'              => $format == 'html' ? nl2br( $this->appointment_notes ) : $this->appointment_notes,
            'booking_number'                 => $this->booking_number,
            'cancellation_reason'            => $this->cancellation_reason,
            'service_description'                   => $format == 'html' ? nl2br( $this->service_description ) : $this->service_description,
            'service_name'                   => $this->service_name,
            'admin_notes'                  => $this->admin_notes,
            'cancellation_time_limit'      => $this->cancellation_time_limit
            ? Utils\DateTime::formatDateTime( $this->cancellation_time_limit )
            : __( 'no limit', 'bookly' ),
            'status'                  => $this->status,
            'sub_service_name'                  => $this->sub_service_name,
            'patient_name'                  => $this->appointment_patient,
            'trip_type'                  => $this->trip_type,
            'flat_rate'                  => $this->flat_rate,
            'mileage'                  => $this->mileage,
            'mileage_fee'                  => $this->mileage_fee,
            'total_mileage_fee'                  => $this->total_mileage_fee,
            'waiting_fee'                  => $this->waiting_fee,
            'after_hours_fee'                  => $this->after_hours_fee,
            'no_show_fee'                  => $this->no_show_fee,
            'extras_fee'                  => $this->extras_fee,
            'total_price'                  => $this->total_price,
            'payment_status'                  => $this->payment_status,
            'payment_type'                  => $this->payment_type,
        );

        return $replace_codes;
    }

    /**
     * @inheritDoc
     */
    protected function getReplaceCodes( $format )
    {
        $replace_codes = parent::getReplaceCodes( $format );
        $replace_codes += $this->prepareReplaceCodes( $replace_codes, $format );
        $replace_codes += (new CustomerCodes($this->customer))->getReplaceCodes( $format );
        return $replace_codes;
    }

    /**
     * Apply client time zone to given datetime string in WP time zone if recipient is client
     * and staff time zone if recipient is staff
     *
     * @param string $datetime
     * @return mixed
     */
    public function tz( $datetime )
    {
        if ( $this->forClient() ) {
            return $this->applyAppointmentTz( $datetime, $this->appointment );
        }

        return $datetime;
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
     * Get appointment.
     *
     * @return Appointment
     */
    public function getAppointment()
    {
        return $this->appointment;
    }

    /**
     * Check whether recipient is customer
     *
     * @return bool
     */
    public function forClient()
    {
        return $this->recipient == Reminder::RECIPIENT_CLIENT;
    }

    /**
     * Check whether recipient is admins
     *
     * @return bool
     */
    public function forAdmins()
    {
        return $this->recipient == Reminder::RECIPIENT_ADMINS;
    }
}