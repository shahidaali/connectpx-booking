<?php
namespace ConnectpxBooking\Lib\Utils;

use ConnectpxBooking\Lib;

/**
 * Class Tables
 *
 * @package ConnectpxBooking\Lib\Utils
 */
abstract class Tables
{
    const APPOINTMENTS = 'appointments';
    const CUSTOMERS = 'customers';
    const EMAIL_NOTIFICATIONS = 'email_notifications';
    const SERVICES = 'services';

    /**
     * Get columns for given table.
     *
     * @param string $table
     * @return array
     */
    public static function getColumns( $table )
    {
        $columns = array();
        switch ( $table ) {
            case self::APPOINTMENTS:
                $columns = array(
                    'id' => esc_html__( 'No.', 'connectpx_booking' ),
                    'pickup_datetime' => esc_html__( 'Appointment date', 'connectpx_booking' ),
                    'customer_full_name' => esc_html__( 'Customer name', 'connectpx_booking' ),
                    'customer_phone' => esc_html__( 'Customer phone', 'connectpx_booking' ),
                    'customer_email' => esc_html__( 'Customer email', 'connectpx_booking' ),
                    'service_title' => esc_html__( 'Service', 'connectpx_booking' ),
                    'status' => esc_html__( 'Status', 'connectpx_booking' ),
                    'payment' => esc_html__( 'Payment', 'connectpx_booking' ),
                    'notes' => esc_html__( 'Notes', 'connectpx_booking' ),
                    'created_date' => esc_html__( 'Created', 'connectpx_booking' ),
                );
                break;
            case self::CUSTOMERS:
                $columns = array(
                    'id' => esc_html__( 'ID', 'connectpx_booking' ),
                    'full_name' => esc_html( Common::getTranslatedOption( 'connectpx_booking_l10n_label_name' ) ),
                    'first_name' => esc_html( Common::getTranslatedOption( 'connectpx_booking_l10n_label_first_name' ) ),
                    'last_name' => esc_html( Common::getTranslatedOption( 'connectpx_booking_l10n_label_last_name' ) ),
                    'wp_user' => esc_html__( 'User', 'connectpx_booking' ),
                    'phone' => esc_html( Common::getTranslatedOption( 'connectpx_booking_l10n_label_phone' ) ),
                    'email' => esc_html( Common::getTranslatedOption( 'connectpx_booking_l10n_label_email' ) ),
                    'notes' => esc_html__( 'Notes', 'connectpx_booking' ),
                    'last_appointment' => esc_html__( 'Last appointment', 'connectpx_booking' ),
                    'total_appointments' => esc_html__( 'Total appointments', 'connectpx_booking' ),
                    'payments' => esc_html__( 'Payments', 'connectpx_booking' ),
                    'birthday' => esc_html__( 'Birthday', 'connectpx_booking' ),
                );
                break;
            case self::EMAIL_NOTIFICATIONS:
                $columns = array(
                    'id' => esc_html__( 'ID', 'connectpx_booking' ),
                    'type' => esc_html__( 'Type', 'connectpx_booking' ),
                    'name' => esc_html__( 'Name', 'connectpx_booking' ),
                    'active' => esc_html__( 'State', 'connectpx_booking' ),
                );
                break;
            case self::SERVICES:
                $columns = array(
                    'id' => esc_html__( 'ID', 'connectpx_booking' ),
                    'title' => esc_html__( 'Title', 'connectpx_booking' ),
                    'category_name' => esc_html__( 'Category', 'connectpx_booking' ),
                    'duration' => esc_html__( 'Duration', 'connectpx_booking' ),
                    'price' => esc_html__( 'Price', 'connectpx_booking' ),
                );
                break;
        }

        return $columns;
    }

    /**
     * Get table settings.
     *
     * @param string|array $tables
     * @return array
     */
    public static function getSettings( $tables )
    {
        if ( ! is_array( $tables ) ) {
            $tables = array( $tables );
        }
        $result = array();
        foreach ( $tables as $table ) {
            $columns = self::getColumns( $table );
            $meta = get_user_meta( get_current_user_id(), 'connectpx_booking_' . $table . '_table_settings', true );
            $defaults = self::getDefaultSettings( $table );

            $exist = true;
            if ( ! $meta ) {
                $exist = false;
                $meta = array();
            }

            if ( ! isset ( $meta['columns'] ) ) {
                $meta['columns'] = array();
            }

            // Remove columns with no title.
            foreach ( $meta['columns'] as $key => $column ) {
                if ( ! isset( $columns[ $key ] ) ) {
                    unset( $meta['columns'][ $key ] );
                }
            }
            // New columns, which not saved at meta
            // show/hide if default settings exist and show without default settings
            foreach ( $columns as $column => $title ) {
                if ( ! isset ( $meta['columns'][ $column ] ) ) {
                    $meta['columns'][ $column ] = array_key_exists( $column, $defaults )
                        ? $defaults[ $column ]
                        : true;
                }
            }

            $result[ $table ] = array(
                'settings' => array(
                    'columns' => $meta['columns'],
                    'filter' => isset ( $meta['filter'] ) ? $meta['filter'] : array(),
                    'order' => isset ( $meta['order'] ) ? $meta['order'] : array(),
                ),
                'titles' => $columns,
                'exist' => $exist,
            );
        }

        return $result;
    }

    /**
     * Update table settings.
     *
     * @param string $table
     * @param array $columns
     * @param array $order
     * @param array $filter
     */
    public static function updateSettings( $table, $columns, $order, $filter )
    {
        $meta = get_user_meta( get_current_user_id(), 'connectpx_booking_' . $table . '_table_settings', true ) ?: array();
        if ( $columns !== null && $order !== null ) {
            $order_columns = array();
            foreach ( $order as $sort_by ) {
                if ( isset( $columns[ $sort_by['column'] ] ) ) {
                    $order_columns[] = array(
                        'column' => $columns[ $sort_by['column'] ]['data'],
                        'order' => $sort_by['dir'],
                    );
                }
            }
            $meta['order'] = $order_columns;
        }

        $meta['filter'] = $filter;

        update_user_meta( get_current_user_id(), 'connectpx_booking_' . $table . '_table_settings', $meta );
    }

    /**
     * Get default settings for hide/show table columns
     *
     * @param string $table
     * @return array
     */
    private static function getDefaultSettings( $table )
    {
        $columns = array();
        switch ( $table ) {
            case self::CUSTOMERS:
                $columns = array( 'id' => false, );
                break;
            case self::APPOINTMENTS:
                $columns = array( 'internal_note' => false, );
                break;
            case self::EMAIL_LOGS:
            case self::EMAIL_NOTIFICATIONS:
            case self::SERVICES:
                $columns = array( 'id' => false, );
                break;
        }

        return $columns;
    }
}