<?php
namespace ConnectpxBooking\Backend\Modules;

use ConnectpxBooking\Lib;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    ConnectpxBooking
 * @subpackage ConnectpxBooking\Backend\Modules
 * @author     Shahid Hussain <shahidhussainaali@gmail.com>
 */
class Dashboard extends Lib\Base\Component {
	protected static $pageSlug = 'connectpx_booking';
	
	/**
     * Render page.
     */
    public static function render()
    {
        self::renderTemplate( 'backend/templates/dashboard' );
    }
	
}
