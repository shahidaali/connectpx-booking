<?php
namespace ConnectpxBooking\Backend;

use ConnectpxBooking\Lib;
use ConnectpxBooking\Lib\Plugin;
use ConnectpxBooking\Lib\Utils;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    ConnectpxBooking
 * @subpackage ConnectpxBooking\Backend
 * @author     Shahid Hussain <shahidhussainaali@gmail.com>
 */
class Backend {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public static function run() {
		Ajax::init();

		add_action( 'admin_enqueue_scripts', array(__CLASS__, 'enqueueScripts') );
		add_action( 'admin_menu', array(__CLASS__, 'adminMenu') );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public static function enqueueScripts() {

		wp_enqueue_style( 
			'connectpx_booking_bootstrap', 
			plugin_dir_url( __FILE__ ) . 'resources/bootstrap/css/bootstrap.min.css', 
			array(), 
			Plugin::version(), 
			'all' 
		);
		wp_enqueue_style( 
			'connectpx_booking_admin', 
			plugin_dir_url( __FILE__ ) . 'resources/css/admin.css', 
			array(), 
			Plugin::version(), 
			'all' 
		);
		wp_enqueue_style( 
			'connectpx_booking_fa', 
			plugin_dir_url( __FILE__ ) . 'resources/css/fontawesome-all.min.css', 
			array(), 
			Plugin::version(), 
			'all' 
		);
		wp_register_script( 
			'connectpx_booking_datatables', 
			plugin_dir_url( __FILE__ ) . 'resources/js/datatables.min.js', 
			array( 'jquery' ), 
			Plugin::version(), 
			false 
		);
		wp_register_script( 
			'connectpx_booking_moment', 
			plugin_dir_url( __FILE__ ) . 'resources/js/moment.min.js', 
			array( 'jquery' ), 
			Plugin::version(), 
			false 
		);
		wp_register_script( 
			'connectpx_booking_range_tools', 
			plugin_dir_url( __FILE__ ) . 'resources/js/range-tools.js', 
			array( 'jquery' ), 
			Plugin::version(), 
			false 
		);
		wp_register_script( 
			'connectpx_booking_daterangepicker', 
			plugin_dir_url( __FILE__ ) . 'resources/js/daterangepicker.js', 
			array( 'connectpx_booking_range_tools' ), 
			Plugin::version(), 
			false 
		);
		wp_register_script( 
			'connectpx_booking_select2', 
			plugin_dir_url( __FILE__ ) . 'resources/js/select2.min.js', 
			array( 'connectpx_booking_range_tools' ), 
			Plugin::version(), 
			false 
		);

		wp_enqueue_script( 
			'connectpx_booking_admin', 
			plugin_dir_url( __FILE__ ) . 'resources/js/admin.js', 
			array( 'jquery' ), 
			Plugin::version(), 
			false 
		);

		wp_localize_script( 'connectpx_booking_admin', 'ConnectpxBookingL10nGlobal', array( 
			'csrf_token' => Lib\Utils\Common::getCsrfToken() 
		) );

		wp_register_script( 
			'connectpx_booking_appointments', 
			plugin_dir_url( __FILE__ ) . 'resources/js/appointments.js', 
			array( 
				'jquery', 
				'connectpx_booking_datatables', 
				'connectpx_booking_moment', 
				'connectpx_booking_daterangepicker',
				'connectpx_booking_select2' 
			), 
			Plugin::version(), 
			false 
		);

	}

	/**
	 * Register admin menu for plugin
	 *
	 * @since    1.0.0
	 */
	public static function adminMenu() {
		$slug = 'connectpx_booking';

		// Translated submenu pages.
        $bookings = __( 'Bookings', 'connectpx_booking' );
        $dashboard = __( 'Dashboard', 'connectpx_booking' );
        $services = __( 'Services', 'connectpx_booking' );
        $customers = __( 'Customers', 'connectpx_booking' );
        $appointments = __( 'Appointments', 'connectpx_booking' );
        $settings = __( 'Settings', 'connectpx_booking' );

		add_menu_page( $bookings, $bookings, 'manage_options', $slug, '', 'dashicons-admin-settings', 100 ); 

		add_submenu_page( $slug, $dashboard, $dashboard, 'manage_options', $slug,
			function() {
				Modules\Dashboard::render();
			}
		);
		add_submenu_page( $slug, $services, $services, 'manage_options', Modules\Services::pageSlug(),
			function() {
				Modules\Services::render();
			}
		);
		add_submenu_page( $slug, $customers, $customers, 'manage_options', Modules\Customers::pageSlug(),
			function() {
				Modules\Customers::render();
			}
		);
		add_submenu_page( $slug, $appointments, $appointments, 'manage_options', Modules\Appointments::pageSlug(),
			function() {
				Modules\Appointments::render();
			}
		);
		add_submenu_page( $slug, $settings, $settings, 'manage_options', Modules\Settings::pageSlug(),
			function() {
				Modules\Settings::render();
			}
		);
	}
}
