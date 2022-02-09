<?php
namespace ConnectpxBooking\Lib;

use ConnectpxBooking\Frontend;
use ConnectpxBooking\Backend;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    ConnectpxBooking
 * @subpackage ConnectpxBooking\Lib
 * @author     Shahid Hussain <shahidhussainaali@gmail.com>
 */
class Plugin {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $pluginName    The string used to uniquely identify this plugin.
	 */
	protected static $pluginName = 'connectpx_booking';

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected static $version = '1.0.0';


	/**
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public static function run() {

		$self = get_called_class();
		add_action( 'plugins_loaded', array( $self, 'loadPluginTextdomain' ) );

		if( is_admin() ) {
			Backend\Backend::run();
		}

		Frontend\Frontend::run();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Optimalsort_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public static function loadPluginTextdomain() {
		load_plugin_textdomain(
			self::pluginName(),
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}
	
	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public static function pluginName() {
		return self::$pluginName;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public static function pluginUrl() {
		return plugin_dir_url( __FILE__ );
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public static function pluginDir() {
		return plugin_dir_path(dirname( __FILE__ ));
	}

	/**
	 *
	 * @since     1.0.0
	 */
	public static function version() {
		return self::$version;
	}

}
