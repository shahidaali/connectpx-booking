<?php
namespace ConnectpxBooking\Backend\Components\Dialogs\Invoice\View;

use ConnectpxBooking\Backend;
use ConnectpxBooking\Lib;

/**
 * Class Ajax
 * @package ConnectpxBooking\Backend\Components\Dialogs\Invoice\Edit
 */
class Ajax extends Lib\Base\Ajax
{
    /**
     * @inheritDoc
     */
    protected static function permissions()
    {
        return array( '_default' => array( 'anonymous' ) );
    }

    /**
     * Get invoice data when editing an invoice.
     */
    public static function renderInvoiceView()
    {
        $statuses = array();
        foreach ( Lib\Entities\Invoice::getStatuses() as $status ) {
            $statuses[] = array(
                'id' => $status,
                'title' => Lib\Entities\Invoice::statusToString( $status ),
                'icon' => Lib\Entities\Invoice::statusToIcon( $status )
            );
        }

        $invoice = Lib\Entities\Invoice::find( self::parameter('id') );
        $preview_table = Backend\Components\Invoice\Invoice::render( $invoice );
        $response['success'] = true;
        $response['data']['html'] = self::renderTemplate( 'backend/components/dialogs/invoice/view/templates/modal', compact( 'invoice', 'preview_table', 'statuses' ), false );
        $response['data']['invoice_id'] = $invoice->getId();
        $response['data']['invoice_status'] = $invoice->getStatus();

        wp_send_json( $response );
    }

    /**
     * Get invoice data when editing an invoice.
     */
    public static function updateInvoice()
    {
        $response = array( 'success' => false );
        $invoice_id       = (int) self::parameter( 'id', 0 );
        $invoice = new Lib\Entities\Invoice();

        if( $invoice->load($invoice_id) ) {
            $invoice->updateTotals();
            $response['success'] = true;
        } else {
            $response['errors'] = array( 'db' => __( 'Invoice not found.', 'connectpx_booking' ) );
        }

        wp_send_json( $response );
    }

    /**
     * Save invoice form (for both create and edit).
     */
    public static function sendInvoiceNotification()
    {
        $response = array( 'success' => false );
        $invoice_id       = (int) self::parameter( 'id', 0 );

        // If no errors then try to save the invoice.
        if ( ! isset ( $response['errors'] ) ) {
            // Single invoice.
            $invoice = new Lib\Entities\Invoice();
            if ( $invoice->load( $invoice_id ) ) {

                Lib\Notifications\Invoice\Sender::send( $invoice );

                $invoice
                    ->setNotificationStatus( 1 );

                if ( $invoice->save() !== false ) {
                    $response['success'] = true;
                } else {
                    $response['errors'] = array( 'db' => __( 'Could not save invoice in database.', 'connectpx_booking' ) );
                }
            }
            
        }

        wp_send_json( $response );
    }
    /**
     * Save invoice form (for both create and edit).
     */
    public static function updateInvoiceStatus()
    {
        $response = array( 'success' => false );
        $invoice_id       = (int) self::parameter( 'id', 0 );
        $invoice_status          = self::parameter( 'invoice_status' );

        // If no errors then try to save the invoice.
        if ( ! isset ( $response['errors'] ) ) {
            // Single invoice.
            $invoice = new Lib\Entities\Invoice();
            if ( $invoice->load( $invoice_id ) ) {
                if( $invoice->getStatus() != $invoice_status ) {

                    $invoice
                        ->setStatus( $invoice_status );

                    if ( $invoice->save() !== false ) {
                        $response['success'] = true;
                    } else {
                        $response['errors'] = array( 'db' => __( 'Could not save invoice in database.', 'connectpx_booking' ) );
                    }
                }
            }
            
        }

        wp_send_json( $response );
    }
}