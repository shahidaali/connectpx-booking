<?php
namespace ConnectpxBooking\Lib\Notifications\Assets\Test;

use ConnectpxBooking\Lib;
use ConnectpxBooking\Lib\DataHolders;
use ConnectpxBooking\Lib\Entities;
use ConnectpxBooking\Lib\Entities\Appointment;
use ConnectpxBooking\Lib\Notifications\Assets;
use ConnectpxBooking\Lib\Utils;

/**
 * Class Codes
 * @package ConnectpxBooking\Lib\Notifications\Assets\Test
 */
class Codes extends Assets\Appointment\Codes
{
    public $cart_info;
    public $new_password;
    public $new_username;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        $customer = new Entities\Customer();
        $customer
            ->setPhone( '12345678' )
            ->setEmail( 'client@example.com' )
            ->setNotes( 'Client notes' )
            ->setFullName( 'Client Name' )
            ->setFirstName( 'Client First Name' )
            ->setLastName( 'Client Last Name' )
            ->setCity( 'City' )
            ->setCountry( 'Country' )
            ->setPostcode( 'Post code' )
            ->setState( 'State' )
            ->setStreet( 'Street' )
            ->setAdditionalAddress( 'Addition address' );

        parent::__construct( new Order( $customer ) );

        $this->item = new Entities\Appointment();

        $pickup_date  = date_create( '-1 month' );
        $event_start = $pickup_date->format( 'Y-m-d 12:00:00' );
        $event_end = $pickup_date->format( 'Y-m-d 13:00:00' );
        $cart_info = array( array(
            'service_name'      => 'Service Name',
            'appointment_pickup' => $event_start,
            'appointment_price' => 24,
        ) );
        $this->amount_due               = '';
        $this->amount_paid              = '';
        $this->appointment_return_pickup          = $event_end;
        $this->appointment_pickup        = $event_start;
        $this->booking_number           = '1';
        $this->cancellation_reason      = 'Some Reason';
        $this->cart_info                = $cart_info;
        $this->client_timezone          = 'UTC';
        $this->new_password             = 'New Password';
        $this->new_username             = 'New User';
        $this->payment_type             = Entities\Appointment::paymentTypeToString( Entities\Appointment::PAYMENT_TYPE_LOCAL );
        $this->service_description             = 'Service info text';
        $this->service_name             = 'Service Name';
        $this->total_price              = '24';
    }

    /**
     * @inheritDoc
     */
    protected function getReplaceCodes( $format )
    {
        $replace_codes = parent::getReplaceCodes( $format );
        return $replace_codes;
    }

    /**
     * @inheritDoc
     */
    public function prepareForItem( Appointment $appointment, $recipient )
    {
        // Do nothing.
    }
}