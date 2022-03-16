<?php
namespace ConnectpxBooking\Backend\Components\Dialogs\Appointment\Edit;

use ConnectpxBooking\Backend\Modules\Calendar;
use ConnectpxBooking\Lib;
use ConnectpxBooking\Lib\Entities\Appointment;
use ConnectpxBooking\Lib\Entities\Customer;
use ConnectpxBooking\Lib\Entities\Service;
use ConnectpxBooking\Lib\Slots\DatePoint;
use ConnectpxBooking\Lib\Utils\Common;
use ConnectpxBooking\Lib\Utils\DateTime;

/**
 * Class Ajax
 * @package ConnectpxBooking\Backend\Components\Dialogs\Appointment\Edit
 */
class Ajax extends Lib\Base\Ajax
{
    /**
     * @inheritDoc
     */
    protected static function permissions()
    {
        return array( '_default' => array( 'anonymous' ) );
    }

    /**
     * Get appointment data when editing an appointment.
     */
    public static function renderEditAppointment()
    {
        $statuses = array();
        foreach ( Appointment::getStatuses() as $status ) {
            $statuses[] = array(
                'id' => $status,
                'title' => Appointment::statusToString( $status ),
                'icon' => Appointment::statusToIcon( $status )
            );
        }

        $payment_statuses = array();
        foreach ( Appointment::getPaymentStatuses() as $status ) {
            $payment_statuses[] = array(
                'id' => $status,
                'title' => Appointment::paymentStatusToString( $status ),
                'icon' => Appointment::paymentStatusToIcon( $status )
            );
        }

        $response = array( 'success' => false, 'data' => array( 'customers' => array() ) );
        $active_tab       = self::parameter( 'tab', 'appointment' );
        $appointment = new Appointment();
        if ( $appointment->load( self::parameter( 'id' ) ) ) {
            $response['success'] = true;

            $query = Appointment::query( 'a' )
                ->select( 'a.*,
                    CONCAT(c.first_name, " ", c.last_name) as full_name,
                    c.email,
                    c.phone
                ' )
                ->leftJoin( 'Customer', 'c', 'c.id = a.customer_id' )
                ->where( 'a.id', $appointment->getId() );

            // Fetch appointment,
            // and shift the dates to appropriate time zone if needed
            $info = $query->fetchRow();
            
            $name = $info['full_name'];
            if ( $info['email'] != '' || $info['phone'] != '' ) {
                $name .= ' (' . trim( $info['email'] . ', ' . $info['phone'], ', ' ) . ')';
            }
            $response['data']['customer_data'] = array(
                'id'                => (int) $info['customer_id'],
                'name'              => $name,
                'timezone'          => Lib\Utils\Common::getLastCustomerTimezone( $info['info_id'] ),
            );

            $service_info = $appointment->getServiceInfo();
            $response['data']['id']              = (int) $info['id'];
            $response['data']['status']              = $info['status'];
            $response['data']['timezone']              = Lib\Utils\Common::getCustomerTimezone( $info['time_zone'], $info['time_zone_offset'] );
            $response['data']['service_id']              = (int) $info['service_id'];
            $response['data']['notes']           = $info['notes'];
            $response['data']['admin_notes']           = $info['admin_notes'];
            $response['data']['schedule_info']           = $appointment->getScheduleInfo();
            $response['data']['pickup_info']           = $appointment->getPickupInfo();
            $response['data']['destination_info']           = $appointment->getDestinationInfo();
            $response['data']['service_info']           = $service_info;
            $response['data']['map_link']           = $appointment->getMapLink();
            $response['data']['payment_info'] = self::renderTemplate( 'backend/components/dialogs/appointment/edit/templates/payment', compact( 'payment_statuses', 'service_info', 'appointment' ), false );

            $response['data']['html'] = self::renderTemplate( 'backend/components/dialogs/appointment/edit/templates/modal', compact( 'appointment', 'statuses', 'active_tab' ), false );
        }
        wp_send_json( $response );
    }

    /**
     * Save appointment form (for both create and edit).
     */
    public static function updateAppointmentStatus()
    {
        $response = array( 'success' => false );
        $appointment_id       = (int) self::parameter( 'id', 0 );
        $appointment_status          = self::parameter( 'appointment_status' );

        // If no errors then try to save the appointment.
        if ( ! isset ( $response['errors'] ) ) {
            // Single appointment.
            $appointment = new Appointment();
            if ( $appointment->load( $appointment_id ) ) {
                if( $appointment->getStatus() != $appointment_status ) {

                    if( $appointment_status == Appointment::STATUS_NOSHOW || $appointment->getIsNoShow() ) {
                        $subService = $appointment->getSubService();

                        $payment_details = !empty($appointment->getPaymentDetails()) ? json_decode($appointment->getPaymentDetails(), true) : [];
                        $adjustments = isset($payment_details['adjustments']) ? $payment_details['adjustments'] : [];

                        $isNowShow = $appointment_status == Appointment::STATUS_NOSHOW;

                        $itemPrice = $subService->paymentLineItems( 
                            $appointment->getDistance(),
                            $appointment->getWaitingTime(),
                            $appointment->getIsAfterHours(),
                            $isNowShow,
                            $adjustments
                        );

                        $appointment
                            ->setTotalAmount( $itemPrice['totals'] );
                    }

                    $appointment
                        ->setStatus( $appointment_status );

                    $modified = $appointment->getModified();
                    if ( $appointment->save() !== false ) {
                        Lib\Notifications\Appointment\Sender::send( $appointment );
                        $response['success'] = true;
                    } else {
                        $response['errors'] = array( 'db' => __( 'Could not save appointment in database.', 'connectpx_booking' ) );
                    }
                }
            }
            
        }

        wp_send_json( $response );
    }

    /**
     * Save appointment form (for both create and edit).
     */
    public static function saveAppointmentForm()
    {
        $response = array( 'success' => false );
        $appointment_id       = (int) self::parameter( 'id', 0 );
        $admin_notes          = self::parameter( 'admin_notes' );

        // If no errors then try to save the appointment.
        if ( ! isset ( $response['errors'] ) ) {
            // Single appointment.
            $appointment = new Appointment();
            if ( $appointment_id ) {
                // Edit.
                $appointment->load( $appointment_id );
            }
            $appointment
                ->setAdminNotes( $admin_notes );

            $modified = $appointment->getModified();
            if ( $appointment->save() !== false ) {
                $response['success'] = true;
            } else {
                $response['errors'] = array( 'db' => __( 'Could not save appointment in database.', 'connectpx_booking' ) );
            }
        }

        wp_send_json( $response );
    }

    /**
     * Save appointment form (for both create and edit).
     */
    public static function adjustAppointmentPayment()
    {
        $response = array( 'success' => false );
        $appointment_id       = (int) self::parameter( 'id', 0 );
        $miles          = (int) self::parameter( 'miles' );
        $waiting_time          = (int) self::parameter( 'waiting_time' );
        $adjustment_reason          = self::parameter( 'adjustment_reason' );
        $adjustment_amount          = (float) self::parameter( 'adjustment_amount' );

        // If no errors then try to save the appointment.
        if ( ! isset ( $response['errors'] ) ) {
            // Single appointment.
            $appointment = new Appointment();
            if ( $appointment->load( $appointment_id ) ) {
                $subService = $appointment->getSubService();

                $payment_details = !empty($appointment->getPaymentDetails()) ? json_decode($appointment->getPaymentDetails(), true) : [];
                $adjustments = isset($payment_details['adjustments']) ? $payment_details['adjustments'] : [];

                // $adjustments = $appointment->getPaymentAdjustments();
                if( !empty($adjustment_reason) && $adjustment_amount <> 0 ) {
                    $adjustments[] = [
                        'reason' => $adjustment_reason,
                        'amount' => $adjustment_amount
                    ];
                }

                $itemPrice = $subService->paymentLineItems( 
                    $miles,
                    $waiting_time,
                    $appointment->getIsAfterHours(),
                    $appointment->getIsNoShow(),
                    $adjustments
                );

                $appointment
                    ->setDistance( $miles )
                    ->setWaitingTime( $waiting_time )
                    ->setTotalAmount( $itemPrice['totals'] );

                if( !empty($adjustments) ) {
                    $payment_details['adjustments'] = $adjustments;

                    $appointment
                        ->setPaymentDetails( json_encode( $payment_details ) );
                }

                if ( $appointment->save() !== false ) {

                    // Update Invoice if exists
                    $invoiceRow = Lib\Entities\InvoiceAppointment::query( 'ia' )
                        ->select( 'i.*' )
                        ->innerJoin( 'Invoice', 'i', 'ia.invoice_id = i.id' )
                        ->where('ia.appointment_id', $appointment->getId())
                        ->fetchRow();
                    if( !empty($invoiceRow) ) {
                        $invoice = new Lib\Entities\Invoice();
                        $invoice->setFields($invoiceRow, true);
                        $invoice->updateTotals();
                    }

                    $response['success'] = true;
                } else {
                    $response['errors'] = array( 'db' => __( 'Could not save appointment in database.', 'connectpx_booking' ) );
                }
            }
            
        }

        wp_send_json( $response );
    }

    /**
     * Save appointment form (for both create and edit).
     */
    public static function updateAppointmentPaymentStatus()
    {
        $response = array( 'success' => false );
        $appointment_id       = (int) self::parameter( 'id', 0 );
        $payment_status          = self::parameter( 'payment_status' );

        // If no errors then try to save the appointment.
        if ( ! isset ( $response['errors'] ) ) {
            // Single appointment.
            $appointment = new Appointment();
            if ( $appointment->load( $appointment_id ) ) {
                if( $appointment->getPaymentStatus() != $payment_status ) {

                    $appointment
                        ->setPaymentStatus( $payment_status );

                    $modified = $appointment->getModified();
                    if ( $appointment->save() !== false ) {
                        $response['success'] = true;
                    } else {
                        $response['errors'] = array( 'db' => __( 'Could not save appointment in database.', 'connectpx_booking' ) );
                    }
                }
            }
            
        }

        wp_send_json( $response );
    }
}