<?php
namespace ConnectpxBooking\Backend\Components\Dashboard\Appointments;

use ConnectpxBooking\Lib;
use ConnectpxBooking\Backend\Modules;

/**
 * Class Ajax
 * @package ConnectpxBooking\Backend\Components\Dashboard\Appointments
 */
class Ajax extends Lib\Base\Ajax
{
    public static function getAppointmentsDataForDashboard()
    {
        list ( $start, $end ) = explode( ' - ', self::parameter( 'range' ) );
        $start = date_create( $start );
        $end   = date_create( $end );
        $day   = array(
            'total'   => 0,
            'revenue' => 0,
        );
        $data  = array(
            'totals' => array(
                'approved' => 0,
                'pending'  => 0,
                'total'    => 0,
                'revenue'  => 0,
            ),
            'filters' => array(
                'approved' => sprintf( '%s#created-date=any&appointment-date=%s-%s&status=%s', Lib\Utils\Common::escAdminUrl( Modules\Appointments::pageSlug() ), $start->format( 'Y-m-d' ), $end->format( 'Y-m-d' ), 'approved' ),
                'pending'  => sprintf( '%s#created-date=any&appointment-date=%s-%s&status=%s', Lib\Utils\Common::escAdminUrl( Modules\Appointments::pageSlug() ), $start->format( 'Y-m-d' ), $end->format( 'Y-m-d' ), 'pending' ),
                'total'    => sprintf( '%s#created-date=any&appointment-date=%s-%s', Lib\Utils\Common::escAdminUrl( Modules\Appointments::pageSlug() ), $start->format( 'Y-m-d' ), $end->format( 'Y-m-d' ) ),
                'revenue'  => sprintf( '%s#appointment-date=%s-%s', Lib\Utils\Common::escAdminUrl(  Modules\Appointments::pageSlug() ), $start->format( 'Y-m-d' ), $end->format( 'Y-m-d' ) ),
            ),
            'days'   => array(),
            'labels' => array(),
        );
        $end->modify( '+1 day' );
        $period = new \DatePeriod( $start, \DateInterval::createFromDateString( '1 day' ), $end );
        /** @var \DateTime $dt */
        foreach ( $period as $dt ) {
            $data['labels'][] = date_i18n( 'M j', $dt->getTimestamp() );
            $data['days'][ $dt->format( 'Y-m-d' ) ] = $day;
        }

        $records = Lib\Entities\Appointment::query( 'a' )
            ->select( 'DATE(a.pickup_datetime) AS pickup_datetime, COUNT(1) AS quantity, a.paid_amount AS revenue, a.status, a.payment_status, a.id' )
            ->whereBetween( 'a.pickup_datetime', $start->format( 'Y-m-d' ), $end->format( 'Y-m-d' ) )
            ->groupBy( 'DATE(a.pickup_datetime), a.id, a.status' )
            ->fetchArray();

        // Consider payment for all appointments only 1 time
        $payment_ids = array();
        foreach ( $records as $record ) {
            $pickup_datetime = $record['pickup_datetime'];
            $quantity = $record['quantity'];
            $status   = $record['status'];
            $payment_status   = $record['payment_status'];

            if ( array_key_exists( $status, $data['totals'] ) ) {
                $data['totals'][ $status ] += $quantity;
            } 

            if( $payment_status == Lib\Entities\Appointment::PAYMENT_COMPLETED && in_array($status, Lib\Entities\Appointment::getCompletedStatuses()) ) {
                $revenue = $record['revenue'];
            } else {
                $revenue = 0;
            }

            $data['totals']['total']   += $quantity;
            $data['totals']['revenue'] += $revenue;
            $data['days'][ $pickup_datetime ]['total']   += $quantity;
            $data['days'][ $pickup_datetime ]['revenue'] += $revenue;
        }

        // $invoices_paid = Lib\Entities\Invoice::query( 'i' )
        //     ->select( 'SUM(i.paid_amount) as revenue' )
        //     ->whereBetween( 'i.start_date', $start->format( 'Y-m-d' ), $end->format( 'Y-m-d' ) )
        //     ->where( 'i.status', Lib\Entities\Invoice::STATUS_COMPLETED )
        //     ->groupBy( 'i.id' )
        //     ->fetchRow();

        // if( $invoices_paid ) {
        //     $data['totals']['revenue'] = Lib\Utils\Price::format( $invoices_paid['revenue'] );
        // }
        $data['totals']['revenue'] = Lib\Utils\Price::format( $data['totals']['revenue'] );

        wp_send_json_success( $data );
    }
}