<?php
namespace ConnectpxBooking\Backend;

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
     * Get list of appointments.
     */
    public static function getAppointments()
    {
        $columns = self::parameter( 'columns' );
        $order   = self::parameter( 'order', array() );
        $filter  = self::parameter( 'filter' );
        $limits  = array(
            'length' => self::parameter( 'length' ),
            'start'  => self::parameter( 'start' ),
        );

        $data = self::getAppointmentsTableData( $filter, $limits, $columns, $order );

        unset( $filter['date'] );

        wp_send_json( array(
            'draw'            => ( int ) self::parameter( 'draw' ),
            'recordsTotal'    => $data['total'],
            'recordsFiltered' => $data['filtered'],
            'data'            => $data['data'],
        ) );
    }

    /**
     * @param array $filter
     * @param array $limits
     * @param array $columns
     * @param array $order
     * @return array
     */
    public static function getAppointmentsTableData( $filter = array(), $limits = array(), $columns = array(), $order = array() )
    {
        $query = Lib\Entities\Appointment::query( 'a' )
            ->select( 'a.id,
                a.status,
                a.notes,
                a.created_at AS created_date,
                a.pickup_datetime,
                a.total_amount,
                a.payment_status,
                a.payment_type,
                CONCAT(c.first_name, " ", c.last_name)  AS customer_full_name,
                c.phone      AS customer_phone,
                c.email      AS customer_email,
                c.country    AS customer_country,
                c.state      AS customer_state,
                c.postcode   AS customer_postcode,
                c.city       AS customer_city,
                c.street     AS customer_street,
                c.street_number AS customer_street_number,
                c.additional_address AS customer_additional_address,
                s.title      AS service_title' 
            )
            ->leftJoin( 'Service', 's', 's.id = a.service_id' )
            ->leftJoin( 'Customer', 'c', 'c.id = a.customer_id' );

        $total = $query->count();

        if ( $filter['id'] != '' ) {
            $query->where( 'a.id', $filter['id'] );
        }

        if ( $filter['date'] == 'any' ) {
            $query->whereNot( 'a.pickup_datetime', null );
        } elseif ( $filter['date'] == 'null' ) {
            $query->where( 'a.pickup_datetime', null );
        } else {
            list ( $start, $end ) = explode( ' - ', $filter['date'], 2 );
            $end = date( 'Y-m-d', strtotime( $end ) + DAY_IN_SECONDS );
            $query->whereBetween( 'a.pickup_datetime', $start, $end );
        }

        if ( $filter['created_date'] != 'any' ) {
            list ( $start, $end ) = explode( ' - ', $filter['created_date'], 2 );
            $end = date( 'Y-m-d', strtotime( $end ) + DAY_IN_SECONDS );
            $query->whereBetween( 'a.created_at', $start, $end );
        }

        if ( $filter['customer'] != '' ) {
            $query->where( 'ca.customer_id', $filter['customer'] );
        }

        if ( $filter['service'] != '' ) {
            $query->where( 'a.service_id', $filter['service'] ?: null );
        }

        if ( $filter['status'] != '' ) {
            $query->where( 'a.status', $filter['status'] );
        }

        foreach ( $order as $sort_by ) {
            $query->sortBy( str_replace( '.', '_', $columns[ $sort_by['column'] ]['data'] ) )
                ->order( $sort_by['dir'] == 'desc' ? Lib\Query::ORDER_DESCENDING : Lib\Query::ORDER_ASCENDING );
        }

        $filtered = $query->count();

        if ( ! empty( $limits ) ) {
            $query->limit( $limits['length'] )->offset( $limits['start'] );
        }

        $data = array();
        foreach ( $query->fetchArray() as $row ) {
            // Appointment status.
            $row['status'] = Lib\Entities\Appointment::statusToString( $row['status'] );
            // Payment title.
            $payment_title = '';
            $payment_raw_title = '';
            if ( $row['total_amount'] !== null ) {
                $payment_title = Lib\Utils\Price::format( $row['total_amount'] );

                $payment_raw_title = trim( sprintf(
                    '%s %s %s',
                    $payment_title,
                    Lib\Entities\Appointment::paymentTypeToString( $row['payment_type'] ),
                    Lib\Entities\Appointment::paymentStatusToString( $row['payment_status'] )
                ) );

                $payment_title .= sprintf(
                    ' %s <span%s>%s</span>',
                    Lib\Entities\Appointment::paymentTypeToString( $row['payment_type'] ),
                    $row['payment_status'] == Lib\Entities\Appointment::PAYMENT_PENDING ? ' class="text-danger"' : '',
                    Lib\Entities\Appointment::paymentStatusToString( $row['payment_status'] )
                );
            }

            $data[] = array(
                'id'                => $row['id'],
                'pickup_datetime'        => $row['pickup_datetime'] === null ? __( 'N/A', 'connectpx_booking' ) : Lib\Utils\DateTime::formatDateTime( $row['pickup_datetime'] ),
                'customer'          => array(
                    'full_name' => $row['customer_full_name'],
                    'phone' => $row['customer_phone'],
                    'email' => $row['customer_email'],
                    'address' =>  Lib\Utils\Common::getFullAddressByCustomerData( array(
                        'country' => $row['customer_country'],
                        'state' => $row['customer_state'],
                        'postcode' => $row['customer_postcode'],
                        'city' => $row['customer_city'],
                        'street' => $row['customer_street'],
                        'street_number' => $row['customer_street_number'],
                        'additional_address' => $row['customer_additional_address'],
                    ) )
                ),
                'service'           => array(
                    'title'    => $row['service_title'],
                ),
                'status'            => $row['status'],
                'payment'           => $payment_title,
                'payment_raw_title' => $payment_raw_title,
                'notes'             => $row['notes'],
                'created_date'      => Lib\Utils\DateTime::formatDateTime( $row['created_date'] ),
            );
        }

        return compact( 'data', 'total', 'filtered' );
    }

    /**
     * Get list of customers.
     */
    public static function getCustomersList()
    {
        global $wpdb;

        $max_results = self::parameter( 'max_results', 20 );
        $filter      = self::parameter( 'filter' );
        $page        = self::parameter( 'page' );
        $query       = Lib\Entities\Customer::query( 'c' );

        $query->select( 'SQL_CALC_FOUND_ROWS c.id, CONCAT(c.first_name, " ", c.last_name) AS text, c.email, c.phone' );

        if ( $filter != '' ) {
            $search_value = Lib\Query::escape( $filter );
            $query
                ->whereLike( 'c.first_name', "%{$search_value}%" )
                ->whereLike( 'c.last_name', "%{$search_value}%" )
                ->whereLike( 'c.phone', "%{$search_value}%", 'OR' )
                ->whereLike( 'c.email', "%{$search_value}%", 'OR' )
            ;
        }

        $query->limit( $max_results )->offset( ( $page - 1 ) * $max_results );

        $rows = $query->fetchArray();
        $more = ( int ) $wpdb->get_var( 'SELECT FOUND_ROWS()' ) > $max_results * $page;

        $customers = array();
        foreach ( $rows as $customer ) {
            $name = $customer['text'];
            if ( $customer['email'] != '' || $customer['phone'] != '' ) {
                $name .= ' (' . trim( $customer['email'] . ', ' . $customer['phone'], ', ' ) . ')';
            }
            $customer['name'] = $name;
            $customers[] = $customer;
        }

        wp_send_json( array(
            'results'    => $customers,
            'pagination' => array(
                'more' => $more,
            ),
        ) );
    }

}