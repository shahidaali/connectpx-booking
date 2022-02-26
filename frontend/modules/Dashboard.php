<?php
namespace ConnectpxBooking\Frontend\Modules;

use ConnectpxBooking\Lib;

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    ConnectpxBooking
 * @subpackage ConnectpxBooking\Dashboard
 * @author     Shahid Hussain <shahidhussainaali@gmail.com>
 */
class Dashboard {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public static function run() {
		Dashboard\Ajax::init();
	}
}
