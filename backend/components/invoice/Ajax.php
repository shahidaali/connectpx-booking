<?php
namespace ConnectpxBooking\Backend\Components\Invoice;

use ConnectpxBooking\Lib;
use ConnectpxBooking\Backend;

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
        $invoice = Lib\Entities\Invoice::find( self::parameter('id') );
        $preview_table = Backend\Components\Invoice\Invoice::render( $invoice );
        // $preview = self::renderTemplate( 'backend/components/invoice/templates/preview', compact( 'invoice' ), true );
        $response['data']['invoice'] = $invoice;
        $response['data']['preview'] = $preview_table;

        wp_send_json( $response );
    }
}