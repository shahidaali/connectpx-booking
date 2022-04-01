<?php
namespace ConnectpxBooking\Backend\Components\Invoice;

use ConnectpxBooking\Lib;

/**
 * Class Invoice
 * @package ConnectpxBooking\Backend\Components\Invoice
 */
class Invoice extends Lib\Base\Component
{
    /**
     * Render invoice content.
     *
     * @param array $payment_data
     * @return string
     */
    public static function render( Lib\Entities\Invoice $invoice )
    {
        $customer = $invoice->getCustomer();
        $customer = $customer->isLoaded() ? $customer : null;

        // Appointments
        $appointments = [];
        foreach ($invoice->loadAppointments() as $key => $appointment) {
            $appointments[] = $appointment->getAppointmentData( $customer );
        }

        $datatables = Lib\Utils\Tables::getSettings( 'invoice_pdf' );
        $company_details = Lib\Utils\Common::getCompanyDetails();
        $created_at = Lib\Slots\DatePoint::fromStr( $invoice->getCreatedAt() );
        $due_days = Lib\Utils\Common::getOption('invoices_due_days', 30);

        $data['datatables'] = $datatables['invoice_pdf']; 
        $data['company_details'] = $company_details; 
        $data['customer'] = [
            'id' => $customer ? $customer->getId() : 'N/A',
            'full_name' => $customer ? $customer->getFullName() : 'N/A',
            'phone' => $customer ? $customer->getPhone() : 'N/A',
            'address' => $customer ? $customer->getAddress() : 'N/A',
        ]; 
        $data['appointments'] = $appointments; 
        $data['due_text'] = __(sprintf("Due After %d Days of Receipt", $due_days), 'connectpx_booking'); 
        $data['thank_you_text'] = Lib\Utils\Common::getOption('invoices_thank_you_text', ''); 
        $data['due_date'] = Lib\Utils\DateTime::formatDate( $created_at->modify( $due_days * DAY_IN_SECONDS )->format( 'Y-m-d' ) ); 
        $data['invoice']['id'] = $invoice->getId(); 
        $data['invoice']['created_date'] = Lib\Utils\DateTime::formatDate( $invoice->getCreatedAt(), 'd-M-y' ); 
        $data['invoice']['total'] = Lib\Utils\Price::format( $invoice->getTotalAmount() ); 
        $data['invoice']['due_amount'] = Lib\Utils\Price::format( $invoice->getTotalAmount() - $invoice->getPaidAmount() ); 
        // __pre($data);
        // exit();

        $data['colssize'] = [
            // 'id' => esc_html__( '#', 'connectpx_booking' ),
            'date' => 5,
            'patient' => 8,
            'pickup_time' => 5,
            'clinic' => 5,
            'address' => 20,
            'city' => 6,
            'zip' => 4,
            'trip_type' => 3,
            'status' => 5,
            'flat_rate' => 5,
            'mileage' => 4,
            'mileage_fee' => 5,
            'total_mileage_fee' => 5,
            'after_hours_fee' => 5,
            'waiting_fee' => 5,
            'no_show_fee' => 5,
            'total' => 5,
        ];
        $content = self::renderTemplate( 'backend/components/invoice/templates/invoice', $data, false );

        return $content;
    }
}