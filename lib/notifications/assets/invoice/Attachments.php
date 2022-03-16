<?php
namespace ConnectpxBooking\Lib\Notifications\Assets\Invoice;

use ConnectpxBooking\Lib;
use ConnectpxBooking\Lib\Entities\Notification;
use ConnectpxBooking\Lib\Notifications\Assets\Base;

/**
 * Class Attachments
 * @package ConnectpxBooking\Lib\Notifications\Assets\Invoice
 *
 * @property Codes $codes
 */
class Attachments extends Base\Attachments
{
    /** @var Codes */
    protected $codes;
    
    /**
     * Constructor.
     *
     * @param Codes $codes
     */
    public function __construct( Codes $codes )
    {
        $this->codes = $codes;
    }

    /**
     * @inheritDoc
     */
    public function createFor( Notification $notification )
    {
        if ( ! isset ( $this->files['invoice'] ) ) {
            // Invoices.
            if ( $this->codes->getInvoice() ) {
                $file = $this->codes->getInvoice()->getInvoicePath();
                if ( $file ) {
                    $this->files['invoice'] = $file;
                }
            }
        }

        return isset ( $this->files['invoice'] ) ? array( $this->files['invoice'] ) : array();

        return array();
    }
}