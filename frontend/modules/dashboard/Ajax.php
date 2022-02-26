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

        $query = Lib\Entities\Appointment::query( 'a' )
            ->select( 'a.service_id,
                    a.id,
                    a.status,
                    a.time_zone,
                    a.time_zone_offset,
                    a.total_amount,
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

        foreach ( $order as $sort_by ) {
            $query->sortBy( str_replace( '.', '_', $columns[ $sort_by['column'] ]['data'] ) )
                ->order( $sort_by['dir'] == 'desc' ? Lib\Query::ORDER_DESCENDING : Lib\Query::ORDER_ASCENDING );
        }

        $total = $query->count( true );
        $query->limit( self::parameter( 'length' ) )->offset( self::parameter( 'start' ) );

        $data = array();
        foreach ( $query->fetchArray() as $row ) {
            // Appointment status.
            $row['appointment_status_text'] = Lib\Entities\Appointment::statusToString( $row['status'] );
            $service_title = Lib\Entities\Service::find( $row['service_id'] )->getTitle();

            $allow_cancel_time = current_time( 'timestamp' ) + (int) Lib\Config::getMinimumTimePriorCancel( $row['service_id'] );

            $allow_cancel = 'blank';
            if ( ! in_array( $row['status'], array(
                Lib\Entities\Appointment::STATUS_CANCELLED,
                Lib\Entities\Appointment::STATUS_REJECTED,
                Lib\Entities\Appointment::STATUS_DONE,
            ) ) ) {
                if ( in_array( $row['status'], array(
                        Lib\Entities\Appointment::STATUS_APPROVED,
                        Lib\Entities\Appointment::STATUS_PENDING,
                    )  ) && $row['pickup_datetime'] === null ) {
                    $allow_cancel = 'allow';
                } else {
                    if ( $row['pickup_datetime'] > current_time( 'mysql' ) ) {
                        if ( $allow_cancel_time < strtotime( $row['pickup_datetime'] ) ) {
                            $allow_cancel = 'allow';
                        } else {
                            $allow_cancel = 'deny';
                        }
                    } else {
                        $allow_cancel = 'expired';
                    }
                }
            }
            $allow_reschedule = 'blank';
            if ( ! in_array( $row['status'], array(
                    Lib\Entities\Appointment::STATUS_CANCELLED,
                    Lib\Entities\Appointment::STATUS_REJECTED,
                    Lib\Entities\Appointment::STATUS_DONE,
                ) ) && $row['pickup_datetime'] !== null ) {
                if ( $row['pickup_datetime'] > current_time( 'mysql' ) ) {
                    if ( $allow_cancel_time < strtotime( $row['pickup_datetime'] )  ) {
                        $allow_reschedule = 'allow';
                    } else {
                        $allow_reschedule = 'deny';
                    }
                } else {
                    $allow_reschedule = 'expired';
                }
            }
            $total_amount = Lib\Utils\Price::format( $row['total_amount'] );

            $data[] = array(
                'id' => $row['id'],
                'date' => strtotime( $row['pickup_datetime'] ),
                'raw_start_date' => $row['pickup_datetime'],
                'pickup_datetime' => ( ( in_array( 'timezone', $appointment_columns ) && $timezone = Lib\Utils\Common::getCustomerTimezone( $row['time_zone'], $row['time_zone_offset'] ) ) ? sprintf( '%s<br/>(%s)', Lib\Utils\DateTime::formatDateTime( $row['pickup_datetime'] ), $timezone ) : Lib\Utils\DateTime::formatDateTime( $row['pickup_datetime'] ) ),
                'service_title' => $service_title,
                'status' => $row['appointment_status_text'],
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