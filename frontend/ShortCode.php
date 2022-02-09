<?php
namespace ConnectpxBooking\Frontend;

use ConnectpxBooking\Lib;
use ConnectpxBooking\Lib\Plugin;
use ConnectpxBooking\Lib\Utils;

/**
 * Class ShortCode
 *
 * @package ConnectpxBooking\Frontend\Modules\Booking
 */
class ShortCode extends Lib\Base\Component
{
    /**
     * Init component.
     */
    public static function init()
    {
        // Register short code.
        add_shortcode( 'connectpx-booking-form', array( __CLASS__, 'render' ) );

        // Assets.
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'linkScripts' ) );
    }

    /**
     * Link scripts.
     */
    public static function linkScripts()
    {
        global $wp_locale;

        wp_register_style( 
            'connectpx_booking_shortcode', 
            plugin_dir_url( __FILE__ ) . 'resources/css/shortcode.css', 
            array('connectpx_booking_picker'), 
            Plugin::version(), 
            'all' 
        );

        wp_register_script( 
            'connectpx_booking_shortcode', 
            plugin_dir_url( __FILE__ ) . 'resources/js/shortcode.js', 
            array(
                'jquery', 
                'connectpx_booking_picker', 
                'connectpx_booking_google_maps',
                'connectpx_booking_moment'
            ), 
            Plugin::version(), 
            false 
        );

        wp_localize_script( 'connectpx_booking_shortcode', 'ConnectpxBookingL10n', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'csrf_token' => Lib\Utils\Common::getCsrfToken(),
            'today' => __( 'Today', 'bookly' ),
            'months' => array_values( $wp_locale->month ),
            'days' => array_values( $wp_locale->weekday ),
            'daysShort' => array_values( $wp_locale->weekday_abbrev ),
            'monthsShort' => array_values( $wp_locale->month_abbrev ),
            'nextMonth' => __( 'Next month', 'bookly' ),
            'prevMonth' => __( 'Previous month', 'bookly' ),
            'show_more' => __( 'Show more', 'bookly' ),
        ) );
    }

    /**
     * Render ConnectpxBooking shortcode.
     *
     * @param $attributes
     * @return string
     * @throws
     */
    public static function render( $atts )
    {
        wp_enqueue_style('connectpx_booking_shortcode');
        wp_enqueue_script('connectpx_booking_shortcode');

        extract(shortcode_atts([
            'service_id' => 0,
        ], $atts));

        $shortcode_options = array(
            'service_id' => $service_id,
        );

        return self::renderTemplate(
            'frontend/templates/shortcode',
            ['shortcode_options' => $shortcode_options],
            false
        );
    }

    /**
     * Check whether current posts have shortcode 'bookly-form'
     *
     * @return bool
     */
    protected static function postsHaveShortCode()
    {
        static $result;

        if ( $result === null ) {
            $result = Lib\Utils\Common::postsHaveShortCode( 'connectpx-booking-form' );
        }

        return $result;
    }
}