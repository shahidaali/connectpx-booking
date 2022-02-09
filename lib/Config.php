<?php
namespace ConnectpxBooking\Lib;

use ConnectpxBooking\Lib\Utils\DateTime;

/**
 * Class Config
 * @package ConnectpxBooking\Lib
 *
*/

abstract class Config
{
    /** @var string */
    private static $wp_timezone;

    /**
     * Get available days and available time ranges
     * for the 1st step of booking wizard.
     *
     * @return array
     */
    public static function getDaysAndTimes()
    {
        /** @var \WP_Locale $wp_locale */
        global $wp_locale;

        $result = array(
            'days'  => array(),
            'times' => array(),
        );

        // $res = array_merge(
        //     Entities\StaffScheduleItem::query()
        //         ->select( '`r`.`staff_id`, `r`.`day_index`, MIN(`r`.`start_time`) AS `start_time`, MAX(`r`.`end_time`) AS `end_time`, `st`.`time_zone`' )
        //         ->leftJoin( 'Staff', 'st', '`st`.`id` = `r`.`staff_id`' )
        //         ->whereNot( 'r.start_time', null )
        //         ->where( 'st.visibility', 'public' )
        //         ->groupBy( 'staff_id' )
        //         ->groupBy( 'day_index' )
        //         ->fetchArray(),
        //     Proxy\SpecialDays::getDaysAndTimes() ?: array()
        // );

        $res = [
            [
                'day_index' => 1,
                'start_time' => '00:00:00',
                'end_time' => '23:55:00',
                'time_zone' => NULL,
            ],
            [
                'day_index' => 2,
                'start_time' => '00:00:00',
                'end_time' => '23:55:00',
                'time_zone' => NULL,
            ],
            [
                'day_index' => 3,
                'start_time' => '00:00:00',
                'end_time' => '23:55:00',
                'time_zone' => NULL,
            ],
            [
                'day_index' => 4,
                'start_time' => '00:00:00',
                'end_time' => '23:55:00',
                'time_zone' => NULL,
            ],
            [
                'day_index' => 5,
                'start_time' => '00:00:00',
                'end_time' => '23:55:00',
                'time_zone' => NULL,
            ],
            [
                'day_index' => 6,
                'start_time' => '00:00:00',
                'end_time' => '23:55:00',
                'time_zone' => NULL,
            ],
            [
                'day_index' => 7,
                'start_time' => '00:00:00',
                'end_time' => '23:55:00',
                'time_zone' => NULL,
            ],
        ];

        /** @var Slots\TimePoint $min_start_time */
        /** @var Slots\TimePoint $max_end_time */
        $min_start_time = null;
        $max_end_time   = null;
        $days           = array();
        $wp_tz_offset   = get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;

        foreach ( $res as $row ) {
            $start_time = Slots\TimePoint::fromStr( $row['start_time'] );
            $end_time   = Slots\TimePoint::fromStr( $row['end_time'] );

            if ( $row['time_zone'] ) {
                $staff_tz_offset = DateTime::timeZoneOffset( $row['time_zone'] );
                $start_time = $start_time->toTz( $staff_tz_offset, $wp_tz_offset );
                $end_time = $end_time->toTz( $staff_tz_offset, $wp_tz_offset );
            }

            if ( $min_start_time === null || $min_start_time->gt( $start_time ) ) {
                $min_start_time = $start_time;
            }
            if ( $max_end_time === null || $max_end_time->lt( $end_time ) ) {
                $max_end_time = $end_time;
            }

            // Convert to client time zone.
            $start_time = $start_time->toClientTz();
            $end_time   = $end_time->toClientTz();

            // Add day(s).
            if ( $start_time->value() < 0 ) {
                $prev_day = $row['day_index'] - 1;
                if ( $prev_day < 1 ) {
                    $prev_day = 7;
                }
                $days[ $prev_day ] = true;
            }
            if ( $start_time->value() < HOUR_IN_SECONDS * 24 && $end_time->value() > 0 ) {
                $days[ $row['day_index'] ] = true;
            }
            if ( $end_time->value() > HOUR_IN_SECONDS * 24 ) {
                $next_day = $row['day_index'] + 1;
                if ( $next_day > 7 ) {
                    $next_day = 1;
                }
                $days[ $next_day ] = true;
            }
        }

        $start_of_week = get_option( 'start_of_week' );
        $week_days     = array_values( $wp_locale->weekday_abbrev );

        // Sort days considering start_of_week;
        uksort( $days, function ( $a, $b ) use ( $start_of_week ) {
            $a -= $start_of_week;
            $b -= $start_of_week;
            if ( $a < 1 ) {
                $a += 7;
            }
            if ( $b < 1 ) {
                $b += 7;
            }

            return $a - $b;
        } );

        // Fill days.
        foreach ( array_keys( $days ) as $day_id ) {
            $result['days'][ $day_id ] = $week_days[ $day_id - 1 ];
        }

        if ( $min_start_time && $max_end_time ) {
            $start        = $min_start_time;
            $end          = $max_end_time;
            $client_start = $start->toClientTz();
            $client_end   = $end->toClientTz();

            while ( $start->lte( $end ) ) {
                $result['times'][ Utils\DateTime::buildTimeString( $start->value(), false ) ] = $client_start->formatI18nTime();
                // The next value will be rounded to integer number of hours, i.e. e.g. 8:00, 9:00, 10:00 and so on.
                $start        = $start->modify( HOUR_IN_SECONDS - ( $start->value() % HOUR_IN_SECONDS ) );
                $client_start = $client_start->modify( HOUR_IN_SECONDS - ( $client_start->value() % HOUR_IN_SECONDS ) );
            }
            // The last value should always be the end time.
            $result['times'][ Utils\DateTime::buildTimeString( $end->value(), false ) ] = $client_end->formatI18nTime();
        }

        return $result;
    }

    /**
     * Get array with bounding days for Pickadate.
     *
     * @return array
     */
    public static function getBoundingDaysForPickadate()
    {
        $result = array();

        $minTimeBeforeBooking = 1 * 3600;
        $dp = Slots\DatePoint::now()->modify( $minTimeBeforeBooking )->toClientTz();
        $result['date_min'] = array(
            (int) $dp->format( 'Y' ),
            (int) $dp->format( 'n' ) - 1,
            (int) $dp->format( 'j' ),
        );
        $dp = $dp->modify( ( self::getMaximumAvailableDaysForBooking() - 1 ) . ' days' );
        $result['date_max'] = array(
            (int) $dp->format( 'Y' ),
            (int) $dp->format( 'n' ) - 1,
            (int) $dp->format( 'j' ),
        );

        return $result;
    }

    /**
     * Get array with bounding days for Pickadate.
     *
     * @return array
     */
    public static function getBoundingTimeForPickatime( $selected_date )
    {
        $result = array();

        $minTimeBeforeBooking = 1 * 3600;
        $dpNow = Slots\DatePoint::now();
        $dp = $selected_date ? Slots\DatePoint::fromStr( $selected_date . " " . date("H:i:s") ) : $dpNow;
        $dp->modify( $minTimeBeforeBooking )->toClientTz();
        if( Slots\DatePoint::fromStr( $dp->format( 'Y-m-d' ) )->eq( Slots\DatePoint::fromStr( $dpNow->format( 'Y-m-d' ) ) )  ) {
            $result['date_min'] = array(
                (int) $dp->format( 'H' ),
                (int) $dp->format( 'i' ),
            );
        } else {
            $result['date_min'] = false;
        }
        $result['date_max'] = array(
            23,
            59,
        );

        return $result;
    }

    /**
     * Check whether payment step is disabled.
     *
     * @return bool
     */
    public static function paymentStepDisabled()
    {
        return ! ( self::payLocallyEnabled()
            || ( self::twoCheckoutActive() && get_option( 'connectpx_booking_2checkout_enabled' ) )
            || ( self::authorizeNetActive() && get_option( 'connectpx_booking_authorize_net_enabled' ) )
            || ( self::mollieActive() && get_option( 'connectpx_booking_mollie_enabled' ) )
            || ( self::paysonActive() && get_option( 'connectpx_booking_payson_enabled' ) )
            || ( self::payuBizActive() && get_option( 'connectpx_booking_payu_biz_enabled' ) )
            || ( self::payuLatamActive() && get_option( 'connectpx_booking_payu_latam_enabled' ) )
            || ( self::stripeActive() && get_option( 'connectpx_booking_stripe_enabled' ) )
            || ( Cloud\API::getInstance()->account->productActive( 'stripe' ) && get_option( 'connectpx_booking_cloud_stripe_enabled' ) )
            || self::paypalEnabled()
        );
    }

    /**
     * Check whether multiple services booking is enabled.
     *
     * @return bool
     */
    public static function multipleServicesBookingEnabled()
    {
        return ( Config::cartActive() ||
                 Config::chainAppointmentsActive() ||
                 Config::multiplyAppointmentsActive() ||
                 Config::recurringAppointmentsActive()
        );
    }

    /**
     * @return bool
     */
    public static function payLocallyEnabled()
    {
        return get_option( 'connectpx_booking_pmt_local' ) == 1;
    }

    /**
     * @return bool
     */
    public static function paypalEnabled()
    {
        return self::proActive() && get_option( 'connectpx_booking_paypal_enabled' ) != '0';
    }

    /**
     * @return bool
     */
    public static function twoCheckoutActive()
    {
        return self::__callStatic( '2checkoutActive', array() );
    }

    /**
     * Get time slot length in seconds.
     *
     * @return integer
     */
    public static function getTimeSlotLength()
    {
        return (int) get_option( 'connectpx_booking_gen_time_slot_length', 15 ) * MINUTE_IN_SECONDS;
    }

    /**
     * Check whether service duration should be used instead of slot length on the frontend.
     *
     * @return bool
     */
    public static function useServiceDurationAsSlotLength()
    {
        return (bool) get_option( 'connectpx_booking_gen_service_duration_as_slot_length', false );
    }

    /**
     * Check whether use client time zone.
     *
     * @return bool
     */
    public static function useClientTimeZone()
    {
        return (bool) get_option( 'connectpx_booking_gen_use_client_time_zone' );
    }

    /**
     * @return int
     */
    public static function getMinimumTimePriorBooking()
    {
        return (int) get_option( 'connectpx_booking_gen_min_time_before_booking', 1 ) * 3600;
    }

    /**
     * @return int
     */
    public static function getMaximumAvailableDaysForBooking()
    {
        return (int) get_option( 'connectpx_booking_gen_max_days_for_booking', 365 );
    }

    /**
     * Whether to show calendar in the second step of booking form.
     *
     * @return bool
     */
    public static function showCalendar()
    {
        return (bool) get_option( 'connectpx_booking_app_show_calendar', true );
    }

    /**
     * Whether to use first and last customer name instead full name.
     *
     * @return bool
     */
    public static function showFirstLastName()
    {
        return (bool) get_option( 'connectpx_booking_cst_first_last_name', false );
    }

    /**
     * Whether to use email confirmation.
     *
     * @return bool
     */
    public static function showEmailConfirm()
    {
        return (bool) get_option( 'connectpx_booking_app_show_email_confirm', false );
    }

    /**
     * Whether to show notes field.
     *
     * @return bool
     */
    public static function showNotes()
    {
        return (bool) get_option( 'connectpx_booking_app_show_notes', false );
    }

    /**
     * Whether to show fully booked time slots in the second step of booking form.
     *
     * @return bool
     */
    public static function showBlockedTimeSlots()
    {
        return (bool) get_option( 'connectpx_booking_app_show_blocked_timeslots', false );
    }

    /**
     * Whether to show wide time slots in the time step of booking form.
     *
     * @return bool
     */
    public static function showWideTimeSlots()
    {
        return self::groupBookingActive() && get_option( 'connectpx_booking_group_booking_app_show_nop' );
    }

    /**
     * Whether to show wide time slots in the time step of booking form.
     *
     * @return bool
     */
    public static function showSingleTimeSlot()
    {
        return (bool) get_option( 'connectpx_booking_app_show_single_slot', false );
    }

    /**
     * Whether to show days in the second step of booking form in separate columns or not.
     *
     * @return bool
     */
    public static function showDayPerColumn()
    {
        return (bool) get_option( 'connectpx_booking_app_show_day_one_column', false );
    }

    /**
     * Whether to show login button at the time step of booking form.
     *
     * @return bool
     */
    public static function showLoginButton()
    {
        return (bool) get_option( 'connectpx_booking_app_show_login_button', false );
    }

    /**
     * Whether phone field is required at the Details step or not.
     *
     * @return bool
     */
    public static function phoneRequired()
    {
        return in_array( 'phone', get_option( 'connectpx_booking_cst_required_details', array() ) );
    }

    /**
     * Whether email field is required at the Details step or not.
     *
     * @return bool
     */
    public static function emailRequired()
    {
        return in_array( 'email', get_option( 'connectpx_booking_cst_required_details', array() ) );
    }

    /**
     * @return bool
     */
    public static function addressRequired()
    {
        return get_option( 'connectpx_booking_cst_required_address' ) == 1;
    }

    /**
     * Whether customer duplicates are allowed or not
     *
     * @return bool
     */
    public static function allowDuplicates()
    {
        return get_option( 'connectpx_booking_cst_allow_duplicates' ) == 1;
    }

    /**
     * Whether custom fields attached to services or not.
     *
     * @return bool
     */
    public static function customFieldsPerService()
    {
        return get_option( 'connectpx_booking_custom_fields_per_service' ) == 1;
    }

    /**
     * Whether to show single instance of custom fields for repeating services.
     *
     * @return bool
     */
    public static function customFieldsMergeRepeating()
    {
        return get_option( 'connectpx_booking_custom_fields_merge_repeating' ) == 1;
    }

    /**
     * Whether step Cart is enabled or not.
     *
     * @return bool
     */
    public static function showStepCart()
    {
        return self::cartActive() && get_option( 'connectpx_booking_cart_enabled' ) && ! Config::wooCommerceEnabled();
    }

    /**
     * Check if emails are sent as HTML or plain text.
     *
     * @return bool
     */
    public static function sendEmailAsHtml()
    {
        return get_option( 'connectpx_booking_email_send_as' ) == 'html';
    }

    /**
     * Whether to show only business days in calendar
     *
     * @return bool
     */
    public static function showOnlyBusinessDaysInCalendar()
    {
        return get_option( 'connectpx_booking_cal_show_only_business_days' ) == 1;
    }

    /**
     * Whether to show only business hours in calendar
     *
     * @return bool
     */
    public static function showOnlyBusinessHoursInCalendar()
    {
        return get_option( 'connectpx_booking_cal_show_only_business_hours' ) == 1;
    }

    /**
     * Whether to show only staff members with appointments in calendar Day view or not
     *
     * @return bool
     */
    public static function showOnlyStaffWithAppointmentsInCalendarDayView()
    {
        return get_option( 'connectpx_booking_cal_show_only_staff_with_appointments' ) == 1;
    }

    /**
     * Get business hours settings
     *
     * @return array
     */
    public static function getBusinessHours()
    {
        $result = array();
        foreach ( array( 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday' ) as $week_day ) {
            $result[] = array(
                'start' => get_option( 'connectpx_booking_bh_' . $week_day . '_start' ) ?: null ,
                'end'   => get_option( 'connectpx_booking_bh_' . $week_day . '_end' ) ?: null
            );
        }

        return $result;
    }

    /**
     * Get WordPress time zone setting.
     *
     * @return string
     */
    public static function getWPTimeZone()
    {
        if ( self::$wp_timezone === null ) {
            if ( $timezone = get_option( 'timezone_string' ) ) {
                // If site timezone string exists, return it.
                self::$wp_timezone = $timezone;
            } else {
                // Otherwise return offset.
                $gmt_offset = get_option( 'gmt_offset' );
                self::$wp_timezone = Utils\DateTime::formatOffset( $gmt_offset * HOUR_IN_SECONDS );
            }
        }

        return self::$wp_timezone;
    }

    /**
     * Get default appointment status
     *
     * @return string
     */
    public static function getDefaultAppointmentStatus()
    {
        $status = get_option( 'connectpx_booking_appointment_default_status' );
        if ( ! in_array( $status, CustomerAppointment::getStatuses() ) ) {
            $status = CustomerAppointment::STATUS_APPROVED;
        }

        return $status;
    }

    /**
     * Is connectpx_booking setup in progress
     *
     * @return bool
     */
    public static function setupMode()
    {
        return (bool) get_option( 'connectpx_booking_setup_step', false );
    }

    /******************************************************************************************************************
     * Add-ons                                                                                                        *
     ******************************************************************************************************************/

    /**
     * WooCommerce Plugin enabled or not.
     *
     * @return bool
     */
    public static function wooCommerceEnabled()
    {
        return ( self::proActive() &&  get_option( 'connectpx_booking_wc_enabled' ) && get_option( 'connectpx_booking_wc_product' ) && class_exists( 'WooCommerce', false ) && ( wc_get_cart_url() !== false ) );
    }

    /**
     * Call magic functions.
     *
     * @param $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic( $name , array $arguments )
    {
        // <add-on>Active
        // <add-on>Enabled
        if ( preg_match( '/^(\w+)Active/', $name, $match ) ) {
            // Check if Pro Active
            /** @var \ConnectpxBookingPro\Lib\Plugin $pro_class */
            $pro_class = '\ConnectpxBookingPro\Lib\Plugin';
            if ( class_exists( $pro_class, false ) ) {
                /** @var Base\Plugin $plugin_class */
                $plugin_class = sprintf( '\ConnectpxBooking%s\Lib\Plugin', ucfirst( $match[1] ) );

                return class_exists( $plugin_class, false );
            }

            return false;
        }

        return null;
    }

    /**
     * @return string
     */
    public static function getLocale()
    {
        $locale = get_locale();
        if ( function_exists( 'get_user_locale' ) ) {
            $locale = get_user_locale();
        }

        return $locale;
    }

    /**
     * @return string
     */
    public static function getShortLocale()
    {
        $locale = self::getLocale();
        // Cut tail for WP locales like Nederlands (Formeel) nl_NL_formal, Deutsch (Schweiz, Du) de_CH_informal and etc
        if ( $second = strpos( $locale, '_', min( 3, strlen( $locale ) ) ) ) {
            $locale = substr( $locale, 0, $second );
        }

        return $locale;
    }

}