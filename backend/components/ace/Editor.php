<?php
namespace ConnectpxBooking\Backend\Components\Ace;

use ConnectpxBooking\Lib;

/**
 * Class Widget
 * @package ConnectpxBooking\Backend\Components\Ace
 */
class Editor extends Lib\Base\Component
{
    /**
     * Render the editor
     *
     * @param string $doc_slug
     * @param string $id
     * @param string $codes
     * @param string $value
     */
    public static function render( $doc_slug, $id = 'connectpx_booking-ace-editor', $codes = '', $value = '', $additional_classes = null )
    {
        wp_enqueue_style( 'connectpx_booking_editor' );
        wp_enqueue_script( 'connectpx_booking_editor' );

        self::renderTemplate( 'backend/components/ace/templates/editor', compact( 'id', 'codes', 'value', 'doc_slug', 'additional_classes' ) );
    }
}