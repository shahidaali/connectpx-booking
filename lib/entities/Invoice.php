<?php
namespace ConnectpxBooking\Lib\Entities;

use ConnectpxBooking\Lib;
use ConnectpxBooking\Backend;

/**
 * Class Invoice
 * @package ConnectpxBooking\Lib\Entities
 */
class Invoice extends Lib\Base\Entity
{
    const STATUS_COMPLETED  = 'completed';
    const STATUS_PENDING    = 'pending';

    /** @var int */
    protected $customer_id;
    /** @var datetime */
    protected $start_date;
    /** @var datetime */
    protected $end_date;
    /** @var float */
    protected $total_amount;
    /** @var float */
    protected $paid_amount;
    /** @var string */
    protected $status = self::STATUS_PENDING;
    /** @var string */
    protected $details;
    /** @var string */
    protected $due_date;
    /** @var string */
    protected $notification_status;
    /** @var string */
    protected $created_at;
    /** @var string */
    protected $updated_at;

    protected static $table = 'connectpx_booking_invoices';

    protected static $schema = array(
        'id'                       => array( 'format' => '%d' ),
        'customer_id'               => array( 'format' => '%d', 'reference' => array( 'entity' => 'Customer' ) ),
        'start_date'               => array( 'format' => '%s' ),
        'end_date'                 => array( 'format' => '%s' ),
        'total_amount'          => array( 'format' => '%s' ),
        'paid_amount'          => array( 'format' => '%s' ),
        'status'          => array( 'format' => '%s' ),
        'details'            => array( 'format' => '%s' ),
        'due_date'            => array( 'format' => '%s' ),
        'notification_status'            => array( 'format' => '%d' ),
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
                self::STATUS_COMPLETED,
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
            case self::STATUS_COMPLETED:  return __( 'Paid', 'connectpx_booking' );
            case self::STATUS_PENDING:    return __( 'Unpaid',   'connectpx_booking' );
            default:                      return '';
        }
    }

    public static function statusToIcon( $status )
    {
        switch ( $status ) {
            case self::STATUS_PENDING:    return 'far fa-clock';
            case self::STATUS_COMPLETED:   return 'fas fa-check';
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
     * Gets due_date
     *
     * @return float
     */
    public function getDueDate()
    {
        return $this->due_date;
    }

    /**
     * Sets due_date
     *
     * @param float $due_date
     * @return $this
     */
    public function setDueDate( $due_date )
    {
        $this->due_date = $due_date;

        return $this;
    }

    /**
     * Gets total
     *
     * @return float
     */
    public function getTotalAmount()
    {
        return $this->total_amount;
    }

    /**
     * Sets total_amount
     *
     * @param float $total_amount
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
     * @return float
     */
    public function getPaidAmount()
    {
        return $this->paid_amount;
    }

    /**
     * Sets paid_amount
     *
     * @param float $paid_amount
     * @return $this
     */
    public function setPaidAmount( $paid_amount )
    {
        $this->paid_amount = $paid_amount;

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
     * Gets notification_status
     *
     * @return string
     */
    public function getNotificationStatus()
    {
        return $this->notification_status;
    }

    /**
     * Sets notification_status
     *
     * @param string $notification_status
     * @return $this
     */
    public function setNotificationStatus( $notification_status )
    {
        $this->notification_status = $notification_status;

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
     * @param Lib\CartInfo $cart_info
     * @return $this
     */
    public function setCartInfo( Lib\CartInfo $cart_info )
    {
        $this
            ->setTotal( $cart_info->getTotal() );

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
     * Gets customer_id
     *
     * @return float
     */
    public function getPendingAppointments()
    {
        return Lib\Entities\Appointment::query( 'a' )
            ->select( 'a.*' )
            ->where('a.customer_id', $this->getCustomerId())
            ->whereGte('DATE(a.pickup_datetime)', $this->getStartDate())
            ->whereLte('DATE(a.pickup_datetime)', $this->getEndDate())
            ->whereIn('a.status', [Lib\Entities\Appointment::STATUS_PENDING, Lib\Entities\Appointment::STATUS_APPROVED])
            ->sortBy('a.pickup_datetime')
            ->order('ASC')
            ->fetchArray();
    }

    /**
     * Gets customer_id
     *
     * @return float
     */
    public function getAppointments()
    {
        return Lib\Entities\InvoiceAppointment::query( 'ia' )
            ->select( 'a.*' )
            ->innerJoin( 'Appointment', 'a', 'ia.appointment_id = a.id' )
            ->where('ia.invoice_id', $this->getId())
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
     * @inheritDoc
     */
    public function updateTotals( array $appointments = [] )
    {
        
        $total_amount = 0;    
        $paid_amount = 0;    
        $a_ids = [];  

        if( empty( $appointments ) ) {
            $appointments = Lib\Utils\Invoice::getInvoiceAppointments( $this->getStartDate(), $this->getEndDate(), $this->getCustomerId() );
        }

        if( !empty( $appointments ) ) {
            foreach ($appointments as $key => $appointment_fields) {
                $appointment = new Lib\Entities\Appointment();
                $appointment->setFields( $appointment_fields, true );

                $a_ids[] = $appointment->getId();
                $total_amount += $appointment->getTotalAmount();
                $paid_amount += $appointment->getPaidAmount();

                $invoiceAppointment = new Lib\Entities\InvoiceAppointment();
                $invoiceAppointment->loadBy([
                    'invoice_id' => $this->getId(),
                    'appointment_id' => $appointment->getId(),
                ]);

                if( $invoiceAppointment->isLoaded() ) {
                    $invoiceAppointment->setId( $invoiceAppointment->getId() );
                } else {
                    $invoiceAppointment->setFields([
                        'invoice_id' => $this->getId(),
                        'appointment_id' => $appointment->getId(),
                    ]);
                }

                $invoiceAppointment->save();
            }
        }

        // Delete removed invoice appointments
        Lib\Entities\InvoiceAppointment::query()
            ->where('invoice_id', $this->id)
            ->whereNotIn('appointment_id', $a_ids)
            ->delete();

        $this
            ->setTotalAmount($total_amount)
            ->setPaidAmount($paid_amount)
            ->save();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function updatePaidAmount()
    {
        $this
            ->setStatus( self::STATUS_COMPLETED )
            ->setPaidAmount( $this->getTotalAmount() )
            ->save();

        foreach ($this->loadAppointments() as $appointment) {
            $appointment
                ->setPaidAmount( $appointment->getTotalAmount() )
                ->setStatus( Lib\Entities\Appointment::STATUS_DONE )
                ->save();
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getInvoicePath()
    {
        $pdf  = $this->getInvoicePdf();
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . wp_unique_filename( sys_get_temp_dir(), 'Invoice_' . $this->getId() . '.pdf' );

        $pdf->Output( $path, 'F' );

        return $path;
    }

    /**
     * @inheritDoc
     */
    public function downloadInvoice()
    {
        $pdf = $this->getInvoicePdf();
        $pdf->Output( 'Invoice_' . $this->getId() . '.pdf', 'D' );
        exit();
    }

    /**
     * @param BooklyLib\Entities\Payment $payment
     * @return \TCPDF
     */
    public function getInvoicePdf()
    {
        include_once Lib\Plugin::pluginDir() . '/lib/TCPDF/tcpdf.php';

        $font_name = 'freesans';
        $font_size = $font_name === 'freesans' ? 4 : 2;
        $pdf = new \TCPDF();
        $pdf->setImageScale( 2.3 );
        $pdf->setPrintHeader( false );
        $pdf->setPrintFooter( false );
        $pdf->AddPage();
        $pdf->SetFont( $font_name, '', $font_size );
        $data = Backend\Components\Invoice\Invoice::render( $this );
        $pdf->writeHTML( $data, true, false, true, false, '' );

        return $pdf;
    }

    /**
     * @inheritDoc
     */
    public function getCodes()
    {
        $codes = array(
            '{invoice_number}' => $this->id,
            '{invoice_link}' => $this->id ? admin_url( 'admin-ajax.php?action=connectpx_booking_invoices_download' ) : '',
            '{invoice_date}' => Lib\Utils\DateTime::formatDate( $created_at->format( 'Y-m-d' ) ),
            '{invoice_due_date}' => Lib\Utils\DateTime::formatDate( $created_at->modify( Lib\Utils\Common::getOption('invoices_due_days', 30) * DAY_IN_SECONDS )->format( 'Y-m-d' ) ),
            '{invoice_due_days}' => Lib\Utils\Common::getOption('invoices_due_days', 30),
        );
        return $codes;
    }
}