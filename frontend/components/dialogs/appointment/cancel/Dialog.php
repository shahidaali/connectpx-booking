<?php
namespace ConnectpxBooking\Frontend\Components\Dialogs\Appointment\Cancel;

use ConnectpxBooking\Lib;

/**
 * Class Dialog
 * @package ConnectpxBooking\Frontend\Components\Dialogs\Appointment\Cancel
 */
class Dialog extends Lib\Base\Component
{
    public static function render( $show_reason )
    {
        static::renderTemplate( 'frontend/components/dialogs/appointment/cancel/templates/cancel', compact( 'show_reason' ) );
    }
}