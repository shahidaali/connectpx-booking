<?php
namespace ConnectpxBooking\Lib\Notifications\Assets\Appointment;

use ConnectpxBooking\Lib;
use ConnectpxBooking\Lib\Entities\Notification;
use ConnectpxBooking\Lib\Notifications\Assets\Base;

/**
 * Class Attachments
 * @package ConnectpxBooking\Lib\Notifications\Assets\Appointment
 *
 * @property Codes $codes
 */
class Attachments extends Base\Attachments
{
    /** @var Codes */
    protected $codes;
    
    /**
     * Constructor.
     *
     * @param Codes $codes
     */
    public function __construct( Codes $codes )
    {
        $this->codes = $codes;
    }

    /**
     * @inheritDoc
     */
    public function createFor( Notification $notification )
    {
        $result = array();

        if ( $notification->getAttachIcs() ) {
            if ( ! isset( $this->files['ics'] ) ) {
                // ICS.
                $ics = new ICS( $this->codes );
                $file = $ics->create();
                if ( $file ) {
                    $this->files['ics'] = $file;
                }
            }
            $result = isset( $this->files['ics'] ) ? array( $this->files['ics'] ) : array();
        }

        return $result;
    }
}