<?php
namespace ConnectpxBooking\Backend\Components\Dialogs\Invoice\View;

use ConnectpxBooking\Lib;
use ConnectpxBooking\Lib\Entities;

/**
 * Class Edit
 * @package ConnectpxBooking\Backend\Components\Dialogs\Invoice\Edit
 */
class Dialog extends Lib\Base\Component
{
    /**
     * Render create/edit appointment dialog.
     * @param bool $show_wp_users
     */
    public static function render()
    {
        wp_enqueue_script('connectpx_booking_invoice_view');

        wp_localize_script( 'connectpx_booking_invoice_view', 'ConnectpxBookingL10nAppDialog', array(
            'datePicker' => Lib\Utils\DateTime::datePickerOptions(),
            'l10n' => array(

            ),
        ) );

        self::renderTemplate( 'backend/components/dialogs/invoice/view/templates/view');
    }
}