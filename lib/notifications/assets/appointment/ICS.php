<?php
namespace ConnectpxBooking\Lib\Notifications\Assets\Appointment;

use ConnectpxBooking\Lib;

/**
 * Class ICS
 * @package ConnectpxBooking\Lib\Notifications\Assets\Appointment
 */
class ICS
{
    protected $data;

    /**
     * Constructor.
     *
     * @param Codes $codes
     */
    public function __construct( Codes $codes )
    {
        $this->data = sprintf(
            "BEGIN:VCALENDAR\n"
            . "VERSION:2.0\n"
            . "PRODID:-//hacksw/handcal//NONSGML v1.0//EN\n"
            . "CALSCALE:GREGORIAN\n"
            . "BEGIN:VEVENT\n"
            . "DTSTART:%s\n"
            . "DTEND:%s\n"
            . "SUMMARY:%s\n"
            . "DESCRIPTION:%s\n"
            . "LOCATION:%s\n"
            . "END:VEVENT\n"
            . "END:VCALENDAR",
            $this->_formatDateTime( $codes->appointment_start ),
            $this->_formatDateTime( $codes->appointment_end ),
            $this->_escape( $codes->service_name ),
            $this->_escape( sprintf( "%s\n%s", $codes->service_name, $codes->staff_name ) ),
            $this->_escape( sprintf( "%s", $codes->location_name ) )
        );
    }

    /**
     * Create ICS file.
     *
     * @return bool|string
     */
    public function create()
    {
        $path = tempnam( get_temp_dir(), 'ConnectpxBooking_' );

        if ( $path ) {
            $info = pathinfo( $path );
            $new_path = sprintf( '%s%s%s.ics', $info['dirname'], DIRECTORY_SEPARATOR, $info['filename'] );
            if ( rename( $path, $new_path ) ) {
                $path = $new_path;
            } else {
                $new_path = sprintf( '%s%s%s.ics', $info['dirname'], DIRECTORY_SEPARATOR, $info['basename'] );
                if ( rename( $path, $new_path ) ) {
                    $path = $new_path;
                }
            }
            Lib\Utils\Common::getFilesystem()->put_contents( $path, $this->data );

            return $path;
        }

        return false;
    }

    /**
     * Format date and time.
     *
     * @param string $datetime
     * @return string
     */
    protected function _formatDateTime( $datetime )
    {
        return date_create( $datetime )->format( 'Ymd\THis' );
    }

    /**
     * Escape string.
     *
     * @param string $input
     * @return string
     */
    protected function _escape( $input )
    {
        $input = preg_replace( '/([\,;])/','\\\$1', $input );
        $input = str_replace( "\n", "\\n", $input );
        $input = str_replace( "\r", "\\r", $input );

        return $input;
    }
}