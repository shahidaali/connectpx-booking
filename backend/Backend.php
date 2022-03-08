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
		Components\Dialogs\Appointment\Edit\Ajax::init();
		Components\Dialogs\Invoice\Edit\Ajax::init();

		add_action( 'admin_enqueue_scripts', array(__CLASS__, 'enqueueScripts') );
		add_action( 'admin_menu', array(__CLASS__, 'adminMenu') );

		// __pre(Lib\Utils\Common::getInvoicePeriodOptions());
		// exit;

		if( 
			( !empty($_GET['page']) && $_GET['page'] == 'connectpx_booking_invoices' ) && 
			( !empty($_GET['tab']) && $_GET['tab'] == 'download' ) && 
			!empty($_GET['id']) 
		) 
		{
			$invoice = Lib\Entities\Invoice::find($_GET['id']);
	        $invoice->downloadInvoice();
	        exit();
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public static function enqueueScripts() {
		Plugin::globalScripts();
		$admin_resources = plugin_dir_url( __FILE__ );

		wp_enqueue_style( 
			'connectpx_booking_admin', 
			$admin_resources . 'resources/css/admin.css', 
			array( 'connectpx_booking_bootstrap', 'connectpx_booking_fa' ), 
			Plugin::version(), 
			'all' 
		);
		wp_enqueue_script( 
			'connectpx_booking_admin', 
			$admin_resources . 'resources/js/admin.js', 
			array( 'jquery', 'connectpx_booking_bootstrap', 'connectpx_booking_global' ), 
			Plugin::version(), 
			false 
		);
		wp_register_script( 
			'connectpx_booking_appointments', 
			$admin_resources . 'modules/resources/js/appointments.js', 
			array( 
				'jquery', 
				'connectpx_booking_global',
				'connectpx_booking_datatables', 
				'connectpx_booking_moment', 
				'connectpx_booking_daterangepicker',
				'connectpx_booking_select2' 
			), 
			Plugin::version(), 
			false 
		);
		wp_register_script( 
			'connectpx_booking_invoices', 
			$admin_resources . 'modules/resources/js/invoices.js', 
			array( 
				'jquery', 
				'connectpx_booking_global',
				'connectpx_booking_datatables', 
				'connectpx_booking_moment', 
				'connectpx_booking_daterangepicker',
				'connectpx_booking_select2' 
			), 
			Plugin::version(), 
			false 
		);

		// Event Calendar
		wp_register_script( 
			'connectpx_booking_event_calendar', 
			$admin_resources . 'modules/resources/js/event-calendar.min.js', 
			array(), 
			Plugin::version(), 
			false 
		);
		wp_register_script( 
			'connectpx_booking_calendar_common', 
			$admin_resources . 'modules/resources/js/calendar-common.js', 
			array(), 
			Plugin::version(), 
			false 
		);
		wp_register_script( 
			'connectpx_booking_calendar', 
			$admin_resources . 'modules/resources/js/calendar.js', 
			array( 
				'jquery', 
				'connectpx_booking_global',
				'connectpx_booking_datatables', 
				'connectpx_booking_moment', 
				'connectpx_booking_daterangepicker',
				'connectpx_booking_select2',
				'connectpx_booking_dropdown',
				'connectpx_booking_event_calendar',
				'connectpx_booking_calendar_common',
			), 
			Plugin::version(), 
			false 
		);
		wp_enqueue_style( 
			'connectpx_booking_calendar', 
			$admin_resources . 'modules/resources/css/event-calendar.min.css', 
			array( 'connectpx_booking_admin' ), 
			Plugin::version(), 
			'all' 
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
        $calendar = __( 'Calendar', 'connectpx_booking' );
        $invoices = __( 'Invoices', 'connectpx_booking' );
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
		add_submenu_page( $slug, $calendar, $calendar, 'manage_options', Modules\Calendar::pageSlug(),
			function() {
				Modules\Calendar::render();
			}
		);
		add_submenu_page( $slug, $invoices, $invoices, 'manage_options', Modules\Invoices::pageSlug(),
			function() {
				Modules\Invoices::render();
			}
		);
		add_submenu_page( $slug, $settings, $settings, 'manage_options', Modules\Settings::pageSlug(),
			function() {
				Modules\Settings::render();
			}
		);
	}
}
