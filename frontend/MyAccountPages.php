<?php
namespace ConnectpxBooking\Frontend;

use ConnectpxBooking\Lib;

/**
 * Class Controller
 * @package ConnectpxBooking\Frontend\Modules\WooCommerce
 */
class MyAccountPages extends Lib\Base\Ajax
{
    /**
     * @inheritDoc
     */
    public static function init()
    {
        /** @var self $self */
        $self = get_called_class();

        add_filter( 'woocommerce_locate_template', array( $self, 'overrideWcTemplates' ), 1, 3 );
        add_filter( 'woocommerce_account_menu_items', array( $self, 'myAccountLinks' ), 12, 1 );
        add_filter( 'woocommerce_get_endpoint_url', array( $self, 'myAccountEndpointUrl' ), 12, 4 );
        add_action( 'init', array( $self, 'myAccountEndpointsRewrite' ) );
        add_action( 'wp', array( $self, 'downloadInvoice' ) );
        add_action( 'woocommerce_account_bookings_endpoint', array( $self, 'myAccountBookingContent' ) );
        add_action( 'woocommerce_account_invoices_endpoint', array( $self, 'myAccountInvoicesContent' ) );
        add_action( 'woocommerce_account_customer-account_endpoint', array( $self, 'myAccountCustomerAccountContent' ) );

        add_action( 'wp_enqueue_scripts', array($self, 'enqueueScripts'), 12 );
        parent::init();
    }

    public static function overrideWcTemplates( $template, $template_name, $template_path ) {
        global $woocommerce;
        $_template = $template;

        if ( ! $template_path ) 
            $template_path = $woocommerce->template_url;

        $plugin_path  = untrailingslashit( plugin_dir_path( __FILE__ ) )  . '/templates/woocommerce/';

        if( file_exists( $plugin_path . $template_name ) )
            $template = $plugin_path . $template_name;

        // Look within passed path within the theme - this is priority
        if( ! $template ) {
            $template = locate_template(
                array(
                    $template_path . $template_name,
                    $template_name
                )
            );
        }

        if ( ! $template ) 
            $template = $_template;

        return $template;
    }

    public static function myAccountLinks( $menu_links ) {
        // we will hook "anyuniquetext123" later
        $new = array( 
            'bookings' => __('Bookings', 'connectpx_booking'), 
            'invoices' => __('Invoices', 'connectpx_booking'), 
            'customer-account' => __('Customer Account', 'connectpx_booking') 
        );

        // or in case you need 2 links
        // $new = array( 'link1' => 'Link 1', 'link2' => 'Link 2' );

        // array_slice() is good when you want to add an element between the other ones
        $menu_links = array_slice( $menu_links, 0, 1, true ) 
        + $new 
        + array_slice( $menu_links, 1, NULL, true );


        return $menu_links;
    }

    public static function myAccountEndpointsRewrite() {
        add_rewrite_endpoint( 'bookings', EP_PAGES );
        add_rewrite_endpoint( 'invoices', EP_PAGES );
        add_rewrite_endpoint( 'customer-account', EP_PAGES );
    }

    public static function myAccountEndpointUrl( $url, $endpoint, $value, $permalink ) {
        return $url;
    }

    public static function myAccountBookingContent() {

        // Disable caching.
        Lib\Utils\Common::noCache();

        $customer = new Lib\Entities\Customer();
        if ( is_user_logged_in() && $customer->loadBy( array( 'wp_user_id' => get_current_user_id() ) ) ) {
            $titles = array(
                'id' => __('No.', 'connectpx_booking'),
                'patient' => __('Patient Name', 'connectpx_booking'),
                'destination' => __('Destination Address', 'connectpx_booking'),
                'date' => __( 'Date', 'connectpx_booking' ),
                'time' => __( 'Time', 'connectpx_booking' ),
                'total_amount' => __( 'Detail', 'connectpx_booking' ),
                'cancel' => __( 'Cancel', 'connectpx_booking' ),
                'reschedule' => __( 'Reschedule', 'connectpx_booking' ),
                'status' => __( 'Status', 'connectpx_booking' ),
            );

            $customer_address = array(
                'country' => $customer->getCountry(),
                'state' => $customer->getState(),
                'postcode' => $customer->getPostcode(),
                'city' => $customer->getCity(),
                'street' => $customer->getStreet(),
                'street_number' => $customer->getStreetNumber(),
                'additional_address' => $customer->getAdditionalAddress(),
            );

            $appointment_columns = [
                'filters',
                'id',
                'date',
                'timezone',
                'patient',
                'destination',
                'total_amount',
                'status',
                'cancel',
                'reason'
            ];
            $filters = in_array( 'filters', $appointment_columns );
            $show_reason = in_array( 'reason', $appointment_columns );
            foreach ( $appointment_columns as $pos => $column ) {
                if ( ! array_key_exists( $column, $titles ) ) {
                    unset( $appointment_columns[ $pos ] );
                }
            }
            $services = Lib\Entities\Service::query( 's' )
                ->select( 's.id, s.title' )
                ->fetchArray();

            self::renderTemplate( 'frontend/templates/customer-bookings', array(
                'appointment_columns' => $appointment_columns,
                'filters' => $filters,
                'show_reason' => $show_reason,
                'customer' => $customer,
                'customer_address' => $customer_address,
                'titles' => $titles,
                'services' => $services,
            ));
        } else {
            self::renderTemplate( 'frontend/templates/permission', array());
        }
    }

    public static function downloadInvoice() {
        global $wp;

        if (is_user_logged_in() && is_account_page() && isset($wp->query_vars['invoices']) && !empty($_GET['download_invoice'])) {
            $invoice = Lib\Entities\Invoice::find($_GET['download_invoice']);
            $invoice->downloadInvoice();
            exit();
        }
    }

    public static function myAccountInvoicesContent() {

        // Disable caching.
        Lib\Utils\Common::noCache();

        $customer = new Lib\Entities\Customer();
        if ( is_user_logged_in() && $customer->loadBy( array( 'wp_user_id' => get_current_user_id() ) ) ) {
            $titles = array(
                'id' => __('No', 'connectpx_booking'),
                'start_date' => __( 'Start Date', 'connectpx_booking' ),
                'end_date' => __( 'End Date', 'connectpx_booking' ),
                'due_date' => __( 'Due Date', 'connectpx_booking' ),
                'status' => __( 'Status', 'connectpx_booking' ),
                'total_amount' => __( 'Total Amount', 'connectpx_booking' ),
                'due_amount' => __( 'Due Amount', 'connectpx_booking' ),
                'actions' => __( 'Actions', 'connectpx_booking' ),
            );

            $customer_address = array(
                'country' => $customer->getCountry(),
                'state' => $customer->getState(),
                'postcode' => $customer->getPostcode(),
                'city' => $customer->getCity(),
                'street' => $customer->getStreet(),
                'street_number' => $customer->getStreetNumber(),
                'additional_address' => $customer->getAdditionalAddress(),
            );

            $invoice_columns = [
                'id',
                'start_date',
                'end_date',
                'due_date',
                'status',
                'total_amount',
                'due_amount',
                'actions',
            ];
            self::renderTemplate( 'frontend/templates/customer-invoices', array(
                'invoice_columns' => $invoice_columns,
                'filters' => 1,
                'customer' => $customer,
                'customer_address' => $customer_address,
                'titles' => $titles,
            ));
        } else {
            self::renderTemplate( 'frontend/templates/permission', array());
        }
    }

    public static function myAccountCustomerAccountContent() {

        // Disable caching.
        Lib\Utils\Common::noCache();

        $customer = new Lib\Entities\Customer();
        if ( is_user_logged_in() && $customer->loadBy( array( 'wp_user_id' => get_current_user_id() ) ) ) {
            $customer_address = array(
                'country' => [
                    'label' => __('Country', 'connectpx_booking'),
                    'value' => $customer->getCountry(),
                ],
                'state' => [
                    'label' => __('State', 'connectpx_booking'),
                    'value' => $customer->getState(),
                ],
                'postcode' => [
                    'label' => __('Postcode', 'connectpx_booking'),
                    'value' => $customer->getPostcode(),
                ],
                'city' => [
                    'label' => __('City', 'connectpx_booking'),
                    'value' => $customer->getCity(),
                ],
                'street' => [
                    'label' => __('Street', 'connectpx_booking'),
                    'value' => $customer->getStreet(),
                ],
                'street_number' => [
                    'label' => __('Street Number', 'connectpx_booking'),
                    'value' => $customer->getStreetNumber(),
                ],
                'additional_address' => [
                    'label' => __('Additional Address', 'connectpx_booking'),
                    'value' => $customer->getAdditionalAddress(),
                ],
            );

            self::renderTemplate( 'frontend/templates/customer-account', array(
                'customer' => $customer,
                'customer_address' => $customer_address,
            ));
        } else {
            self::renderTemplate( 'frontend/templates/permission', array());
        }
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public static function enqueueScripts() {
        global $wp;

        if (is_user_logged_in() && is_account_page() && isset($wp->query_vars['bookings'])) {
            $titles = array(
                'id' => __('No.', 'connectpx_booking'),
                'patient' => __('Patient Name', 'connectpx_booking'),
                'destination' => __('Destination Address', 'connectpx_booking'),
                'date' => __( 'Date', 'connectpx_booking' ),
                'time' => __( 'Time', 'connectpx_booking' ),
                'total_amount' => __( 'Detail', 'connectpx_booking' ),
                'cancel' => __( 'Cancel', 'connectpx_booking' ),
                'reschedule' => __( 'Reschedule', 'connectpx_booking' ),
                'status' => __( 'Status', 'connectpx_booking' ),
            );
            $appointment_columns = [
                'filters',
                'id',
                'date',
                'timezone',
                'patient',
                'destination',
                'total_amount',
                'status',
                'cancel',
                'reason'
            ];
            $filters = in_array( 'filters', $appointment_columns );
            $show_reason = in_array( 'reason', $appointment_columns );
            foreach ( $appointment_columns as $pos => $column ) {
                if ( ! array_key_exists( $column, $titles ) ) {
                    unset( $appointment_columns[ $pos ] );
                }
            }

            // Prepare URL for AJAX requests.
            $ajax_url = admin_url( 'admin-ajax.php' );

            // Support WPML.
            if ( $sitepress instanceof \SitePress ) {
                $ajax_url = add_query_arg( array( 'lang' => $sitepress->get_current_language() ), $ajax_url );
            }
            
            wp_enqueue_script('connectpx_booking_customer_bookings');
            wp_enqueue_style('connectpx_booking_customer_bookings');

            wp_localize_script( 'connectpx_booking_customer_bookings', 'ConnectpxBookingCustomerBookingsL10n', array(
                'ajax_url' => $ajax_url,
                'appointment_columns' => $appointment_columns,
                'filters' => $filters,
                'zeroRecords' => __( 'No appointments.', 'connectpx_booking' ),
                'minDate' => 0,
                'maxDate' => Lib\Config::getMaximumAvailableDaysForBooking(),
                'datePicker' => Lib\Utils\DateTime::datePickerOptions(),
                'dateRange' => Lib\Utils\DateTime::dateRangeOptions( array( 'anyTime' => __( 'Any time', 'connectpx_booking' ) ) ),
                'expired_appointment' => __( 'Expired', 'connectpx_booking' ),
                'deny_cancel_appointment' => __( 'Not allowed', 'connectpx_booking' ),
                'cancel' => __( 'Cancel', 'connectpx_booking' ),
                'payment' => __( 'Detail', 'connectpx_booking' ),
                'reschedule' => __( 'Reschedule', 'connectpx_booking' ),
                'noTimeslots' => __( 'There are no time slots for selected date.', 'connectpx_booking' ),
                'profile_update_success' => __( 'Profile updated successfully.', 'connectpx_booking' ),
                'processing' => __( 'Processing...', 'connectpx_booking' ),
                'errors' => array(
                    'cancel' => __( 'Unfortunately, you\'re not able to cancel the appointment because the required time limit prior to canceling has expired.', 'connectpx_booking' ),
                    'reschedule' => __( 'The selected time is not available anymore. Please, choose another time slot.', 'connectpx_booking' ),
                ),
            ) );
        }

        if (is_user_logged_in() && is_account_page() && isset($wp->query_vars['invoices'])) {
            $titles = array(
                'id' => __('No', 'connectpx_booking'),
                'start_date' => __( 'Start Date', 'connectpx_booking' ),
                'end_date' => __( 'End Date', 'connectpx_booking' ),
                'due_date' => __( 'Due Date', 'connectpx_booking' ),
                'status' => __( 'Status', 'connectpx_booking' ),
                'total_amount' => __( 'Total Amount', 'connectpx_booking' ),
                'due_amount' => __( 'Due Amount', 'connectpx_booking' ),
                'actions' => __( 'Actions', 'connectpx_booking' ),
            );

            $invoice_columns = [
                'id',
                'start_date',
                'end_date',
                'due_date',
                'status',
                'total_amount',
                'due_amount',
                'actions',
            ];
            $filters = 1;

            // Prepare URL for AJAX requests.
            $ajax_url = admin_url( 'admin-ajax.php' );

            // Support WPML.
            if ( $sitepress instanceof \SitePress ) {
                $ajax_url = add_query_arg( array( 'lang' => $sitepress->get_current_language() ), $ajax_url );
            }
            
            wp_enqueue_script('connectpx_booking_customer_invoices');
            wp_enqueue_style('connectpx_booking_customer_invoices');

            wp_localize_script( 'connectpx_booking_customer_invoices', 'ConnectpxBookingCustomerInvoicesL10n', array(
                'ajax_url' => $ajax_url,
                'invoice_columns' => $invoice_columns,
                'filters' => $filters,
                'zeroRecords' => __( 'No invoices.', 'connectpx_booking' ),
                'datePicker' => Lib\Utils\DateTime::datePickerOptions(),
                'dateRange' => Lib\Utils\DateTime::dateRangeOptions( array( 'anyTime' => __( 'Start any time', 'connectpx_booking' ), 'dueAnyTime' => __( 'Due any time', 'connectpx_booking' ) ) ),
                'processing' => __( 'Processing...', 'connectpx_booking' ),
                'errors' => array(
                ),
            ) );
        }

        if (is_user_logged_in() && is_account_page() && isset($wp->query_vars['customer-account'])) {
            // Prepare URL for AJAX requests.
            $ajax_url = admin_url( 'admin-ajax.php' );

            // Support WPML.
            if ( $sitepress instanceof \SitePress ) {
                $ajax_url = add_query_arg( array( 'lang' => $sitepress->get_current_language() ), $ajax_url );
            }
            
            wp_enqueue_script('connectpx_booking_customer_account');
            wp_enqueue_style('connectpx_booking_customer_account');

            wp_localize_script( 'connectpx_booking_customer_account', 'ConnectpxBookingCustomerAccountL10n', array(
                'ajax_url' => $ajax_url,
                'profile_update_success' => __( 'Profile updated successfully.', 'connectpx_booking' ),
            ) );
        }
    }
}