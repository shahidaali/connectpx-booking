<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Connectpx Booking
 * Plugin URI:        https://connectpx.com
 * Description:       This plugin provides online booking solution.
 * Version:           1.0.0
 * Author:            Shahid Hussain
 * Author URI:        https://connectpx.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       connectpx_booking
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if(!function_exists('__pre')) {
    function __pre( $code ) {
        echo '<pre>';
        print_r($code);
        echo '</pre>';
    }
}

include_once __DIR__ . '/autoload.php';
ConnectpxBooking\Lib\Plugin::run();


