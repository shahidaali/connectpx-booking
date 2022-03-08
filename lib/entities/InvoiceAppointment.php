<?php
namespace ConnectpxBooking\Lib\Entities;

use ConnectpxBooking\Lib;
use ConnectpxBooking\Backend;

/**
 * Class Invoice
 * @package ConnectpxBooking\Lib\Entities
 */
class InvoiceAppointment extends Lib\Base\Entity
{
    /** @var int */
    protected $invoice_id;
    /** @var datetime */
    protected $appointment_id;

    protected static $table = 'connectpx_booking_invoice_appointments';

    protected static $schema = array(
        'id'                       => array( 'format' => '%d' ),
        'invoice_id'               => array( 'format' => '%d', 'reference' => array( 'entity' => 'Invoice' ) ),
        'appointment_id'               => array( 'format' => '%d', 'reference' => array( 'entity' => 'Appointment' ) ),
    );

    /**
     * Gets invoice_id
     *
     * @return float
     */
    public function getInvoiceId()
    {
        return $this->invoice_id;
    }

    /**
     * Sets invoice_id
     *
     * @param float $invoice_id
     * @return $this
     */
    public function setInvoiceId( $invoice_id )
    {
        $this->invoice_id = $invoice_id;

        return $this;
    }

    /**
     * Gets appointment_id
     *
     * @return float
     */
    public function getAppointmentId()
    {
        return $this->appointment_id;
    }

    /**
     * Sets appointment_id
     *
     * @param float $appointment_id
     * @return $this
     */
    public function setAppointmentId( $appointment_id )
    {
        $this->appointment_id = $appointment_id;

        return $this;
    }
}