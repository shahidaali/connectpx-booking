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
		Components\Dialogs\Schedule\Edit\Ajax::init();
		Components\Dialogs\Invoice\Edit\Ajax::init();
		Components\Dialogs\Invoice\View\Ajax::init();
		Components\Dialogs\Notifications\Ajax::init();
		Components\Dashboard\Appointments\Ajax::init();

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

		// Ace Editor
		wp_register_script( 
			'connectpx_booking_editor_ace', 
			$admin_resources . 'components/ace/resources/js/ace.js', 
			array(), 
			Plugin::version(), 
			false 
		);
		wp_register_script( 
			'connectpx_booking_editor_ext_language', 
			$admin_resources . 'components/ace/resources/js/ext-language_tools.js', 
			array(), 
			Plugin::version(), 
			false 
		);
		wp_register_script( 
			'connectpx_booking_editor_mode', 
			$admin_resources . 'components/ace/resources/js/mode-connectpx_booking.js', 
			array(), 
			Plugin::version(), 
			false 
		);
		wp_register_script( 
			'connectpx_booking_editor', 
			$admin_resources . 'components/ace/resources/js/editor.js', 
			array(
				'connectpx_booking_editor_ace',
				'connectpx_booking_editor_ext_language',
				'connectpx_booking_editor_mode',
			), 
			Plugin::version(), 
			false 
		);
		wp_register_style( 
			'connectpx_booking_editor', 
			$admin_resources . 'components/ace/resources/css/ace.css', 
			array(), 
			Plugin::version(), 
			'all' 
		);

		// Admin Scripts
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
			'connectpx_booking_schedules', 
			$admin_resources . 'modules/resources/js/schedules.js', 
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

		// Notifications
		wp_enqueue_script( 
			'connectpx_booking_notifications_list', 
			$admin_resources . 'modules/resources/js/notifications-list.js', 
			array(), 
			Plugin::version(), 
			false 
		);
		wp_enqueue_script( 
			'connectpx_booking_email_logs', 
			$admin_resources . 'modules/resources/js/email-logs.js', 
			array(), 
			Plugin::version(), 
			false 
		);
		wp_register_script( 
			'connectpx_booking_notifications', 
			$admin_resources . 'modules/resources/js/notifications.js', 
			array( 
				'jquery', 
				'connectpx_booking_global',
				'connectpx_booking_datatables', 
				'connectpx_booking_moment', 
				'connectpx_booking_daterangepicker',
				'connectpx_booking_select2',
				'connectpx_booking_notifications_list',
				'connectpx_booking_email_logs'
			), 
			Plugin::version(), 
			false 
		);

		// Notification Dialog
		wp_register_script( 
			'connectpx_booking_notification_dialog', 
			$admin_resources . 'components/dialogs/notifications/resources/js/notification-dialog.js', 
			array( 
				'jquery', 
				'connectpx_booking_global',
				'connectpx_booking_bootstrap',
				'connectpx_booking_moment', 
				'connectpx_booking_daterangepicker',
				'connectpx_booking_select2',
				'connectpx_booking_dropdown',
				'connectpx_booking_editor' 
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

		// Dashboard assets
		wp_register_script( 
			'connectpx_booking_chart', 
			$admin_resources . 'components/dashboard/appointments/resources/js/chart.min.js', 
			array(), 
			Plugin::version(), 
			false 
		);
		wp_register_script( 
			'connectpx_booking_appointments_dashboard', 
			$admin_resources . 'components/dashboard/appointments/resources/js/appointments-dashboard.js', 
			array( 
				'jquery', 
				'connectpx_booking_global',
				'connectpx_booking_datatables', 
				'connectpx_booking_moment', 
				'connectpx_booking_daterangepicker',
				'connectpx_booking_select2',
				'connectpx_booking_dropdown',
				'connectpx_booking_chart',
			), 
			Plugin::version(), 
			false 
		);
		wp_enqueue_style( 
			'connectpx_booking_appointments_dashboard', 
			$admin_resources . 'components/dashboard/appointments/resources/css/appointments-dashboard.css', 
			array( 
				'connectpx_booking_bootstrap',
				'connectpx_booking_admin', 
				'connectpx_booking_fa' 
			), 
			Plugin::version(), 
			'all' 
		);

		wp_register_script( 
			'connectpx_booking_appointments_dashboard', 
			$admin_resources . 'components/dashboard/appointments/resources/js/appointments-dashboard.js', 
			array( 
				'jquery', 
				'connectpx_booking_global',
				'connectpx_booking_datatables', 
				'connectpx_booking_moment', 
				'connectpx_booking_daterangepicker',
				'connectpx_booking_select2',
				'connectpx_booking_dropdown',
				'connectpx_booking_chart',
			), 
			Plugin::version(), 
			false 
		);
		wp_register_script( 
			'connectpx_booking_dashboard', 
			$admin_resources . 'modules/resources/js/dashboard.js', 
			array( 
				'jquery', 
				'connectpx_booking_global',
				'connectpx_booking_datatables', 
				'connectpx_booking_moment', 
				'connectpx_booking_daterangepicker',
				'connectpx_booking_select2',
				'connectpx_booking_dropdown',
				'connectpx_booking_appointments_dashboard'
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
        $schedules = __( 'Schedules', 'connectpx_booking' );
        $calendar = __( 'Calendar', 'connectpx_booking' );
        $invoices = __( 'Invoices', 'connectpx_booking' );
        $notifications = __( 'Notifications', 'connectpx_booking' );
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
		add_submenu_page( $slug, $schedules, $schedules, 'manage_options', Modules\Schedules::pageSlug(),
			function() {
				Modules\Schedules::render();
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
		add_submenu_page( $slug, $notifications, $notifications, 'manage_options', Modules\Notifications::pageSlug(),
			function() {
				Modules\Notifications::render();
			}
		);
		add_submenu_page( $slug, $settings, $settings, 'manage_options', Modules\Settings::pageSlug(),
			function() {
				Modules\Settings::render();
			}
		);
	}
}
