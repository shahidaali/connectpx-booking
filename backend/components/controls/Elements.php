<?php
namespace ConnectpxBooking\Backend\Components\Controls;

/**
 * Class Elements
 * @package ConnectpxBooking\Backend\Components\Controls
 */
class Elements
{
    /**
     * Render reorder.
     * @param string $class
     */
    public static function renderReorder( $class = '' )
    {
        printf(
            '<i class="fas fa-fw fa-bars text-muted bookly-cursor-move bookly-js-draghandle %s" title="%s"></i>',
            $class,
            esc_attr__( 'Reorder', 'bookly' )
        );
    }
}
