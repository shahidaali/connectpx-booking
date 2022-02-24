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
     * Get list of appointments.
     */
    public static function getInvoices()
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

    /**
     * Get data for Event Calendar
     */
    public static function getCalendarAppointments()
    {
        $result     = array();
        $one_day    = new \DateInterval( 'P1D' );
        $start_date = new \DateTime( self::parameter( 'start' ) );
        $end_date   = new \DateTime( self::parameter( 'end' ) );

        // Determine display time zone
        $display_tz = Lib\Utils\Common::getCurrentUserTimeZone();

        // Due to possibly different time zones of staff members expand start and end dates
        // to provide 100% coverage of the requested date range
        $start_date->sub( $one_day );
        $end_date->add( $one_day );

        // Load special days.
        $special_days = array();
        $query = self::getAppointmentsQueryForCalendar( $start_date, $end_date );
        $appointments = self::buildAppointmentsForCalendar( $query, $display_tz );
        $result = array_merge( $result, $appointments );

        // Schedule
        $schedule = array();
        $day   = clone $start_date;
        $items = Lib\Config::getBusinessHours();

        // Find previous day end time.
        $last_end = clone $day;
        $last_end->sub( $one_day );
        $last_end->setTime( 24, 0 );
        // Do the loop.
        while ( $day < $end_date ) {
            $start = $last_end->format( 'Y-m-d H:i:s' );
            $item = $items[ (int) $day->format( 'w' ) + 1 ];
            if ( $item['start'] !== null ) {
                $end = $day->format( 'Y-m-d ' . $item['start'] );
                if ( $start < $end ) {
                    $schedule[] = compact( 'start', 'end' );
                }
                $last_end = clone $day;
                $end_time = explode( ':', $item['end'] );
                $last_end->setTime( $end_time[0], $end_time[1] );
            }
            

            $day->add( $one_day );
        }

        if ( $last_end->format( 'Ymd' ) != $day->format( 'Ymd' ) ) {
            $schedule[] = array(
                'start' => $last_end->format( 'Y-m-d H:i:s' ),
                'end'   => $day->format( 'Y-m-d 24:00:00' ),
            );
        }

        // Add schedule to result,
        // with appropriate time zone shift if needed
        foreach ( $schedule as $item ) {
            $result[] = array(
                'start'      => $item['start'],
                'end'        => $item['end'],
                'display'    => 'background',
            );
        }

        wp_send_json( $result );
    }

    /**
     * Update calendar refresh rate.
     */
    public static function updateCalendarRefreshRate()
    {
        $rate = (int) self::parameter( 'rate', 0 );
        update_user_meta( get_current_user_id(), 'connectpx_booking_calendar_refresh_rate', $rate );

        wp_send_json_success();
    }

    /**
     * Get appointments query for Event Calendar
     *
     * @param int $staff_id
     * @param \DateTime $start_date
     * @param \DateTime $end_date
     * @param array|null $location_ids
     * @return Lib\Query
     */
    public static function getAppointmentsQueryForCalendar( \DateTime $start_date, \DateTime $end_date )
    {
        $query = Lib\Entities\Appointment::query( 'a' )
            ->whereGt( 'a.pickup_datetime', $start_date->format( 'Y-m-d H:i:s' ) )
            ->whereLt( 'a.pickup_datetime', $end_date->format( 'Y-m-d H:i:s' ) );

        $service_ids = array_filter( explode( ',', self::parameter( 'service_ids' ) ) );

        if ( ! empty( $service_ids ) && ! in_array( 'all', $service_ids ) ) {
            $raw_where = array();
            if ( in_array( 'custom', $service_ids ) ) {
                $raw_where[] = 'a.service_id IS NULL';
            }

            $service_ids = array_filter( $service_ids, 'is_numeric' );
            if ( ! empty( $service_ids ) ) {
                $raw_where[] = 'a.service_id IN (' . implode( ',', $service_ids ) . ')';
            }

            if ( $raw_where ) {
                $query->whereRaw( implode( ' OR ', $raw_where ), array() );
            }
        }

        return $query;
    }

    /**
     * Build appointments for Event Calendar.
     *
     * @param Lib\Query $query
     * @param int $staff_id
     * @param string $display_tz
     * @return mixed
     */
    public static function buildAppointmentsForCalendar( Lib\Query $query, $display_tz )
    {
        $template = '<div>' . str_replace( "\n", '</div><div>', Lib\Utils\Common::getOption( 'calendar_format', '' ) ) . '</div>';

        $participants = null;
        $coloring_mode = 'status';
        $query
            ->select( 'a.id, 
                a.admin_notes, 
                a.pickup_datetime, 
                a.return_pickup_datetime,
                a.status AS status,
                a.notes AS appointment_notes,
                a.total_amount, 
                a.payment_type, 
                a.payment_status, 
                a.paid_amount,
                s.title AS service_name, 
                s.description AS service_description,
                CONCAT(c.first_name, " ", c.last_name) AS client_name, 
                c.first_name AS client_first_name, 
                c.last_name AS client_last_name, 
                c.phone AS client_phone, 
                c.email AS client_email, 
                c.id AS customer_id, 
                c.notes AS client_note,
                c.country, 
                c.state, 
                c.postcode, 
                c.city, 
                c.street, 
                c.street_number, 
                c.additional_address
            ')
            ->leftJoin( 'Customer', 'c', 'c.id = a.customer_id' )
            ->leftJoin( 'Service', 's', 's.id = a.service_id' );

        // Fetch appointments,
        // and shift the dates to appropriate time zone if needed
        $appointments = array();
        $wp_tz = Lib\Config::getWPTimeZone();
        $convert_tz = $display_tz !== $wp_tz;

        foreach ( $query->fetchArray() as $appointment ) {
            if ( ! isset ( $appointments[ $appointment['id'] ] ) ) {
                if ( $convert_tz ) {
                    $appointment['pickup_datetime'] = Lib\Utils\DateTime::convertTimeZone( $appointment['pickup_datetime'], $wp_tz, $display_tz );
                    $appointment['return_pickup_datetime'] = $appointment['return_pickup_datetime'] ? Lib\Utils\DateTime::convertTimeZone( $appointment['return_pickup_datetime'], $wp_tz, $display_tz ) : null;
                }
                $appointments[ $appointment['id'] ] = $appointment;
            }
            $appointments[ $appointment['id'] ]['customer'] = array(
                'appointment_notes' => $appointment['appointment_notes'],
                'client_email' => $appointment['client_email'],
                'client_first_name' => $appointment['client_first_name'],
                'client_last_name' => $appointment['client_last_name'],
                'client_name' => $appointment['client_name'],
                'client_note' => $appointment['client_note'],
                'client_phone' => $appointment['client_phone'],
                'total_amount' => $appointment['total_amount'],
                'paid_amount' => $appointment['paid_amount'],
                'payment_status' => Lib\Entities\Appointment::paymentStatusToString( $appointment['payment_status'] ),
                'payment_type' => Lib\Entities\Appointment::paymentTypeToString( $appointment['payment_type'] ),
                'status' => $appointment['status'],
            );
        }

        $status_codes = array(
            Lib\Entities\Appointment::STATUS_APPROVED => 'success',
            Lib\Entities\Appointment::STATUS_NOSHOW => 'success',
            Lib\Entities\Appointment::STATUS_CANCELLED => 'danger',
            Lib\Entities\Appointment::STATUS_REJECTED => 'danger',
        );
        $cancelled_statuses = array(
            Lib\Entities\Appointment::STATUS_CANCELLED,
            Lib\Entities\Appointment::STATUS_REJECTED,
        );
        $pending_statuses = array(
            Lib\Entities\Appointment::STATUS_CANCELLED,
            Lib\Entities\Appointment::STATUS_REJECTED,
            Lib\Entities\Appointment::STATUS_PENDING,
        );
        $colors = array();
        if ( $coloring_mode == 'status' ) {
            $colors = array(
                Lib\Entities\Appointment::STATUS_PENDING => "#1e73be",
                Lib\Entities\Appointment::STATUS_APPROVED => "#81d742",
                Lib\Entities\Appointment::STATUS_CANCELLED => "#eeee22",
                Lib\Entities\Appointment::STATUS_REJECTED => "#dd3333",
            );
            $colors['mixed'] = "#8224e3";
        }
        foreach ( $appointments as $key => $appointment ) {
            $codes['appointment_date'] = Lib\Utils\DateTime::formatDate( $appointment['pickup_datetime'] );
            $codes['appointment_time'] = Lib\Utils\DateTime::formatTime( $appointment['pickup_datetime'] );
            $codes['booking_number'] = $appointment['id'];
            $codes['admin_notes'] = esc_html( $appointment['admin_notes'] );
            $codes['service_name'] = $appointment['service_name'] ? esc_html( $appointment['service_name'] ) : __( 'Untitled', 'connectpx_booking' );
            $codes['service_description'] = esc_html( $appointment['service_description'] );
            $codes['total_amount'] = Lib\Utils\Price::format( $appointment['total_amount'] );
            $codes['paid_amount'] = Lib\Utils\Price::format( $appointment['paid_amount'] );
            $codes['payment_status'] = Lib\Entities\Appointment::paymentStatusToString( $appointment['payment_status'] );
            $codes['payment_type'] = Lib\Entities\Appointment::paymentTypeToString( $appointment['payment_type'] );
            $codes['status'] = Lib\Entities\Appointment::statusToString( $appointment['status'] );
            $codes['client_name'] = $appointment['client_name'];
            $codes['client_email'] = $appointment['client_email'];
            $codes['client_phone'] = $appointment['client_phone'];

            // Customers for popover.
            $popover_customers = '';
            $overall_status = $appointment['customer']['status'];

            $codes['participants'] = array();
            $event_status = null;
            $status_color = 'secondary';
            $customer = $appointment['customer'];
            if ( isset( $status_codes[ $customer['status'] ] ) ) {
                $status_color = $status_codes[ $customer['status'] ];
            }
            if ( $coloring_mode == 'status' ) {
                if ( $event_status === null ) {
                    $event_status = $customer['status'];
                } elseif ( $event_status != $customer['status'] ) {
                    $event_status = 'mixed';
                }
            }
            if ( $customer['status'] != $overall_status && ( ! in_array( $customer['status'], $cancelled_statuses ) || ! in_array( $overall_status, $cancelled_statuses ) ) ) {
                if ( in_array( $customer['status'], $pending_statuses ) && in_array( $overall_status, $pending_statuses ) ) {
                    $overall_status = Lib\Entities\Appointment::STATUS_PENDING;
                } else {
                    $overall_status = '';
                }
            }
            $popover_customers .= '<div class="d-flex"><div class="text-muted flex-fill">' . $customer['client_name'] . '</div><div class="text-nowrap"><span class="badge badge-' . $status_color . '">' . Lib\Entities\Appointment::statusToString( $customer['status'] ) . '</span></div></div>';
            $codes['participants'][] = $customer;

            $tooltip = '<i class="fas fa-fw fa-circle mr-1" style="color:#81d742"></i><span>{service_name}</span>' . $popover_customers . '<span class="d-block text-muted">{appointment_time}</span>';

            $color = $colors[ $event_status ];

            $appointments[ $key ] = array(
                'id' => $appointment['id'],
                'start' => $appointment['pickup_datetime'],
                'end' => $appointment['pickup_datetime'],
                //'end' => Lib\Slots\DatePoint::fromStr($appointment['pickup_datetime'])->modify("+1 hour")->format("Y-m-d H:i:s"),
                'title' => ' ',
                'color' => $color,
                'resourceId' => $appointment['id'],
                'extendedProps' => array(
                    'tooltip' => Lib\Utils\Codes::replace( $tooltip, $codes, false ),
                    'desc' => Lib\Utils\Codes::replace( $template, $codes, false ),
                    'overall_status' => $overall_status,
                    'header_text' => $appointment['appointment_time'],
                ),
            );
        }

        return array_values( $appointments );
    }
}