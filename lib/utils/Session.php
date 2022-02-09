<?php
namespace ConnectpxBooking\Lib\Utils;

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
 * @subpackage ConnectpxBooking\Lib\Utils
 * @author     Shahid Hussain <shahidhussainaali@gmail.com>
 */
class Session {

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public static function start() {
		if(!session_id()) {
			session_start();
		}
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public static function get($key, $default = null) {
		return isset($_SESSION['connectpx_booking'][$key]) ? $_SESSION['connectpx_booking'][$key] : $default;
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public static function set($key, $value) {
		self::start();

		$_SESSION['connectpx_booking'][$key] = $value;
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public static function unset($key) {
		unset($_SESSION['connectpx_booking'][$key]);
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public static function reset() {
		self::start();

		$_SESSION['connectpx_booking'] = [];
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public static function set_flash( $message, $type = 'error' ) {
		self::start();

		self::set('flash_message', [
			'type' => $type,
			'message' => $message,
		]);
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public static function falsh_messages( $context = 'admin' ) {
		if(self::get('flash_message')) {
			$flash_message = self::get('flash_message');
			$type = $flash_message['type'];
			$message = is_array($flash_message['message']) ? implode("<br>", $flash_message['message']) : $flash_message['message'];

			self::unset('flash_message');
			if( $context == 'admin' ) {
				return '<div id="setting-error-settings_updated" class="notice notice-'.$type.' settings-error is-dismissible"><p><strong>'.$message.'</strong></p></div>';
			}
		}
	}
}
