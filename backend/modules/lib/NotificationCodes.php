<?php
namespace ConnectpxBooking\Backend\Modules\Lib;

use ConnectpxBooking\Lib;
use ConnectpxBooking\Lib\Entities\Notification;

/**
 * Class NotificationCodes
 * @package ConnectpxBooking\Backend\Modules\Lib
 */
class NotificationCodes
{
    /** @var string */
    protected $type;

    /** @var array */
    protected $codes;

    /**
     * Constructor.
     *
     * @param string $type
     */
    public function __construct( $type = 'email' )
    {
        $this->type = $type;
        $this->codes = array(
            'appointment' => array(
                'appointment_pickup_date' => array( 'description' => __( 'Pickup date of appointment', 'connectpx_booking' ), 'if' => true ),
                'appointment_pickup_time' => array( 'description' => __( 'Pickup time of appointment', 'connectpx_booking' ), 'if' => true ),
                'appointment_return_pickup_date' => array( 'description' => __( 'Return pickup date of appointment', 'connectpx_booking' ), 'if' => true ),
                'appointment_return_pickup_time' => array( 'description' => __( 'Return pickup time of appointment', 'connectpx_booking' ), 'if' => true ),
                'appointment_notes' => array( 'description' => __( 'Customer notes for appointment', 'connectpx_booking' ), 'if' => true ),
                'booking_number' => array( 'description' => __( 'Booking number', 'connectpx_booking' ) ),
                'patient_name' => array( 'description' => __( 'Name of patient', 'connectpx_booking' ) ),
                'admin_notes' => array( 'description' => __( 'Admin notes', 'connectpx_booking' ) ),
                'status' => array( 'description' => __( 'Appointment status', 'connectpx_booking' ) ),
                'cancellation_time_limit' => array( 'description' => __( 'Time limit to which appointments can be cancelled ', 'connectpx_booking' ) ),
                'sub_service_name' => array( 'description' => __( 'Name of sub service', 'connectpx_booking' ) ),
                'trip_type' => array( 'description' => __( 'Type of trip', 'connectpx_booking' ) ),
                'flat_rate' => array( 'description' => __( 'Flat rate', 'connectpx_booking' ) ),
                'mileage' => array( 'description' => __( 'Milage', 'connectpx_booking' ) ),
                'mileage_fee' => array( 'description' => __( 'Per mile fee', 'connectpx_booking' ) ),
                'total_mileage_fee' => array( 'description' => __( 'Total milage fee', 'connectpx_booking' ) ),
                'waiting_fee' => array( 'description' => __( 'Waiting fee', 'connectpx_booking' ) ),
                'after_hours_fee' => array( 'description' => __( 'After hours fee', 'connectpx_booking' ) ),
                'no_show_fee' => array( 'description' => __( 'No show fee', 'connectpx_booking' )),
                'extras_fee' => array( 'description' => __( 'Manual adjustments', 'connectpx_booking' )),
            ),
            'cart' => array(
                'appointments_table' => array( 'description' => __( 'Detail of booked appointments', 'connectpx_booking' )),
            ),
            'schedule' => array(
                'schedule_no' => array( 'description' => __( 'Schedule id.' )),
                'schedule_start_date' => array( 'description' => __( 'Schedule start date.' )),
                'schedule_end_date' => array( 'description' => __( 'Schedule end date.' )),
                'schedule_status' => array( 'description' => __( 'Schedule end date.' )),
                'schedule_repeat_info' => array( 'description' => __( 'Schedule repeat info.' )),
                'current_date_time' => array( 'description' => __( 'Current date and time.' )),
                'cancellation_reason' => array( 'description' => __( 'Cancellation reason.' )),
            ),
            'company' => array(
                'company_address' => array( 'description' => __( 'Address of company', 'connectpx_booking' ), 'if' => true ),
                'company_name' => array( 'description' => __( 'Name of company', 'connectpx_booking' ), 'if' => true ),
                'company_phone' => array( 'description' => __( 'Company phone', 'connectpx_booking' ), 'if' => true ),
                'company_website' => array( 'description' => __( 'Company web-site address', 'connectpx_booking' ), 'if' => true ),
            ),
            'customer' => array(
                'client_address' => array( 'description' => __( 'Address of client', 'connectpx_booking' ), 'if' => true ),
                'client_email' => array( 'description' => __( 'Email of client', 'connectpx_booking' ), 'if' => true ),
                'client_first_name' => array( 'description' => __( 'First name of client', 'connectpx_booking' ), 'if' => true ),
                'client_last_name' => array( 'description' => __( 'Last name of client', 'connectpx_booking' ), 'if' => true ),
                'client_name' => array( 'description' => __( 'Full name of client', 'connectpx_booking' ), 'if' => true ),
                'client_note' => array( 'description' => __( 'Note of client', 'connectpx_booking' ), 'if' => true ),
                'client_phone' => array( 'description' => __( 'Phone of client', 'connectpx_booking' ), 'if' => true ),
            ),
            'customer_timezone' => array(
                'client_timezone' => array( 'description' => __( 'Time zone of client', 'connectpx_booking' ), 'if' => true ),
            ),
            'payment' => array(
                'payment_type' => array( 'description' => __( 'Payment type', 'connectpx_booking' ) ),
                'payment_status' => array( 'description' => __( 'Payment status', 'connectpx_booking' ) ),
                'payment_status' => array( 'description' => __( 'Appointment payment status', 'connectpx_booking' )),
                'payment_type' => array( 'description' => __( 'Payment method', 'connectpx_booking' )),
                'amount_total' => array( 'description' => __( 'Total amount', 'connectpx_booking' )),
                'amount_paid' => array( 'description' => __( 'Paid amount', 'connectpx_booking' )),
                'amount_due' => array( 'description' => __( 'Due amount', 'connectpx_booking' )),
            ),
            'invoice' => array(
                'invoice_number' => array( 'description' => __( 'Invoice no', 'connectpx_booking' ) ),
                'start_date' => array( 'description' => __( 'Invoice start date', 'connectpx_booking' ) ),
                'end_date' => array( 'description' => __( 'Invoice end date', 'connectpx_booking' ) ),
                'due_date' => array( 'description' => __( 'Invoice due date', 'connectpx_booking' ) ),
                'total_amount' => array( 'description' => __( 'Total amount', 'connectpx_booking' )),
                'paid_amount' => array( 'description' => __( 'Paid amount', 'connectpx_booking' )),
                'invoice_status' => array( 'description' => __( 'Invoice status', 'connectpx_booking' )),
            ),
            'service' => array(
                'service_description' => array( 'description' => __( 'Info of service', 'connectpx_booking' ), 'if' => true ),
                'service_name' => array( 'description' => __( 'Name of service', 'connectpx_booking' ) ),
            ),
            'user_credentials' => array(
                'new_password' => array( 'description' => __( 'Customer new password', 'connectpx_booking' ) ),
                'new_username' => array( 'description' => __( 'Customer new username', 'connectpx_booking' ) ),
                'site_address' => array( 'description' => __( 'Site address', 'connectpx_booking' ) ),
            ),
        );
        $this->codes['appointments_list'] = array(
            'appointments' => array(
                'description' => array(
                    __( 'Loop over appointments list', 'connectpx_booking' ),
                    __( 'Loop over appointments list with delimiter', 'connectpx_booking' ),
                ),
                'loop' => array(
                    'item' => 'appointment',
                    'codes' => array_merge(
                        $this->codes['appointment'],
                        $this->codes['service'],
                    ),
                ),
            ),
        );

        if ( $type == 'email' ) {
            // Only email.
            $this->codes['company']['company_logo'] = array( 'description' => __( 'Company logo', 'connectpx_booking' ), 'if' => true );
            $this->codes['appointment']['cancel_appointment'] = array( 'description' => __( 'Cancel appointment link', 'connectpx_booking' ) );
        }
    }

    /**
     * Render codes for given notification type.
     *
     * @param string $notification_type
     */
    public function render( $notification_type )
    {
        $codes = $this->_build( $notification_type );
        ksort( $codes );

        $tbody = '';
        foreach ( $codes as $key => $code ) {
            if ( ! isset( $code['loop'] ) ) {
                $tbody .= sprintf(
                    '<tr><td class="p-0"><input value="{%s}" class="border-0 connectpx_booking-outline-0" readonly="readonly" onclick="this.select()" /> &ndash; %s</td></tr>',
                    $key,
                    esc_html( $code['description'] )
                );
            }
        }

        printf(
            '<table class="connectpx_booking-js-codes connectpx_booking-js-codes-%s"><tbody>%s</tbody></table>',
            $notification_type,
            $tbody
        );
    }

    /**
     * Get a list of codes.
     *
     * @param string $notification_type
     * @return array
     */
    public function getCodes( $notification_type )
    {
        $codes = $this->_build( $notification_type );
        ksort( $codes );

        return $codes;
    }

    /**
     * Build array of codes for given notification type.
     *
     * @param $notification_type
     * @return array
     */
    private function _build( $notification_type )
    {
        $codes = array();

        switch ( $notification_type ) {
            case Notification::TYPE_APPOINTMENT_REMINDER:
            case Notification::TYPE_APPOINTMENT_STATUS_CHANGED:
                $codes = array_merge(
                    $this->codes['appointment'],
                    $this->codes['company'],
                    $this->codes['customer'],
                    $this->codes['customer_timezone'],
                    $this->codes['payment'],
                    $this->codes['service'],
                );
                break;
            case Notification::TYPE_SCHEDULE_STATUS_CHANGED:
                $codes = array_merge(
                    $this->codes['appointment'],
                    $this->codes['schedule'],
                    $this->codes['company'],
                    $this->codes['customer'],
                    $this->codes['customer_timezone'],
                    $this->codes['service'],
                );
                break;
            case Notification::TYPE_NEW_BOOKING:
                $codes = array_merge(
                    $this->codes['appointment'],
                    $this->codes['schedule'],
                    $this->codes['company'],
                    $this->codes['cart'],
                    $this->codes['customer_timezone'],
                    $this->codes['customer'],
                    $this->codes['payment'],
                    $this->codes['service'],
                );
                break;
            case Notification::TYPE_NEW_INVOICE:
                $codes = array_merge(
                    $this->codes['company'],
                    $this->codes['customer'],
                    $this->codes['invoice'],
                );
                break;
            case Notification::TYPE_CUSTOMER_NEW_WP_USER:
                $codes = array_merge(
                    $this->codes['customer'],
                    $this->codes['user_credentials'],
                );
                break;
        }

        return $codes;
    }

    /**
     * @param array $groups
     * @return array
     */
    public function getGroups( array $groups )
    {
        $codes = array();
        foreach ( $groups as $group ) {
            if ( array_key_exists( $group, $this->codes ) ) {
                $codes = array_merge( $codes, $this->codes[ $group ] );
            }
        }

        ksort( $codes );

        return $codes;
    }
}