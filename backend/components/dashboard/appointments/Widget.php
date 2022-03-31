<?php
namespace ConnectpxBooking\Backend\Components\Dashboard\Appointments;

use ConnectpxBooking\Lib;

/**
 * Class Widget
 * @package ConnectpxBooking\Backend\Components\Dashboard\Appointments
 */
class Widget extends Lib\Base\Component
{
    public static function init()
    {
        /** @var \WP_User $current_user */
        global $current_user;

        if ( $current_user && current_user_can( 'manage_options' ) ) {
            $class = __CLASS__;
            add_action( 'wp_dashboard_setup', function () use ( $class ) {
                wp_add_dashboard_widget( strtolower( str_replace( '\\', '-', $class ) ), 'ConnectpxBooking - ' . __( 'Appointments', 'connectpx_booking' ), array( $class, 'renderWidget' ) );
            } );
        }
    }

    /**
     * Render widget on WordPress dashboard.
     */
    public static function renderWidget()
    {
        self::enqueueAssets();
        self::renderTemplate( 'backend/components/dashboard/appointments/templates/widget' );
    }

    /**
     * Render on ConnectpxBooking/Dashboard page.
     */
    public static function renderChart()
    {
        self::enqueueAssets();
        self::renderTemplate( 'backend/components/dashboard/appointments/templates/block' );
    }

    /**
     * Enqueue assets
     */
    private static function enqueueAssets()
    {
        $currencies = Lib\Utils\Price::getCurrencies();

        wp_enqueue_script( 'connectpx_booking_appointments_dashboard' );
        wp_enqueue_style( 'connectpx_booking_appointments_dashboard' );
        
        wp_localize_script( 'connectpx_booking_appointments_dashboard', 'ConnectpxBookingAppointmentsWidgetL10n', array(
            'csrfToken'    => Lib\Utils\Common::getCsrfToken(),
            'appointments' => __( 'Appointments', 'connectpx_booking' ),
            'revenue'      => __( 'Revenue', 'connectpx_booking' ),
            'currency'     => $currencies[ Lib\Utils\Common::getOption('pmt_currency', 'USD') ]['symbol'],
        ) );
    }
}