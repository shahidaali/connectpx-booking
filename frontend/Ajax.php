<?php
namespace ConnectpxBooking\Frontend;

use ConnectpxBooking\Lib;
/**
 * Class Ajax
 * @package ConnectpxBooking\Frontend\Modules\Booking
 */
class Ajax extends Lib\Base\Ajax
{
    /**
     * @inheritDoc
     */
    protected static function permissions()
    {
        return array( '_default' => 'anonymous' );
    }

    /**
     * 1. Step date.
     *
     * response JSON
     */
    public static function renderService()
    {
        $userData = new Lib\UserBookingData();
        $userData->load();

        self::_handleTimeZone( $userData );
        $services = Lib\Utils\Common::getSubServices();

        $userData->setServiceId( self::parameter('service_id') );
        $userData = $userData->setActiveStep( 'service' );

        $response = array(
            'success' => true,
            'csrf_token' => Lib\Utils\Common::getCsrfToken(),
            'html' => self::renderTemplate( 'frontend/templates/steps/service', array(
                'progress_bar' => self::_renderProgressBar($userData),
                // 'buttons' => self::_renderButtons($userData),
                'userData' => $userData,
                'services' => $services,
            ), false ),
        );

        $userData->sessionSave();

        // Output JSON response.
        wp_send_json( $response );
    }

    /**
     * 1. Step date.
     *
     * response JSON
     */
    public static function renderDate()
    {
        $userData = new Lib\UserBookingData();
        $loaded = $userData->load();

        if ( $loaded ) {
            self::_handleTimeZone( $userData );

            // Available days and times.
            $days_times = Lib\Config::getDaysAndTimes();
            $bounding = Lib\Config::getBoundingDaysForPickadate();

            $userData = $userData->setActiveStep( 'time' );

            $response = array(
                'success' => true,
                'csrf_token' => Lib\Utils\Common::getCsrfToken(),
                'html' => self::renderTemplate( 'frontend/templates/steps/date', array(
                    'progress_bar' => self::_renderProgressBar($userData),
                    'buttons' => self::_renderButtons($userData),
                    'userData' => $userData,
                    'days' => $days_times['days'],
                    'times' => $days_times['times'],
                ), false ),
                'date_max' => $bounding['date_max'],
                'date_min' => $bounding['date_min'],
                'disabled_days' => [],
            );
        } else {
            $response = array( 'success' => false, 'error' => __('Session Error', 'connectpx_booking') );
        }

        $userData->sessionSave();

        // Output JSON response.
        wp_send_json( $response );
    }

    /**
     * 3. Step time.
     *
     * response JSON
     */
    public static function renderTime()
    {
        $userData = new Lib\UserBookingData();
        $loaded = $userData->load();

        if ( $loaded ) {
            self::_handleTimeZone( $userData );

            // Render slots by groups (day or month).
            $slots = $userData->getSlots();
            $slots_data = [];
            // $selected_date = isset ( $slots[0][2] ) ? $slots[0][2] : null;
            $selected_date = $userData->getDateFrom();
            $bounding = Lib\Config::getBoundingTimeForPickatime( $selected_date );

            // Set response.
            $response = array(
                'success' => true,
                'csrf_token' => Lib\Utils\Common::getCsrfToken(),
                'has_slots' => ! empty ( $slots_data ),
                'slots_data' => $slots_data,
                'selected_date' => $selected_date,
                'html' => self::renderTemplate( 'frontend/templates/steps/time', array(
                    'date' => $selected_date,
                    'has_slots' => ! empty ( $slots_data ),
                    'userData' => $userData,
                ), false ),
                'date_max' => $bounding['date_max'],
                'date_min' => $bounding['date_min'],
                'disabled_days' => [],
            );
        } else {
            $response = array( 'success' => false, 'error' => __('Session Error', 'connectpx_booking') );
        }
        $userData->sessionSave();

        // Output JSON response.
        wp_send_json( $response );
    }

    /**
     * 4. Step repeat.
     *
     * response JSON
     */
    public static function renderRepeat()
    {
        $userData = new Lib\UserBookingData();

        if ( $userData->load() ) {
            // Available days and times.
            $bounding  = Lib\Config::getBoundingDaysForPickadate();
            $slots    = $userData->getSlots();
            $datetime = date_create( $slots[0][2] );
            $date_min = array(
                (int) $datetime->format( 'Y' ),
                (int) $datetime->format( 'n' ) - 1,
                (int) $datetime->format( 'j' ),
            );

            $schedule = array();
            $repeat_data = $userData->getRepeatData();
            if ( $repeat_data ) {
                $until = Lib\Slots\DatePoint::fromStrInClientTz( $repeat_data['until'] );
                foreach ( $slots as $slot ) {
                    $date = Lib\Slots\DatePoint::fromStr( $slot[2] );
                    if ( $until->lt( $date ) ) {
                        $until = $date->toClientTz();
                    }
                }

                $schedule = Proxy\RecurringAppointments::buildSchedule(
                    clone $userData,
                    $slots[0][2],
                    $until->format( 'Y-m-d' ),
                    $repeat_data['repeat'],
                    $repeat_data['params'],
                    array_map( function ( $slot ) { return $slot[2]; }, $slots )
                );
            }

            $response = Proxy\Shared::stepOptions( array(
                'success'  => true,
                'html' => Proxy\RecurringAppointments::getStepHtml( $userData, $show_cart_btn, $info_text, $progress_tracker ),
                'date_max' => $bounding['date_max'],
                'date_min' => $date_min,
                'repeated' => (int) $userData->getRepeated(),
                'repeat_data' => $userData->getRepeatData(),
                'schedule' => $schedule,
                'short_date_format' => Lib\Utils\DateTime::convertFormat( 'D, M d', Lib\Utils\DateTime::FORMAT_PICKADATE ),
                'pages_warning_info' => nl2br( Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_repeat_schedule_help' ) ),
                'could_be_repeated' => Proxy\RecurringAppointments::canBeRepeated( true, $userData ),
            ), 'repeat' );
        } else {
            $response = array( 'success' => false, 'error' => Errors::SESSION_ERROR );
        }
        $userData->sessionSave();

        // Output JSON response.
        wp_send_json( $response );
    }

    /**
     * Save booking data in session.
     */
    public static function sessionSave()
    {
        $errors  = array();
        $userData = new Lib\UserBookingData();
        $userData->load();
        $parameters = self::parameters();
        $errors = $userData->validate( $parameters );
        if ( empty ( $errors ) ) {
            if ( self::hasParameter( 'slots' ) ) {
                // Decode slots.
                $parameters['slots'] = json_decode( $parameters['slots'], true );
            }

            $userData->fillData( $parameters );
        }
        $userData->sessionSave();
        $errors['success'] = empty( $errors );

        wp_send_json( $errors );
    }

    /**
     * Get steps bar
     *
     * @return array
     * @throws
     */
    public static function _renderButtons($userData)
    {
        return self::renderTemplate( 'frontend/templates/steps/buttons', array(
            'steps' => Lib\Utils\Common::getSteps(),
            'userData' => $userData,
        ), false );
    }

    /**
     * Get steps bar
     *
     * @return array
     * @throws
     */
    public static function _renderProgressBar($userData)
    {
        return self::renderTemplate( 'frontend/templates/steps/progress-bar', array(
            'steps' => Lib\Utils\Common::getSteps(),
            'userData' => $userData,
        ), false );
    }

    /**
     * Get disabled days in Pickadate format.
     *
     * @return array
     * @throws
     */
    private static function _getDisabledDaysForPickadate( $userData )
    {
        $one_day = new \DateInterval( 'P1D' );
        $result = array();
        $date = new \DateTime( $this->userData->getDateFrom() );
        $date->modify( 'first day of this month' );
        $end_date = clone $date;
        $end_date->modify( 'first day of next month' );
        $Y = (int) $date->format( 'Y' );
        $n = (int) $date->format( 'n' ) - 1;
        while ( $date < $end_date ) {
            if ( ! array_key_exists( $date->format( 'Y-m-d' ), $this->slots ) ) {
                $result[] = array( $Y, $n, (int) $date->format( 'j' ) );
            }
            $date->add( $one_day );
        }

        return $result;
    }

    /**
     * Handle time zone parameters.
     *
     * @param Lib\UserBookingData $userData
     */
    private static function _handleTimeZone( Lib\UserBookingData $userData )
    {
        $time_zone        = null;
        $time_zone_offset = null;  // in minutes

        if ( self::hasParameter( 'time_zone_offset' ) ) {
            // Browser values.
            $time_zone        = self::parameter( 'time_zone' );
            $time_zone_offset = self::parameter( 'time_zone_offset' );
        } else if ( self::hasParameter( 'time_zone' ) ) {
            // WordPress value.
            $time_zone = self::parameter( 'time_zone' );
            if ( preg_match( '/^UTC[+-]/', $time_zone ) ) {
                $offset           = preg_replace( '/UTC\+?/', '', $time_zone );
                $time_zone        = null;
                $time_zone_offset = - $offset * 60;
            } else {
                $time_zone_offset = - timezone_offset_get( timezone_open( $time_zone ), new \DateTime() ) / 60;
            }
        }

        if ( $time_zone !== null || $time_zone_offset !== null ) {
            // Client time zone.
            $userData
                ->setTimeZone( $time_zone )
                ->setTimeZoneOffset( $time_zone_offset )
                ->applyTimeZone();
        }
    }

    /**
     * Override parent method to exclude actions from CSRF token verification.
     *
     * @param string $action
     * @return bool
     */
    protected static function csrfTokenValid( $action = null )
    {
        $excluded_actions = array(
            'approveAppointment',
            'cancelAppointment',
            'rejectAppointment',
            'renderDate',
            'renderExtras',
            'renderTime',
        );

        return in_array( $action, $excluded_actions ) || parent::csrfTokenValid( $action );
    }
}