<?php
namespace ConnectpxBooking\Lib\DataHolders\Notification;

use ConnectpxBooking\Lib\Entities\Notification;
use ConnectpxBooking\Lib\Entities\Service;

/**
 * Class Settings
 * @package ConnectpxBooking\Lib\DataHolders\Notification
 */
class Settings
{
    /** @var array */
    protected $settings;
    /** @var int */
    protected $offset_hours = 0;
    /** @var int */
    protected $at_hour;
    /** @var string  @see CustomerAppointment::STATUS_* or any */
    protected $status = 'any';
    /** @var bool */
    protected $instant = 0;
    /** @var mixed value 'any' or an array of service_ids */
    protected $services  = 'any';

    /**
     * Condition constructor.
     *
     * @param Notification $notification
     */
    public function __construct( Notification $notification )
    {
        $this->settings = (array) json_decode( $notification->getSettings(), true );
        $this->prepare( $notification->getType() );
    }

    /**
     * @param string $type
     */
    private function prepare( $type )
    {
        switch ( $type ) {
            case Notification::TYPE_CUSTOMER_NEW_WP_USER:
                $this->instant = 1;
                break;
            case Notification::TYPE_NEW_INVOICE:
                $this->instant = 1;
                break;
            case Notification::TYPE_SCHEDULE_CANCELLED:
                $this->instant = 1;
                break;
            case Notification::TYPE_APPOINTMENT_STATUS_CHANGED:
            case Notification::TYPE_SCHEDULE_STATUS_CHANGED:
            case Notification::TYPE_NEW_BOOKING:
                $this->status   = $this->settings['status'];
                $this->instant  = 1;
                break;
            case Notification::TYPE_APPOINTMENT_REMINDER:
                $this->status   = $this->settings['status'];
                break;
        }
    }

    /**
     * @return int
     */
    public function getOffsetHours()
    {
        return (int) $this->offset_hours;
    }

    /**
     * @return string|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Gets at_hour
     *
     * @return int
     */
    public function getSendAtHour()
    {
        return (int) $this->at_hour;
    }

    /**
     * Gets at_hour
     *
     * @return int|null
     */
    public function getAtHour()
    {
        return $this->at_hour;
    }

    /**
     * Gets instant
     *
     * @return bool
     */
    public function getInstant()
    {
        return $this->instant;
    }

    /**
     * @return string|array
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * Default notification settings.
     * @return array
     */
    public static function getDefault()
    {
        return array(
            'status'   => 'any',
            'option'   => 2,
            'services' => array(
                'any' => 'any',
                'ids' => array(),
            ),
            'offset_hours'   => 2,
            'perform'        => 'before',
            'at_hour'        => 9,
            'before_at_hour' => 18,
            'offset_before_hours' => -24,
            'offset_bidirectional_hours' => 0,
        );
    }

    /**
     * @param Service $service
     * @param string  $status customer appointment status
     * @param Service $parent if set send staff notification for non simple service.
     * @return bool
     */
    public function allowedServiceWithStatus( Service $service, $status, $parent = null )
    {
        if ( in_array( $this->getStatus(), array( 'any', $status ) ) ) {
            if ( $this->services == 'any' ) {
                return true;
            } elseif ( $parent ) {
                return in_array( $service->getId(), isset( $this->services[ $parent->getType() ][ $parent->getId() ] ) ? $this->services[ $parent->getType() ][ $parent->getId() ] : array() );
            } else {
                return array_key_exists( $service->getId(), $this->services[ $service->getType() ] );
            }
        }

        return false;
    }
}