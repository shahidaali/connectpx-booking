<?php
namespace ConnectpxBooking\Lib\Notifications\Assets\Base;

use ConnectpxBooking\Lib\Entities\Notification;
use ConnectpxBooking\Lib\Utils\Common;

/**
 * Class Attachments
 * @package ConnectpxBooking\Lib\Notifications\Assets\Base
 */
abstract class Attachments
{
    /** @var array */
    protected $files = array();

    /**
     * Create attachment files.
     *
     * @param Notification $notification
     * @return array
     */
    abstract public function createFor( Notification $notification );

    /**
     * Remove attachment files.
     */
    public function clear()
    {
        $fs = Common::getFilesystem();
        foreach ( $this->files as $file ) {
            $fs->delete( $file, false, 'f' );
        }

        $this->files = array();
    }
}