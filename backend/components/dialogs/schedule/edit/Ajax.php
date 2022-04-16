<?php
namespace ConnectpxBooking\Backend\Components\Dialogs\Schedule\Edit;

use ConnectpxBooking\Backend\Modules\Calendar;
use ConnectpxBooking\Lib;
use ConnectpxBooking\Lib\Entities\Schedule;
use ConnectpxBooking\Lib\Entities\Customer;
use ConnectpxBooking\Lib\Entities\Service;
use ConnectpxBooking\Lib\Slots\DatePoint;
use ConnectpxBooking\Lib\Utils\Common;
use ConnectpxBooking\Lib\Utils\DateTime;

/**
 * Class Ajax
 * @package ConnectpxBooking\Backend\Components\Dialogs\Schedule\Edit
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
     * Get schedule data when editing an schedule.
     */
    public static function renderEditSchedule()
    {
        $statuses = array();
        foreach ( Schedule::getStatuses() as $status ) {
            $statuses[] = array(
                'id' => $status,
                'title' => Schedule::statusToString( $status ),
                'icon' => Schedule::statusToIcon( $status )
            );
        }

        $response = array( 'success' => false, 'data' => array( 'customers' => array() ) );
        $schedule = new Schedule();
        if ( $schedule->load( self::parameter( 'id' ) ) ) {
            $response['success'] = true;

            $query = Schedule::query( 's' )
                ->select( 's.*,
                    CONCAT(c.first_name, " ", c.last_name) as full_name,
                    c.email,
                    c.phone
                ' )
                ->leftJoin( 'Customer', 'c', 'c.id = s.customer_id' )
                ->where( 's.id', $schedule->getId() );

            // Fetch schedule,
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

            $appointment = $schedule->loadFirstAppointment();
            $service_info = $appointment->getServiceInfo();
            $pickup_info = $appointment->getPickupInfo();
            $destination_info = $appointment->getDestinationInfo();
            $response['data']['id']              = (int) $info['id'];
            $response['data']['status']              = $info['status'];
            $response['data']['timezone']              = Lib\Utils\Common::getCustomerTimezone( $info['time_zone'], $info['time_zone_offset'] );
            $response['data']['service_id']              = (int) $info['service_id'];
            $response['data']['notes']           = $info['notes'];
            $response['data']['admin_notes']           = $info['admin_notes'];
            $response['data']['schedule_info']           = $schedule->getScheduleInfo();
            $response['data']['pickup_info']           = $pickup_info;
            $response['data']['destination_info']           = $destination_info;
            $response['data']['service_info']           = $service_info;
            $response['data']['map_link']           = $appointment->getMapLink();

            $response['data']['html'] = self::renderTemplate( 'backend/components/dialogs/schedule/edit/templates/modal', compact( 'schedule', 'statuses' ), false );
        }
        wp_send_json( $response );
    }

    /**
     * Save schedule form (for both create and edit).
     */
    public static function updateScheduleStatus()
    {
        $response = array( 'success' => false );
        $schedule_id       = (int) self::parameter( 'id', 0 );
        $schedule_status          = self::parameter( 'schedule_status' );

        // If no errors then try to save the schedule.
        if ( ! isset ( $response['errors'] ) ) {
            // Single schedule.
            $schedule = new Schedule();
            if ( $schedule->load( $schedule_id ) ) {
                $result = $schedule->updateStatus( $schedule_status, true );

                if( $result ) {
                    $response['success'] = true;
                } else if( $result === false ) {
                    $response['errors'] = array( 'db' => __( 'Could not save appointment in database.', 'connectpx_booking' ) );
                }
            }
            
        }

        wp_send_json( $response );
    }
}