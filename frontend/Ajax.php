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
        // if( !self::parameter('reset_form') ) {
        //     $userData->load();
        // }

        self::_handleTimeZone( $userData );
        
        $userData->setServiceId( self::parameter('service_id') );
        $userData = $userData->setActiveStep( 'service' );

        $service = new Lib\Entities\Service();
        $service_available = false;
        if( $service->load( self::parameter('service_id') ) && $service->isEnabled() ) {
            $service_available = true;
        }

        $sub_services = $userData->getCustomerSubServices();

        $response = array(
            'success' => true,
            'csrf_token' => Lib\Utils\Common::getCsrfToken(),
            'html' => self::renderTemplate( 'frontend/templates/steps/service', array(
                'progress_bar' => self::_renderProgressBar($userData),
                // 'buttons' => self::_renderButtons($userData),
                'userData' => $userData,
                'sub_services' => $sub_services,
                'service_available' => $service_available,
                'service_not_available_message' => Lib\Utils\Common::getOption('service_not_available_message'),
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

            $userData = $userData->setActiveStep( 'date' );

            $selected_date = null;
            if( $userData->getDateFrom() ) {
                $datetime = date_create( $userData->getDateFrom() );
                $selected_date = array(
                    (int) $datetime->format( 'Y' ),
                    (int) $datetime->format( 'n' ) - 1,
                    (int) $datetime->format( 'j' ),
                );
            }

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
                'selected_date' => $selected_date,
                'disabled_days' => [],
                'active_step' => $userData->getActiveStep(),
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

            $userData = $userData->setActiveStep( 'date' );

            // Render slots by groups (day or month).
            $slots = $userData->getSlots();
            $slots_data = [];
            // $selected_date = isset ( $slots[0][0] ) ? $slots[0][0] : null;
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
                    'return_pickup_checkbox_text' => __('Not sure', 'connectpx_booking'),
                ), false ),
                'date_max' => $bounding['date_max'],
                'date_min' => $bounding['date_min'],
                'disabled_days' => [],
                'time_error' => __('Please select pickup time.', 'connectpx_booking'),
                'is_round_trip' => $userData->getSubService()->isRoundTrip(),
                'slot_length' => (int) Lib\Utils\Common::getOption('slot_length', 15),
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
            $userData = $userData->setActiveStep( 'repeat' );

            // Available days and times.
            $bounding  = Lib\Config::getBoundingDaysForPickadate();
            $slots    = $userData->getSlots();
            $datetime = date_create( $userData->getDateFrom() );
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
                    $date = Lib\Slots\DatePoint::fromStr( $slot[0] );
                    if ( $until->lt( $date ) ) {
                        $until = $date->toClientTz();
                    }
                }

                $scheduler = new Lib\Scheduler( 
                    clone $userData, 
                    $slots[0][0], 
                    $slots[0][1], 
                    $slots[0][2], 
                    $until->format( 'Y-m-d' ), 
                    $repeat_data['repeat'], 
                    $repeat_data['params']
                );

                $schedule = $scheduler->build( $slots );
            }

            $response = array(
                'success'  => true,
                'html' => self::renderTemplate( 'frontend/templates/steps/repeat', array(
                    'progress_bar' => self::_renderProgressBar($userData),
                    'buttons' => self::_renderButtons($userData),
                    'userData' => $userData,
                ), false ),
                'date_max' => $bounding['date_max'],
                'date_min' => $date_min,
                'repeated' => $repeat_data ? (int) $userData->getRepeated() : 0,
                'repeat_data' => $repeat_data,
                'schedule' => $schedule,
                'short_date_format' => Lib\Utils\DateTime::convertFormat( 'D, M d', Lib\Utils\DateTime::FORMAT_PICKADATE ),
                'pages_warning_info' => '',
                'could_be_repeated' => true,
            );
        } else {
            $response = array( 'success' => false, 'error' => __('Session Error', 'connectpx_booking') );
        }
        $userData->sessionSave();

        // Output JSON response.
        wp_send_json( $response );
    }

    /**
     * 6. Step details.
     *
     * @throws
     */
    public static function renderDetails()
    {
        $userData = new Lib\UserBookingData();
        $loaded = $userData->load();

        if ( $loaded ) {
            $userData = $userData->setActiveStep( 'details' );

            if ( self::hasParameter( 'add_to_cart' ) ) {
                $userData->addSlotsToCart();
            }

            // Render main template.
            $terms_page = "#";
            if( Lib\Utils\Common::getOption('terms_page_id', 0) ) {
                $terms_page = get_permalink( Lib\Utils\Common::getOption('terms_page_id', 0) );
            }
            $customer = $userData->getCustomer();

            $html = self::renderTemplate( 'frontend/templates/steps/details', array(
                'progress_bar' => self::_renderProgressBar($userData),
                'buttons' => self::_renderButtons($userData),
                'userData' => $userData,
                'terms_page' => $terms_page,
                'show_address' => false,
            ), false );

            $map_default_lat_lngs = [
                'lat' => (float) Lib\Utils\Common::getOption('google_map_lat', 42.3144255),
                'lng' => (float) Lib\Utils\Common::getOption('google_map_lng', -83.518173),
            ];
            $response = array(
                'success' => true,
                'html' => $html,
                'is_round_trip' => $userData->getSubService()->isRoundTrip(),
                'woocommerce' => array(
                    'enabled' => 1,
                    'cart_url' => wc_get_cart_url(),
                ),
                'terms_error' => __('Please accept terms and conditions to proceed.', 'connectpx_booking'),
                'customer_default_lat_lngs' => $customer->getDefaultLatLngs(),
                // 'customer_default_lat_lngs' => [
                //     'pickup' => $map_default_lat_lngs,
                //     'destination' => $map_default_lat_lngs,
                // ],
                'map_default_lat_lngs' => $map_default_lat_lngs,
            );
        } else {
            $response = array( 'success' => false, 'error' => __('Session Error', 'connectpx_booking') );
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
     * Get Schedule for step Repeat on frontend.
     */
    public static function getCustomerSchedule()
    {
        $userData = new Lib\UserBookingData();
        if ( $userData->load() ) {
            $until    = self::parameter( 'until' );
            $repeat   = self::parameter( 'repeat' );
            $params   = self::parameter( 'params', array() );
            $slots    = $userData->getSlots();
            $datetime = $slots[0][0];

            $userData->setRepeatData( compact( 'repeat','until','params' ) );

            $scheduler = new Lib\Scheduler( 
                clone $userData, 
                $slots[0][0], 
                $slots[0][1], 
                $slots[0][2], 
                $until, 
                $repeat, 
                $params
            );

            $schedule = $scheduler->build( $slots );

            $userData->sessionSave();
            wp_send_json_success( $schedule );
        } else {
            wp_send_json_error();
        }
    }

    /**
     * Add product to cart
     *
     * return string JSON
     */
    public static function addToWoocommerceCart()
    {
        $userData = new Lib\UserBookingData();

        if ( $userData->load() ) {
            $session = WC()->session;
            /** @var \WC_Session_Handler $session */
            if ( $session instanceof \WC_Session_Handler && $session->get_session_cookie() === false ) {
                $session->set_customer_session_cookie( true );
            }

            $pickupDetail = $userData->getPickupDetail();
            $connectpx_booking = $userData->getData();
            $connectpx_booking['items']       = $userData->cart->getItemsData();
            $connectpx_booking['wc_checkout'] = array(
                'billing_first_name' => $userData->getFirstName(),
                'billing_last_name'  => $userData->getLastName(),
                'billing_email'      => $userData->getEmail(),
                'billing_phone'      => $userData->getPhone(),
                // Billing country on WC checkout is select (select2)
                'billing_country'    => 'US',
                'billing_state'      => 'MI',
                'billing_city'       => 'Southfield',
                'billing_address_1'  => '2129 Daylene Drive',
                'billing_address_2'  => '',
                'billing_postcode'   => '48075',
            );
            $product_id = Lib\Utils\Common::getOption('wc_product_id', 0);

            // Qnt 1 product in $userData exists value with number_of_persons
            WC()->cart->add_to_cart( $product_id, 1, '', array(), compact( 'connectpx_booking' ) );

            $response = array( 'success' => true );
        } else {
            $response = array( 'success' => false, 'error' => __('Session Error', 'connectpx_booking') );
        }
        wp_send_json( $response );
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