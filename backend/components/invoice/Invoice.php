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
            $pickup_details = $appointment->getPickupDetail() ? json_decode($appointment->getPickupDetail(), true) : [];
            $destination_details = $appointment->getDestinationDetail() ? json_decode($appointment->getDestinationDetail(), true) : [];
            $subService = $appointment->getSubService();
            $payment_details = !empty($appointment->getPaymentDetails()) ? json_decode($appointment->getPaymentDetails(), true) : null;
            $payment_adjustments = $payment_details && isset($payment_details['adjustments']) ? $payment_details['adjustments'] : [];
            $lineItems = $subService->paymentLineItems(
                $appointment->getDistance(),
                $appointment->getWaitingTime(),
                $appointment->getIsAfterHours(),
                $appointment->getIsNoShow(),
                $payment_adjustments
            );
            $milesToCharge = $subService->getMilesToCharge( $appointment->getDistance() );
            $perMilePrice = $subService->getRatePerMile();

            $row['id'] = $appointment->getId();
            $row['date'] = Lib\Utils\DateTime::formatDate($appointment->getPickupDateTime(), 'm/d/Y');
            $row['patient'] = $pickup_details['patient_name'] ?? 'N/A';
            $row['pickup_time'] = Lib\Utils\DateTime::formatTime($appointment->getPickupDateTime());
            $row['clinic'] = $destination_details['hospital'] ?? 'N/A';
            $row['address'] = $destination_details['address']['address'] ?? 'N/A';
            // $row['address'] = 'N/A';
            $row['city'] = sprintf("%s, %s", $destination_details['address']['city'], $destination_details['address']['state']);
            $row['zip'] = $destination_details['address']['postcode'] ?: ($customer ? $customer->getPostcode() : 'N/A');
            $row['trip_type'] = $subService->isRoundTrip() ? 'RT' : 'O';
            $row['status'] = Lib\Entities\Appointment::statusToString($appointment->getStatus());
            $row['flat_rate'] = isset($lineItems['items']['flat_rate']) 
                ? Lib\Utils\Price::format( $lineItems['items']['flat_rate']['total'] ) 
                : Lib\Utils\Price::format( 0 );
            $row['mileage'] = $milesToCharge;
            $row['mileage_fee'] = Lib\Utils\Price::format( $perMilePrice );
            $row['total_mileage_fee'] = isset($lineItems['items']['milage']) 
                ? Lib\Utils\Price::format( $lineItems['items']['milage']['total'] ) 
                : Lib\Utils\Price::format( 0 );
            $row['after_hours_fee'] = isset($lineItems['items']['after_hours']) 
                ? Lib\Utils\Price::format( $lineItems['items']['after_hours']['total'] ) 
                : Lib\Utils\Price::format( 0 );
            $row['waiting_fee'] = isset($lineItems['items']['waiting_time']) 
                ? Lib\Utils\Price::format( $lineItems['items']['waiting_time']['total'] ) 
                : Lib\Utils\Price::format( 0 );
            $row['no_show_fee'] = isset($lineItems['items']['no_show']) 
                ? Lib\Utils\Price::format( $lineItems['items']['no_show']['total'] ) 
                : Lib\Utils\Price::format( 0 );
            $row['extras'] = Lib\Utils\Price::format( $lineItems['total_adjustments'] );
            $row['total'] = Lib\Utils\Price::format( $lineItems['totals'] );

            $appointments[] = $row;
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
        // __pre($data);
        // exit();
        $content = self::renderTemplate( 'backend/components/invoice/templates/invoice', $data, false );

        return $content;
    }
}