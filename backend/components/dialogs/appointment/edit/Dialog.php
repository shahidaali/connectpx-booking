<?php
namespace ConnectpxBooking\Backend\Components\Dialogs\Appointment\Edit;

use ConnectpxBooking\Lib;
use ConnectpxBooking\Lib\Entities\Appointment;

/**
 * Class Edit
 * @package ConnectpxBooking\Backend\Components\Dialogs\Appointment\Edit
 */
class Dialog extends Lib\Base\Component
{
    /**
     * Render create/edit appointment dialog.
     * @param bool $show_wp_users
     */
    public static function render( $show_wp_users = true )
    {
        wp_enqueue_script('connectpx_booking_appointments_edit');

        $statuses = array();
        foreach ( Appointment::getStatuses() as $status ) {
            $statuses[] = array(
                'id' => $status,
                'title' => Appointment::statusToString( $status ),
                'icon' => Appointment::statusToIcon( $status )
            );
        }

        wp_localize_script( 'connectpx_booking_appointments_edit', 'ConnectpxBookingL10nAppDialog', array(
            'datePicker' => Lib\Utils\DateTime::datePickerOptions(),
            'statuses' => $statuses,
            'freeStatuses' => array(
                Appointment::STATUS_CANCELLED,
                Appointment::STATUS_REJECTED,
            ),
            'addons' => array(),
            'send_notifications' => (int) get_user_meta( get_current_user_id(), 'connectpx_booking_appointment_form_send_notifications', true ),
            'appropriate_slots' => get_option( 'connectpx_booking_appointments_displayed_time_slots', 'all' ) === 'appropriate',
            'service_main' => get_option( 'connectpx_booking_appointments_main_value', 'all' ) === 'service',
            'l10n' => array(
                'edit_appointment' => __( 'Edit appointment', 'connectpx_booking' ),
                'new_appointment' => __( 'New appointment', 'connectpx_booking' ),
                'send_notifications' => __( 'Send notifications', 'connectpx_booking' ),
                'provider' => __( 'Provider', 'connectpx_booking' ),
                'service'=> __( 'Service', 'connectpx_booking' ),
                'select_a_service' => __( '-- Select a service --', 'connectpx_booking' ),
                'location' => __( 'Location', 'connectpx_booking' ),
                'staff_any' => get_option( 'connectpx_booking_l10n_option_employee' ),
                'date' => __( 'Date', 'connectpx_booking' ),
                'period' => __( 'Period', 'connectpx_booking' ),
                'to' => __( 'to', 'connectpx_booking' ),
                'customers' => __( 'Customers', 'connectpx_booking' ),
                'selected_maximum' => __( 'Selected / maximum', 'connectpx_booking' ),
                'minimum_capacity' => __( 'Minimum capacity', 'connectpx_booking' ),
                'edit_booking_details' => __( 'Edit booking details', 'connectpx_booking' ),
                'status' => __( 'Status', 'connectpx_booking' ),
                'payment' => __( 'Payment', 'connectpx_booking' ),
                'remove_customer' => __( 'Remove customer', 'connectpx_booking' ),
                'search_customers' => __( '-- Search customers --', 'connectpx_booking' ),
                'new_customer' => __( 'New customer', 'connectpx_booking' ),
                'no_result_found' => __( 'No result found', 'connectpx_booking' ),
                'searching' => __( 'Searching', 'connectpx_booking' ),
                'save' => __( 'Save', 'connectpx_booking' ),
                'cancel' => __( 'Cancel', 'connectpx_booking' ),
                'internal_note' => __( 'Internal note', 'connectpx_booking' ),
                'chose_queue_type_info' =>  __( 'If you have added a new customer to this appointment or changed the appointment status for an existing customer, and for these records you want the corresponding email or SMS notifications to be sent to their recipients, select the "Send if new or status changed" option before clicking Send. You can also send notifications as if all customers were added as new by selecting "Send as for new".', 'connectpx_booking' ),
                'send_if_new_or_status_changed' => __( 'Send if new or status changed', 'connectpx_booking' ),
                'send_as_for_new' => __( 'Send as for new', 'connectpx_booking' ),
                'send' => __( 'Send', 'connectpx_booking' ),
                'notices' => array(
                    'service_required' => __( 'Please select a service', 'connectpx_booking' ),
                    'provider_required' => __( 'Please select a provider', 'connectpx_booking' ),
                    'date_interval_not_available' => __( 'The selected period is occupied by another appointment', 'connectpx_booking' ),
                    'date_interval_warning' => __( 'Selected period doesn\'t match service duration', 'connectpx_booking' ),
                    'interval_not_in_staff_schedule' => __( 'Selected period doesn\'t match provider\'s schedule', 'connectpx_booking' ),
                    'no_timeslots_available' => __( 'No timeslots available', 'connectpx_booking' ),
                ),
            ),
        ) );

        self::renderTemplate( 'backend/components/dialogs/appointment/edit/templates/edit', compact( 'show_wp_users', 'statuses' ) );
    }
}