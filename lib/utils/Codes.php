<?php
namespace ConnectpxBooking\Lib\Utils;

use ConnectpxBooking\Lib;
use ConnectpxBooking\Lib\Entities;

/**
 * Class Codes
 * @package ConnectpxBooking\Lib\Utils
 */
abstract class Codes
{
    protected static $tokens = array(
        'T_CODE' => '{(\w+(?:\.\w+)*(?:\#\w+)*)}',
        'T_IF' => '{#if\s+(\w+(?:\.\w+)*(?:\#\w+)*)\s*(?:(==|!=|>=|>|<=|<|=|!empty|empty)\s*(.+?))?}\n?',
        'T_END_IF' => '{\/if}\n?',
        'T_EACH' => '{#each\s+(\w+(?:\.\w+)*)\s+as\s+(\w+)(?:\s+delimited\s+by\s+"(.+?)")?\s*}\n?',
        'T_END_EACH' => '{\/each}\n?',
    );

    /**
     * Replace codes in text
     *
     * @param string $text
     * @param array $codes
     * @param false|array $bold array of excludes
     * @return string
     */
    public static function replace( $text, $codes, $bold = true )
    {
        return self::stringify( self::tokenize( $text ), $codes, $bold );
    }

    /**
     * Build string from tokens and codes data
     *
     * @param array $tokens
     * @param array $codes
     * @param bool $bold
     * @return string
     */
    protected static function stringify( $tokens, $codes, $bold )
    {
        $output = '';

        foreach ( $tokens as $token ) {
            switch ( $token[0] ) {
                case 'T_TEXT':
                    $output .= $token[1];
                    break;
                case 'T_CODE':
                    $data = self::get( $token[1], $codes );
                    if ( $data !== null ) {
                        if ( $bold !== false && ! in_array( $token[1], $bold ) ) {
                            $output .= '<b>' . $data . '</b>';
                        } else {
                            $output .= $data;
                        }
                    }
                    break;
                case 'T_IF':
                    $data = self::get( $token[1], $codes );
                    $nested_tokens = $token[3];
                    $if = false;
                    switch ( $token[2]['operator'] ) {
                        case '==' :
                        case '=' :
                            if ( $data == $token[2]['operand'] ) {
                                $if = true;
                            }
                            break;
                        case '!=' :
                            if ( $data != $token[2]['operand'] ) {
                                $if = true;
                            }
                            break;
                        case '>' :
                            if ( $data > $token[2]['operand'] ) {
                                $if = true;
                            }
                            break;
                        case '>=' :
                            if ( $data >= $token[2]['operand'] ) {
                                $if = true;
                            }
                            break;
                        case '<' :
                            if ( $data < $token[2]['operand'] ) {
                                $if = true;
                            }
                            break;
                        case '<=' :
                            if ( $data <= $token[2]['operand'] ) {
                                $if = true;
                            }
                            break;
                        case 'empty' :
                            if ( empty( $data ) ) {
                                $if = true;
                            }
                            break;
                        default :
                            if ( !empty( $data ) ) {
                                $if = true;
                            }
                            break;
                    }
                    if ( $if ) {
                        $output .= self::stringify( $nested_tokens, $codes, $bold );
                    }
                    break;
                case 'T_EACH':
                    $data = self::get( $token[1], $codes );
                    $context_code = $token[2];
                    $delimiter = $token[3];
                    $nested_tokens = $token[4];
                    if ( is_array( $data ) ) {
                        $parts = array();
                        foreach ( $data as $context_codes ) {
                            $parts[] = self::stringify( $nested_tokens, array( $context_code => $context_codes ) + $codes, $bold );
                        }
                        $output .= implode( $delimiter, $parts );
                    }
                    break;
                default:
                    // Do nothing
            }
        }

        return $output;
    }

    /**
     * Split text into array of tokens
     *
     * @param string $text
     * @param int $offset
     * @param string $stop_token
     * @return array
     */
    protected static function tokenize( $text, &$offset = 0, $stop_token = null )
    {
        $tokens = array();
        $text_start = null;

        while ( isset ( $text[ $offset ] ) ) {
            if ( $type = self::match( $text, $matches, $offset ) ) {
                if ( $text_start !== null ) {
                    // Raw text ended
                    $tokens[] = array( 'T_TEXT', substr( $text, $text_start, $offset - $text_start ) );
                    $text_start = null;
                }

                $offset += strlen( $matches[0] );

                if ( $type == $stop_token ) {
                    break;
                }

                $token = array( $type );

                if ( $type == 'T_CODE' ) {
                    $token[] = $matches[1];
                } elseif ( $type == 'T_IF' ) {
                    $token[] = $matches[1];
                    $token[] = array(
                        'operator' => isset( $matches[2] ) ? $matches[2] : null,
                        'operand' => isset( $matches[3] ) ? $matches[3] : null,
                    );
                    $token[] = self::tokenize( $text, $offset, 'T_END_IF' );
                } elseif ( $type == 'T_EACH' ) {
                    $token[] = $matches[1]; // code
                    $token[] = $matches[2]; // context code
                    $token[] = isset ( $matches[3] ) ? $matches[3] : ''; // delimiter
                    $token[] = self::tokenize( $text, $offset, 'T_END_EACH' );
                }

                $tokens[] = $token;
            } else {
                if ( $text_start === null ) {
                    // Raw text started
                    $text_start = $offset;
                }
                ++ $offset;
            }
        }
        if ( $text_start !== null ) {
            // Raw text ended
            $tokens[] = array( 'T_TEXT', substr( $text, $text_start ) );
        }

        return $tokens;
    }

    /**
     * Match string with tokens
     *
     * @param string $subject
     * @param array &$matches
     * @param int $offset
     * @return false|string
     */
    protected static function match( $subject, &$matches, $offset )
    {
        foreach ( self::$tokens as $type => $pattern ) {
            if ( preg_match( "/$pattern/A", $subject, $matches, null, $offset ) ) {

                return $type;
            }
        }

        return false;
    }

    /**
     * Get dot-notated path from array
     *
     * @param string $path
     * @param array $array
     * @return mixed|null
     */
    protected static function get( $path, $array )
    {
        $result = $array;
        foreach ( explode( '.', $path ) as $key ) {
            if ( isset ( $result[ $key ] ) ) {
                $result = $result[ $key ];
            } else {
                return null;
            }
        }

        return $result;
    }

    /**
     * Generate HTML for codes table
     *
     * @param array $codes
     * @return string
     */
    public static function tableHtml( array $codes )
    {
        $tbody = '';
        foreach ( $codes as $code => $description ) {
            $tbody .= sprintf(
                '<tr><td class="p-0"><input value="{%s}" class="border-0 connectpx_booking-outline-0" readonly="readonly" onclick="this.select()" /> &ndash; %s</td></tr>',
                $code,
                esc_html( $description )
            );
        }

        return '<table><tbody>' . $tbody . '</tbody></table>';
    }

    /**************************************************************************
     * Codes for entities                                                     *
     **************************************************************************/

    /**
     * Get codes for Appointment entity
     *
     * @param Entities\Appointment $appointment
     * @param string $format
     * @return array
     */
    public static function getAppointmentCodes( Entities\Appointment $appointment, $format = 'text' )
    {
        $service = Entities\Service::find( $appointment->getServiceId() );

        $timezone = Lib\Config::getWPTimeZone();
        $appointment_pickup_dt = $appointment->getPickupDateTime() ? Lib\Utils\DateTime::convertTimeZone( $appointment->getPickupDateTime(), Lib\Config::getWPTimeZone(), $timezone ) : null;
        $appointment_return_pickup_dt = $appointment->getReturnPickupDateTime() ? Lib\Utils\DateTime::convertTimeZone( $appointment->getReturnPickupDateTime(), Lib\Config::getWPTimeZone(), $timezone ) : null;
        $service_name = $service->getTitle();

        $company_logo = '';
        if ( $format == 'html' ) {
            $img = wp_get_attachment_image_src( get_option( 'connectpx_booking_co_logo_attachment_id' ), 'full' );
            // Company logo as <img> tag.
            if ( $img ) {
                $company_logo = sprintf(
                    '<img src="%s" alt="%s" />',
                    esc_attr( $img[0] ),
                    esc_attr( get_option( 'connectpx_booking_co_name' ) )
                );
            }
        }

        $codes = array(
            'signed_up' => 0,
            'number_of_persons' => 0,
            'participants' => array(),
            'appointment_date' => $appointment_pickup_dt === null ? __( 'N/A', 'connectpx_booking' ) : Lib\Utils\DateTime::formatDate( $appointment_pickup_dt ),
            'appointment_pickup_time' => $appointment_pickup_dt === null ? __( 'N/A', 'connectpx_booking' ) : $appointment_pickup_dt->formatI18nTime(),
            'appointment_return_pickup_time' => $appointment_return_pickup_dt === null ? __( 'N/A', 'connectpx_booking' ) : $appointment_return_pickup_dt->formatI18nTime(),
            'booking_number' => $appointment->getId(),
            'company_address' => $format == 'html' ? nl2br( get_option( 'connectpx_booking_co_address' ) ) : get_option( 'connectpx_booking_co_address' ),
            'company_logo'    => $company_logo,
            'company_name'    => get_option( 'connectpx_booking_co_name' ),
            'company_phone'   => get_option( 'connectpx_booking_co_phone' ),
            'company_website' => get_option( 'connectpx_booking_co_website' ),
            'service_info' => $format == 'html' ? nl2br( $service->getInfo() ) : $service->getInfo(),
            'service_name' => $service_name,
            'service_price' => Lib\Utils\Price::format( $service->getPrice() ),
            'internal_note' => $appointment->getInternalNote(),
        );
        
        $appointment_notes = array();
        $client_names = array();
        foreach ( $appointment->getCustomerAppointments( true ) as $customer_appointment ) {
            $codes['participants'][] = self::getCustomerAppointmentCodes( $customer_appointment );
            $codes['signed_up'] += $customer_appointment->getNumberOfPersons();
            $codes['number_of_persons'] += $customer_appointment->getNumberOfPersons();
            $appointment_notes[] = $customer_appointment->getNotes();
            $client_names[] = $customer_appointment->customer->getFullName();
        }
        $codes['client_names'] = implode( ', ', $client_names );
        $codes['appointment_notes'] = implode( ', ', $appointment_notes );

        return Lib\Proxy\Shared::prepareAppointmentCodes( $codes, $appointment );
    }

    /**
     * Get codes for CustomerAppointment entity
     *
     * @param Entities\CustomerAppointment $customer_appointment
     * @param string $format
     * @return array
     */
    public static function getCustomerAppointmentCodes( Entities\CustomerAppointment $customer_appointment, $format = 'text' )
    {
        $customer = $customer_appointment->customer;
        $payment = $customer_appointment->getPaymentId() ? Entities\Payment::find( $customer_appointment->getPaymentId() ) : null;
        $cancel_appointment_url = admin_url( 'admin-ajax.php?action=connectpx_booking_cancel_appointment&token=' . $customer_appointment->getToken() );

        $codes = array(
            'appointment_notes' => $customer_appointment->getNotes(),
            'client_name' => $customer->getFullName(),
            'client_note' => $customer->getNotes(),
            'client_first_name' => $customer->getFirstName(),
            'client_last_name' => $customer->getLastName(),
            'client_phone' => $customer->getPhone(),
            'client_email' => $customer->getEmail(),
            'payment_status' => $payment ? Lib\Entities\Payment::statusToString( $payment->getStatus() ) : '',
            'payment_type' => $payment ? Lib\Entities\Payment::typeToString( $payment->getType() ) : '',
            'number_of_persons' => $customer_appointment->getNumberOfPersons(),
            'total_price' => $payment ? Lib\Utils\Price::format( $payment->getTotal() ) : '',
            'amount_paid' => $payment ? Lib\Utils\Price::format( $payment->getPaid() ) : '',
            'amount_due' => $payment ? Lib\Utils\Price::format( $payment->getTotal() - $payment->getPaid() ) : '',
            'status' => $customer_appointment->getStatus(),
            'cancel_appointment' => sprintf( '<a href="%s" target="_blank">%s</a>', $cancel_appointment_url, __( 'Cancel Appointment', 'connectpx_booking' ) ),
            'cancel_appointment_url' => $cancel_appointment_url,
        );

        return Lib\Proxy\Shared::prepareCustomerAppointmentCodes( $codes, $customer_appointment, $format );
    }
}