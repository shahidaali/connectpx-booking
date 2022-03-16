<?php
namespace ConnectpxBooking\Lib\Notifications\Assets\Invoice;

use ConnectpxBooking\Lib;
use ConnectpxBooking\Lib\Config;
use ConnectpxBooking\Lib\Entities\Invoice;
use ConnectpxBooking\Lib\Notifications\Base\Reminder;
use ConnectpxBooking\Lib\Notifications\Assets\Base;
use ConnectpxBooking\Lib\Notifications\WPML;
use ConnectpxBooking\Lib\Utils;
use ConnectpxBooking\Lib\Notifications\Assets\Customer\Codes as CustomerCodes;

/**
 * Class Codes
 * @package ConnectpxBooking\Lib\Notifications\Assets\Invoice
 */
class Codes extends Base\Codes
{
    // Core
    public $invoice_number;
    public $start_date;
    public $end_date;
    public $due_date;
    public $total_amount;
    public $paid_amount;
    public $status;
    // Files
    public $files_count;

    /** @var Invoice */
    protected $invoice;
    protected $customer;
    /** @var string */
    protected $recipient;

    /**
     * Prepare codes for given order invoice.
     *
     * @param Invoice $invoice
     * @param string $recipient  "client" or "staff"
     */
    public function prepareForInvoice( Invoice $invoice, $recipient )
    {
        $customer = $invoice->getCustomer();

        $this->recipient = $recipient;
        $this->invoice = $invoice;
        $this->customer = $customer;

        $this->invoice_number = $invoice->getId();
        $this->start_date = Lib\Utils\DateTime::formatDate( $invoice->getStartDate(), 'd/m/Y' );
        $this->end_date = Lib\Utils\DateTime::formatDate( $invoice->getEndDate(), 'd/m/Y' );
        $this->due_date = Lib\Utils\DateTime::formatDate( $invoice->getDueDate(), 'd/m/Y' );
        $this->total_amount = Lib\Utils\Price::format( $invoice->getTotalAmount() );
        $this->paid_amount = Lib\Utils\Price::format( $invoice->getPaidAmount() );
        $this->invoice_status = Lib\Entities\Invoice::statusToString( $invoice->getStatus() );
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
            'invoice_number'               => $this->invoice_number,
            'start_date'               => $this->start_date,
            'end_date'               => $this->end_date,
            'due_date'               => $this->due_date,
            'total_amount'               => $this->total_amount,
            'paid_amount'               => $this->paid_amount,
            'invoice_status'               => $this->invoice_status,
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
     * Get customer.
     *
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Get invoice.
     *
     * @return Invoice
     */
    public function getInvoice()
    {
        return $this->invoice;
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