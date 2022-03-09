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
class Settings extends Lib\Base\Component {
    protected static $pageSlug = 'connectpx_booking_settings';

	/**
     * Render page.
     */
    public static function render()
    {
        wp_enqueue_media();

        $messages = self::save();
        $business_hours = self::getBusinessHours();
        $slot_length_options = self::getSlotLengthOptions();
        $min_time_requirements = self::getMinimumTimeRequirement();
        $wc_products = self::getWcProducts();
        $appointment_statuses = [
            Lib\Entities\Appointment::STATUS_PENDING => __("Pending", 'connectpx_booking'),
            Lib\Entities\Appointment::STATUS_APPROVED => __("Approved", 'connectpx_booking'),
        ];
        $ntf_processing_intervals = [];
        for ($i=1; $i < 24; $i++) { 
            $ntf_processing_intervals[$i] = $i . " h";
        }
        self::renderTemplate( 'backend/templates/settings', compact(
            'messages', 
            'business_hours', 
            'slot_length_options', 
            'min_time_requirements',
            'wc_products',
            'appointment_statuses',
            'ntf_processing_intervals',
        ) );
    }

    /**
     * Register settings page
     *
     * @since    1.0.0
     */
    public static function save() {
        if ( ! isset( $_POST['connectpx_booking_options'] ) ) {
            return;
        }

        $old_options = Lib\Utils\Common::getOptions();
        $connectpx_booking_options = isset($_POST['connectpx_booking']) ? $_POST['connectpx_booking'] : [];

        $connectpx_booking_options = array_merge($old_options, $connectpx_booking_options);

        // Update options
        update_option( 'connectpx_booking_options',  $connectpx_booking_options );

        Lib\Utils\Common::resetOptions($connectpx_booking_options);

        return [
            'status' => 'success',
            'message' => __( 'Settings saved' )
        ];
    }

    /**
     * Render schedule.
     *
     * @param bool $echo
     * @return string|void
     */
    public static function _buildBusinessHours( $type = "from" )
    {
        $ts_length  = Lib\Config::getTimeSlotLength();
        $time_start = 0;
        $time_end   = DAY_IN_SECONDS;

        // Run the loop.
        $values = [];

        $values["off"] = __('Off', 'connectpx_booking'); 

        if( $type == 'from' ) {
            $time_end -= $ts_length;
        }
        else if( $type == 'to' ) {
            $time_start += $ts_length;
        }

        while ( $time_start <= $time_end ) {
            $values[ Lib\Utils\DateTime::buildTimeString( $time_start ) ] = Lib\Utils\DateTime::formatTime( $time_start );
            $time_start += $ts_length;
        }

        return $values;
    }

    /**
     * Render schedule.
     *
     * @param bool $echo
     * @return string|void
     */
    public static function getSlotLengthOptions()
    {
        $options = [];
        foreach ( array( 5, 10, 12, 15, 20, 30, 45, 60, 90, 120, 180, 240, 360 ) as $duration ) {
            $options[ $duration ] = Lib\Utils\DateTime::secondsToInterval( $duration * MINUTE_IN_SECONDS );
        }
        return $options;
    }
    /**
     * Render schedule.
     *
     * @param bool $echo
     * @return string|void
     */
    public static function getBusinessHours()
    {
        /** @global \WP_Locale $wp_locale */
        global $wp_locale;

        $start_of_week = (int) get_option( 'start_of_week' );

        $days = [];

        for ( $i = 1; $i <= 7; $i ++ ) {
            $day_index = ( $start_of_week + $i ) < 8 ? $start_of_week + $i : $start_of_week + $i - 7;
            $days[ $day_index ] = $wp_locale->weekday[ $day_index == 7 ? 6 : ( $day_index - 1 ) ];
        }

        return [
            'days' => $days,
            'from' => self::_buildBusinessHours('from'),
            'to' => self::_buildBusinessHours('to'),
        ];
    }

    /**
     * @inheritDoc
     */
    public static function getMinimumTimeRequirement()
    {
        $values = array(
            'min_time_prior_booking' => array( 'no' => __( 'Disabled', 'connectpx_booking' ) ),
            'min_time_prior_cancel'  => array( 'no' => __( 'Disabled', 'connectpx_booking' ) ),
        );
        foreach ( array_merge( array( 0.5 ), range( 1, 12 ), range( 24, 144, 24 ), range( 168, 672, 168 ) ) as $hour ) {
            $values['min_time_prior_booking']["$hour"] = Lib\Utils\DateTime::secondsToInterval( $hour * HOUR_IN_SECONDS );
        }
        foreach ( array_merge( array( 1 ), range( 2, 12, 2 ), range( 24, 168, 24 ) ) as $hour ) {
            $values['min_time_prior_cancel']["$hour"] = Lib\Utils\DateTime::secondsToInterval( $hour * HOUR_IN_SECONDS );
        }

        return $values;
    }

    /**
     * @inheritDoc
     */
    public static function getWcProducts()
    {
        global $wpdb;

        $query = 'SELECT ID, post_title FROM ' . $wpdb->posts . ' WHERE post_type = \'product\' AND post_status = \'publish\' ORDER BY post_title';
        $products = $wpdb->get_results( $query );

        $options = array( 0 => __( 'Select Product', 'connectpx_booking' ) );
        foreach ( $products as $product ) {
            $options[$product->ID] = $product->post_title;
        }

        return $options;
    }
}
