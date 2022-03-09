<?php
namespace ConnectpxBooking\Lib\Notifications\Assets\Base;

use ConnectpxBooking\Lib\Utils;

/**
 * Class Codes
 * @package ConnectpxBooking\Lib\Notifications\Assets\Base
 */
abstract class Codes
{
    /**
     * Do replacements.
     *
     * @param string $text
     * @param string $format
     * @return string
     */
    public function replace( $text, $format = 'text' )
    {
        $codes = $this->getReplaceCodes( $format );

        return Utils\Codes::replace( $text, $codes, false );
    }

    /**
     * Do replacements for SMS.
     * Returns both personal and impersonal text.
     *
     * @param string $text
     * @return array
     */
    public function replaceForSms( $text )
    {
        $codes = $this->getReplaceCodes( 'text' );

        // Impersonal codes.
        $impersonal_codes = $this->_impersonalCodes( $codes );

        return array(
            'personal'   => Utils\Codes::replace( $text, $codes, false ),
            'impersonal' => Utils\Codes::replace( $text, $impersonal_codes, false ),
        );
    }

    /**
     * @param array $codes
     * @return array
     */
    protected function _impersonalCodes( $codes )
    {
        $impersonal_codes = array();

        foreach ( $codes as $name => $code ) {
            if ( is_array( $code ) ) {
                $impersonal_codes[ $name ] = $this->_impersonalCodes( $code );
            } else {
                $count = Utils\SMSCounter::count( (string) $code );
                if ( $count->encoding == Utils\SMSCounter::UTF16 ) {
                    $impersonal_symbol = 'х';   // ascii 245
                } else {
                    $impersonal_symbol = 'x';   // ascii 120
                }
                $impersonal_codes[ $name ] = preg_replace( '/[^\s]/', $impersonal_symbol, $code );
            }
        }

        return $impersonal_codes;
    }

    /**
     * Get replacement codes for given format.
     *
     * @param string $format
     * @return array
     */
    protected function getReplaceCodes( $format )
    {
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

        return array(
            'company_address' => $format == 'html' ? nl2br( get_option( 'connectpx_booking_co_address' ) ) : get_option( 'connectpx_booking_co_address' ),
            'company_logo'    => $company_logo,
            'company_name'    => get_option( 'connectpx_booking_co_name' ),
            'company_phone'   => get_option( 'connectpx_booking_co_phone' ),
            'company_website' => get_option( 'connectpx_booking_co_website' ),
        );
    }
}