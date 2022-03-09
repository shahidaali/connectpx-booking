<?php
namespace ConnectpxBooking\Backend\Components\Controls;

use ConnectpxBooking\Lib;

/**
 * Class Container
 * @package ConnectpxBooking\Backend\Components\Controls
 */
class Container extends Lib\Base\Component
{
    /**
     * Render header for container.
     *
     * @param string $title
     * @param string $id
     * @param bool   $opened
     */
    public static function renderHeader( $title, $id = null, $opened = true )
    {
        if ( empty( $id ) ) {
            $id = 'container_' . mt_rand( 10000, 99999 );
        }
        $opened = (boolean) $opened;
        self::renderTemplate( 'backend/components/controls/templates/container', compact( 'title', 'id', 'opened' ) );
    }

    /**
     * Render the end of container.
     */
    public static function renderFooter()
    {
        print '</div></div>';
    }
}