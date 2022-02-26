<?php
namespace ConnectpxBooking\Lib;

use ConnectpxBooking\Lib;
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
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public static function globalScripts() {
		$global_resources = plugin_dir_url( __FILE__ );
		$admin_resources = plugin_dir_url( __FILE__ ) . "../backend/";
		$front_resources = plugin_dir_url( __FILE__ ) . "../frontend/";

		// Date Time Picker
		wp_register_style( 
			'connectpx_booking_picker_base', 
            $front_resources . 'resources/js/pickadate/themes/classic.css', 
			array(), 
			Plugin::version(), 
			'all' 
		);
		wp_register_style( 
			'connectpx_booking_picker_time', 
            $front_resources . 'resources/js/pickadate/themes/classic.time.css', 
			array(), 
			Plugin::version(), 
			'all' 
		);
		wp_register_style( 
			'connectpx_booking_picker', 
            $front_resources . 'resources/js/pickadate/themes/classic.date.css', 
			array('connectpx_booking_picker_base', 'connectpx_booking_picker_time'), 
			Plugin::version(), 
			'all' 
		);
		wp_register_script( 
            'connectpx_booking_picker_base', 
            $front_resources . 'resources/js/pickadate/picker.js', 
            array('jquery'), 
            Plugin::version(), 
            false 
        );
		wp_register_script( 
            'connectpx_booking_picker_time', 
            $front_resources . 'resources/js/pickadate/picker.time.js', 
            array(), 
            Plugin::version(), 
            false 
        );
		wp_register_script( 
            'connectpx_booking_picker', 
            $front_resources . 'resources/js/pickadate/picker.date.js', 
            array('connectpx_booking_picker_base', 'connectpx_booking_picker_time'), 
            Plugin::version(), 
            false 
        );

		// Bootstrap
		wp_register_style( 
			'connectpx_booking_bootstrap', 
			$global_resources . 'resources/bootstrap/css/bootstrap.min.css', 
			array(), 
			Plugin::version(), 
			'all' 
		);
		wp_register_script( 
			'connectpx_booking_bootstrap', 
			$global_resources . 'resources/bootstrap/js/bootstrap.min.js', 
			array('jquery'), 
			Plugin::version(), 
			false
		);

		// Font Awesome
		wp_enqueue_style( 
			'connectpx_booking_fa', 
			$global_resources . 'resources/css/fontawesome-all.min.css', 
			array(), 
			Plugin::version(), 
			'all' 
		);

		// Datatables
		wp_register_script( 
			'connectpx_booking_datatables', 
			$global_resources . 'resources/js/datatables.min.js', 
			array( 'jquery' ), 
			Plugin::version(), 
			false 
		);

		// Moment.js
		wp_register_script( 
			'connectpx_booking_moment', 
			$global_resources . 'resources/js/moment.min.js', 
			array( 'jquery' ), 
			Plugin::version(), 
			false 
		);

		// Other
		wp_register_script( 
			'connectpx_booking_alert', 
			$global_resources . 'resources/js/alert.js', 
			array( 'jquery' ), 
			Plugin::version(), 
			false 
		);
		wp_register_script( 
			'connectpx_booking_range_tools', 
			$global_resources . 'resources/js/range-tools.js', 
			array( 'jquery' ), 
			Plugin::version(), 
			false 
		);
		wp_register_script( 
			'connectpx_booking_dropdown', 
			$global_resources . 'resources/js/dropdown.js', 
			array( 'jquery' ), 
			Plugin::version(), 
			false 
		);
		wp_register_script( 
			'connectpx_booking_daterangepicker', 
			$global_resources . 'resources/js/daterangepicker.js', 
			array( 'connectpx_booking_range_tools' ), 
			Plugin::version(), 
			false 
		);
		wp_register_script( 
			'connectpx_booking_select2', 
			$admin_resources . 'resources/js/select2.min.js', 
			array( 'connectpx_booking_range_tools' ), 
			Plugin::version(), 
			false 
		);

		// Global
		wp_register_script( 
			'connectpx_booking_global', 
			$global_resources . 'resources/js/global.js', 
			array('jquery', 'connectpx_booking_alert'), 
			Plugin::version(), 
			false 
		);
		wp_localize_script( 'connectpx_booking_global', 'ConnectpxBookingL10nGlobal', array( 
			'csrf_token' => Lib\Utils\Common::getCsrfToken(),
			'ajax_url' => admin_url( 'admin-ajax.php' )
		) );

		// Appointment Dialog
		wp_register_script( 
			'connectpx_booking_appointments_edit', 
			$admin_resources . 'components/dialogs/appointment/edit/resources/js/appointments_edit.js', 
			array( 
				'jquery', 
				'connectpx_booking_global',
				'connectpx_booking_bootstrap',
				'connectpx_booking_moment', 
				'connectpx_booking_daterangepicker',
				'connectpx_booking_select2' 
			), 
			Plugin::version(), 
			false 
		);
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
