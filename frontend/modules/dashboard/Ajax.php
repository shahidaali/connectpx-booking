<?php
namespace ConnectpxBooking\Frontend\Modules\Dashboard;

use ConnectpxBooking\Lib;
/**
 * Class Ajax
 * @package ConnectpxBooking\Frontend\Modules\Booking
 */
class Ajax extends Lib\Base\Ajax
{
    /** @var BooklyLib\Entities\Customer */
    protected static $customer;

    /**
     * @inheritDoc
     */
    protected static function permissions()
    {
        return array( '_default' => 'customer' );
    }

    /**
     * Get a list of appointments
     */
    public static function getCustomerAppointments()
    {
        $columns = self::parameter( 'columns' );
        $appointment_columns = self::parameter( 'appointment_columns' );
        $order = self::parameter( 'order', array() );
        $client_diff = get_option( 'gmt_offset' ) * MINUTE_IN_SECONDS;
        $date_filter = self::parameter( 'date' );
        $service_filter = self::parameter( 'service' );
        $search_query = self::parameter( 'search_query', null );

        $query = Lib\Entities\Appointment::query( 'a' )
            ->select( 'a.*,
                    IF (a.time_zone_offset IS NULL,
                        a.pickup_datetime,
                        DATE_SUB(a.pickup_datetime, INTERVAL ' . $client_diff . ' + a.time_zone_offset MINUTE)
                    ) AS pickup_datetime,
                    s.title AS service_title
            ' )
            ->leftJoin( 'Service', 's', 's.id = a.service_id' )
            ->where( 'a.customer_id', self::$customer->getId() )
            ->groupBy( 'a.id' );

        if ( $date_filter !== null ) {
            if ( $date_filter == 'any' ) {
                $query->whereNot( 'a.pickup_datetime', null );
            } elseif ( $date_filter == 'null' ) {
                $query->where( 'a.pickup_datetime', null );
            } else {
                list ( $start, $end ) = explode( ' - ', $date_filter, 2 );
                $end = date( 'Y-m-d', strtotime( $end ) + DAY_IN_SECONDS );
                $query->whereBetween( 'DATE(a.pickup_datetime)', $start, $end );
            }
        }

        if ( $service_filter !== null && $service_filter !== '' ) {
            $query->where( 'a.service_id', $service_filter ?: null );
        }

        if ( $search_query ) {
            $query->whereLike( 'a.pickup_detail', '%' . $search_query .'%' );
        }

        foreach ( $order as $sort_by ) {
            $query->sortBy( str_replace( '.', '_', $columns[ $sort_by['column'] ]['data'] ) )
                ->order( $sort_by['dir'] == 'desc' ? Lib\Query::ORDER_DESCENDING : Lib\Query::ORDER_ASCENDING );
        }

        $total = $query->count( true );
        $query->limit( self::parameter( 'length' ) )->offset( self::parameter( 'start' ) );

        $data = array();
        foreach ( $query->fetchArray() as $row ) {
            $appointment = new Lib\Entities\Appointment($row);
            // Appointment status.
            $service_title = Lib\Entities\Service::find( $row['service_id'] )->getTitle();
            $allow_cancel = $appointment->cancelAllowed();
            $allow_reschedule = $appointment->rescheduleAllowed();
            $total_amount = Lib\Utils\Price::format( $row['total_amount'] );

            $pickupInfo = $appointment->getPickupDetail() ? json_decode( $appointment->getPickupDetail(), true ) : [];
            $destinationInfo = $appointment->getDestinationDetail() ? json_decode( $appointment->getDestinationDetail(), true ) : [];

            $data[] = array(
                'id' => $row['id'],
                'date' => strtotime( $row['pickup_datetime'] ),
                'raw_start_date' => $row['pickup_datetime'],
                'pickup_datetime' => ( ( in_array( 'timezone', $appointment_columns ) && $timezone = Lib\Utils\Common::getCustomerTimezone( $row['time_zone'], $row['time_zone_offset'] ) ) ? sprintf( '%s<br/>(%s)', Lib\Utils\DateTime::formatDateTime( $row['pickup_datetime'] ), $timezone ) : Lib\Utils\DateTime::formatDateTime( $row['pickup_datetime'] ) ),
                'service_title' => $service_title,
                'patient' => $pickupInfo['patient_name'] ?? 'N/A',
                'destination' => $destinationInfo['address']['address'] ?? 'N/A',
                'status' => Lib\Entities\Appointment::statusToString( $row['status'] ),
                'total_amount' => $total_amount,
                'allow_cancel' => $allow_cancel,
                'allow_reschedule' => $allow_reschedule,
            );
        }

        $data = array_values( $data );

        wp_send_json( array(
            'draw' => (int) self::parameter( 'draw' ),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $data,
        ) );
    }

    /**
     * Get a list of invoices
     */
    public static function getCustomerInvoices()
    {
        $columns = self::parameter( 'columns' );
        $invoice_columns = self::parameter( 'invoice_columns' );
        $order = self::parameter( 'order', array() );
        $date_filter = self::parameter( 'date' );
        $due_date_filter = self::parameter( 'due_date' );
        $status_filter = self::parameter( 'status' );

        $query = Lib\Entities\Invoice::query( 'i' )
            ->select( 'i.*' )
            ->where( 'i.customer_id', self::$customer->getId() )
            ->groupBy( 'i.id' );

        if ( $date_filter !== null ) {
            if ( $date_filter == 'any' ) {
                $query->whereNot( 'i.start_date', null );
            } elseif ( $date_filter == 'null' ) {
                $query->where( 'i.start_date', null );
            } else {
                list ( $start, $end ) = explode( ' - ', $date_filter, 2 );
                $end = date( 'Y-m-d', strtotime( $end ) + DAY_IN_SECONDS );
                $query->whereBetween( 'DATE(i.start_date)', $start, $end );
            }
        }

        if ( $due_date_filter !== null ) {
            if ( $due_date_filter == 'any' ) {
                $query->whereNot( 'i.due_date', null );
            } elseif ( $due_date_filter == 'null' ) {
                $query->where( 'i.due_date', null );
            } else {
                list ( $start, $end ) = explode( ' - ', $due_date_filter, 2 );
                $end = date( 'Y-m-d', strtotime( $end ) + DAY_IN_SECONDS );
                $query->whereBetween( 'DATE(i.due_date)', $start, $end );
            }
        }

        if ( $status_filter != '' ) {
            $query->where( 'i.status', $status_filter );
        }

        foreach ( $order as $sort_by ) {
            $query->sortBy( str_replace( '.', '_', $columns[ $sort_by['column'] ]['data'] ) )
                ->order( $sort_by['dir'] == 'desc' ? Lib\Query::ORDER_DESCENDING : Lib\Query::ORDER_ASCENDING );
        }

        $total = $query->count( true );
        $query->limit( self::parameter( 'length' ) )->offset( self::parameter( 'start' ) );

        $data = array();
        foreach ( $query->fetchArray() as $row ) {
            // Invoice status.
            $invoice = new Lib\Entities\Invoice();
            $invoice->setFields($row);

            $data[] = array(
                'id' => $invoice->getId(),
                'start_date' => Lib\Utils\DateTime::formatDate( $invoice->getStartDate(), 'm/d/Y' ),
                'end_date' => Lib\Utils\DateTime::formatDate( $invoice->getEndDate(), 'm/d/Y' ),
                'due_date' => Lib\Utils\DateTime::formatDate( $invoice->getDueDate(), 'm/d/Y' ),
                'status' => Lib\Entities\Invoice::statusToString( $invoice->getStatus() ),
                'total_amount' => Lib\Utils\Price::format( $invoice->getTotalAmount() ),
                'due_amount' => Lib\Utils\Price::format( $invoice->getTotalAmount() - $invoice->getPaidAmount() ),
                'download_link' => home_url( '/my-account/invoices?download_invoice=' . $invoice->getId() ),
            );
        }

        $data = array_values( $data );

        wp_send_json( array(
            'draw' => (int) self::parameter( 'draw' ),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $data,
        ) );
    }

    public static function customerSaveProfile()
    {
        $columns = explode( ',', self::parameter( 'columns' ) );
        $profile_data = self::parameters();
        $response = array( 'success' => true, 'errors' => array() );

        foreach ( $profile_data as $field => $value ) {
            $errors = array();
            switch ( $field ) {
                case 'last_name':
                case 'first_name':
                case 'full_name':
                    $errors = self::_validateProfile( 'name', $profile_data );
                    break;
                case 'email':
                    $errors = self::_validateProfile( 'email', $profile_data );
                    break;
                case 'phone':
                    $errors = self::_validateProfile( 'phone', $profile_data );
                    break;
                case 'country':
                case 'state':
                case 'postcode':
                case 'city':
                case 'street':
                case 'street_number':
                case 'additional_address':
                    $errors = self::_validateProfile( 'address', $profile_data );
                    break;

            }
            $response['errors'] = array_merge( $response['errors'], $errors );
        }

        if ( empty( $response['errors'] ) && $profile_data['current_password'] ) {
            // Update wordpress password
            $user = get_userdata( self::$customer->getWpUserId() );
            if ( $user ) {
                if ( ! wp_check_password( $profile_data['current_password'], $user->data->user_pass ) ) {
                    $response['errors']['current_password'][] = __( 'Wrong current password', 'connectpx_booking' );
                }
            }
            if ( $profile_data['new_password_1'] == '' ) {
                $response['errors']['new_password_1'][] = __( 'Required', 'connectpx_booking' );
            }
            if ( $profile_data['new_password_2'] == '' ) {
                $response['errors']['new_password_2'][] = __( 'Required', 'connectpx_booking' );
            }
            if ( $profile_data['new_password_1'] != $profile_data['new_password_2'] ) {
                $response['errors']['new_password_2'][] = __( 'Passwords mismatch', 'connectpx_booking' );
            }
            if ( empty( $response['errors'] ) ) {
                wp_set_password( $profile_data['new_password_1'], self::$customer->getWpUserId() );
            }
        }
        
        // If email changed

        if( empty( $response['errors'] ) ) {
            if( self::$customer->getEmail() != $profile_data['email'] && email_exists( $profile_data['email'] ) ) {
                $response['errors']['email'][] = __( 'This email is already registered.', 'connectpx_booking' );
            }
        }

        if ( empty( $response['errors'] ) ) {
            // Save profile

            self::$customer
                ->setFirstName( $profile_data['first_name'] )
                ->setLastName( $profile_data['last_name'] )
                ->setEmail( $profile_data['email'] )
                ->setPhone( $profile_data['phone'] )
                ->setCountry( isset( $profile_data['country'] ) ? $profile_data['country'] : self::$customer->getCountry() )
                ->setState( isset( $profile_data['state'] ) ? $profile_data['state'] : self::$customer->getState() )
                ->setPostcode( isset( $profile_data['postcode'] ) ? $profile_data['postcode'] : self::$customer->getPostcode() )
                ->setCity( isset( $profile_data['city'] ) ? $profile_data['city'] : self::$customer->getCity() )
                ->setStreet( isset( $profile_data['street'] ) ? $profile_data['street'] : self::$customer->getStreet() )
                ->setStreetNumber( isset( $profile_data['street_number'] ) ? $profile_data['street_number'] : self::$customer->getStreetNumber() )
                ->setAdditionalAddress( isset( $profile_data['additional_address'] ) ? $profile_data['additional_address'] : self::$customer->getAdditionalAddress() );
            self::$customer->save();

            $user_data = wp_update_user([
                'ID' => self::$customer->getWpUserId(),
                'first_name' => self::$customer->getFirstName(),
                'last_name' => self::$customer->getLastName(),
                'nickname' => self::$customer->getFullName(),
                'display_name' => self::$customer->getFullName(),
                'user_email' => self::$customer->getEmail(),
            ]);

            if ( is_wp_error( $user_data ) ) {
                $response['errors']['account'][] = $user_data->get_error_message();
                $response['success'] = false;
            }
        } else {
            $response['success'] = false;
        }

        wp_send_json( $response );
    }

    /**
     * Validate profile data
     *
     * @param string $field
     * @param array $profile_data
     * @return array
     */
    private static function _validateProfile( $field, $profile_data )
    {
        $validator = new Lib\Validator();
        switch ( $field ) {
            case 'email':
                $validator->validateEmail( 'email', $profile_data );
                break;
            case 'name':
                $validator->validateName( 'first_name', $profile_data['first_name'] );
                $validator->validateName( 'last_name', $profile_data['last_name'] );
                break;
            case 'phone':
                $validator->validatePhone( 'phone', $profile_data['phone'], Lib\Config::phoneRequired() );
                break;
            case 'address':
                foreach ( ['country', 'state', 'postcode', 'city', 'street', 'street_number', 'additional_address'] as $field_name ) {
                    $validator->validateAddress( $field_name, $profile_data[ $field_name ], true );
                }
                break;
        }

        return $validator->getErrors();
    }

    /**
     * @inheritDoc
     */
    protected static function hasAccess( $action )
    {
        if ( parent::hasAccess( $action ) ) {
            self::$customer = Lib\Entities\Customer::query()->where( 'wp_user_id', get_current_user_id() )->findOne();

            return self::$customer->isLoaded();
        }

        return false;
    }
}