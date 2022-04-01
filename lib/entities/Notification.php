<?php
namespace ConnectpxBooking\Lib\Entities;

use ConnectpxBooking\Lib;
use ConnectpxBooking\Lib\Entities\Appointment;
use ConnectpxBooking\Lib\DataHolders\Notification\Settings;

/**
 * Class Notification
 * @package ConnectpxBooking\Lib\Entities
 */
class Notification extends Lib\Base\Entity
{
    const TYPE_APPOINTMENT_REMINDER                          = 'appointment_reminder';
    const TYPE_APPOINTMENT_STATUS_CHANGED                    = 'appointment_status_changed';
    const TYPE_SCHEDULE_CANCELLED                            = 'schedule_cancelled';
    const TYPE_CUSTOMER_NEW_WP_USER                          = 'customer_new_wp_user';
    const TYPE_NEW_BOOKING                                   = 'new_booking';
    const TYPE_NEW_INVOICE                                   = 'new_invoice';

    /** @var array Human readable notification titles */
    public static $titles;
    /** @var array */
    public static $type_ids;
    /** @var array */
    public static $icons;

    /** @var  string */
    protected $gateway = 'email';
    /** @var  string */
    protected $type;
    /** @var  bool */
    protected $active = 0;
    /** @var  string */
    protected $name = '';
    /** @var  string */
    protected $subject = '';
    /** @var  string */
    protected $message = '';
    /** @var  int */
    protected $to_customer = 0;
    /** @var  bool */
    protected $to_admin = 0;
    /** @var  bool */
    protected $to_custom = 0;
    /** @var  string */
    protected $custom_recipients;
    /** @var  bool */
    protected $attach_ics = 0;
    /** @var  bool */
    protected $attach_invoice = 0;
    /** @var  string json */
    protected $settings = '[]';
    /** @var Settings */
    protected $settings_object;

    protected static $table = 'connectpx_booking_notifications';

    protected static $schema = array(
        'id'             => array( 'format' => '%d' ),
        'gateway'        => array( 'format' => '%s' ),
        'type'           => array( 'format' => '%s' ),
        'active'         => array( 'format' => '%d' ),
        'name'           => array( 'format' => '%s' ),
        'subject'        => array( 'format' => '%s' ),
        'message'        => array( 'format' => '%s' ),
        'to_customer'    => array( 'format' => '%d' ),
        'to_admin'       => array( 'format' => '%d' ),
        'to_custom'      => array( 'format' => '%d' ),
        'custom_recipients' => array( 'format' => '%s' ),
        'attach_ics'     => array( 'format' => '%d' ),
        'attach_invoice' => array( 'format' => '%d' ),
        'settings'       => array( 'format' => '%s' ),
    );

    /**************************************************************************
     * Entity Fields Getters & Setters                                        *
     **************************************************************************/

    /**
     * Get type ID.
     *
     * @return int|null
     */
    public function getTypeId()
    {
        self::initTypeIds();

        return isset ( self::$type_ids[ $this->getType() ] )
            ? self::$type_ids[ $this->getType() ]
            : null;
    }

    /**
     * Get type string for given type ID.
     *
     * @param int $type_id
     * @return string|null
     */
    public static function getTypeString( $type_id )
    {
        self::initTypeIds();

        return array_search( $type_id, self::$type_ids ) ?: null;
    }

    /**
     * Notification title.
     *
     * @param $type
     * @return string
     */
    public static function getTitle( $type = null )
    {
        self::initTitles();

        return array_key_exists( $type, self::$titles )
            ? self::$titles[ $type ]
            : __( 'Unknown', 'connectpx_booking' );
    }

    /**
     * Return custom notification codes.
     *
     * @param $gateway
     * @return array
     */
    public static function getTypes( $gateway = 'email' )
    {
        $types = array(
            self::TYPE_NEW_BOOKING,
            self::TYPE_NEW_INVOICE,
            self::TYPE_APPOINTMENT_STATUS_CHANGED,
            self::TYPE_SCHEDULE_CANCELLED,
            self::TYPE_APPOINTMENT_REMINDER,
            self::TYPE_CUSTOMER_NEW_WP_USER
        );

        return $types;
    }

    /**
     * Notification icon.
     *
     * @param $type
     * @return string
     */
    public static function getIcon( $type = null )
    {
        self::initIcons();

        return array_key_exists( $type, self::$icons )
            ? self::$icons[ $type ]
            : 'fa-question';
    }

    /**
     * Fill array with notification titles.
     */
    private static function initTitles()
    {
        if ( self::$titles === null ) {
            self::$titles = array(
                self::TYPE_APPOINTMENT_STATUS_CHANGED => __( 'Notification about customer\'s appointment status change', 'connectpx_booking' ),
                self::TYPE_SCHEDULE_CANCELLED => __( 'Notification about customer\'s schedule is canceled', 'connectpx_booking' ),
                self::TYPE_NEW_BOOKING                         => __( 'New booking notification', 'connectpx_booking' ),
                self::TYPE_NEW_INVOICE                         => __( 'New invoice notification', 'connectpx_booking' ),
                self::TYPE_APPOINTMENT_REMINDER                => __( 'Appointment reminder', 'connectpx_booking' ),
                self::TYPE_CUSTOMER_NEW_WP_USER                => __( 'Customer account information', 'connectpx_booking' ),
                /** @see \ConnectpxBooking\Backend\Modules\CloudSms\Ajax::sendTestSms */
                'test_message'                                 => __( 'Test message', 'connectpx_booking' ),
            );
        }
    }

    /**
     * Fill array of type ids.
     */
    private static function initTypeIds()
    {
        if ( self::$type_ids === null ) {
            self::$type_ids = array(
                /** @see \ConnectpxBooking\Backend\Modules\CloudSms\Ajax::sendTestSms */
                'test_message'                                 => 0,
                self::TYPE_CUSTOMER_NEW_WP_USER                => 5,
                self::TYPE_APPOINTMENT_REMINDER                => 19,
                self::TYPE_APPOINTMENT_STATUS_CHANGED          => 21,
                self::TYPE_SCHEDULE_CANCELLED                  => 24,
                self::TYPE_NEW_BOOKING                         => 22,
                self::TYPE_NEW_INVOICE                         => 23,
            );
        }
    }

    /**
     * Fill array of icons.
     */
    private static function initIcons()
    {
        if ( self::$icons === null ) {
            self::$icons = array(
                self::TYPE_NEW_BOOKING                                   => 'far fa-calendar-check',
                self::TYPE_NEW_INVOICE                                   => 'far fa-file',
                self::TYPE_APPOINTMENT_STATUS_CHANGED                    => 'fas fa-arrows-alt-h',
                self::TYPE_SCHEDULE_CANCELLED                            => 'fas fa-arrows-alt-h',
                self::TYPE_CUSTOMER_NEW_WP_USER                          => 'fas fa-user-plus',
                self::TYPE_APPOINTMENT_REMINDER                          => 'far fa-bell',
            );
        }
    }

    /**
     * Return unique name for WPML
     *
     * @return string
     */
    private function getWpmlName()
    {
        return sprintf( '%s_%s_%d', $this->getGateway(), $this->getType(), $this->getId() );
    }

    /**
     * Get Settings object.
     *
     * @return Settings
     */
    public function getSettingsObject()
    {
        if ( $this->settings_object === null ) {
            $this->settings_object = new Settings( $this );
        }

        return $this->settings_object;
    }

    /**
     * Check whether notification settings match given order item.
     *
     * @param Appointment $appointment
     * @return bool
     */
    public function matchesAppointmentForClient( Appointment $appointment )
    {
        return $this->getSettingsObject()->allowedServiceWithStatus( $appointment->getService(), $appointment->getStatus() );
    }

    /**
     * Check whether notification settings match given order item for staff.
     *
     * @param Appointment    $appointment
     * @param Service $parent
     * @return bool
     */
    public function matchesAppointmentForAdmin( Appointment $appointment, $parent )
    {
        return $this->getSettingsObject()->allowedServiceWithStatus( $appointment->getService(), $appointment->getStatus(), $parent );
    }

    /**************************************************************************
     * Entity Fields Getters & Setters                                        *
     **************************************************************************/

    /**
     * Gets gateway
     *
     * @return string
     */
    public function getGateway()
    {
        return $this->gateway;
    }

    /**
     * Sets gateway
     *
     * @param string $gateway
     * @return $this
     */
    public function setGateway( $gateway )
    {
        $this->gateway = $gateway;

        return $this;
    }

    /**
     * Gets type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets type
     *
     * @param string $type
     * @return $this
     */
    public function setType( $type )
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Gets active
     *
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Sets active
     *
     * @param bool $active
     * @return $this
     */
    public function setActive( $active )
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Gets to admin
     *
     * @return bool
     */
    public function getToAdmin()
    {
        return $this->to_admin;
    }

    /**
     * Sets to admin
     *
     * @param bool $to_admin
     * @return $this
     */
    public function setToAdmin( $to_admin )
    {
        $this->to_admin = $to_admin;

        return $this;
    }

    /**
     * Gets to_custom
     *
     * @return bool
     */
    public function getToCustom()
    {
        return $this->to_custom;
    }

    /**
     * Sets to_custom
     *
     * @param bool $to_custom
     * @return $this
     */
    public function setToCustom( $to_custom )
    {
        $this->to_custom = $to_custom;

        return $this;
    }

    /**
     * Gets custom_recipients
     *
     * @return string
     */
    public function getCustomRecipients()
    {
        return $this->custom_recipients;
    }

    /**
     * Sets custom_recipients
     *
     * @param string $custom_recipients
     * @return $this
     */
    public function setCustomRecipients( $custom_recipients )
    {
        $this->custom_recipients = $custom_recipients;

        return $this;
    }

    /**
     * Gets name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets name
     *
     * @param string $name
     * @return $this
     */
    public function setName( $name )
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Sets subject
     *
     * @param string $subject
     * @return $this
     */
    public function setSubject( $subject )
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Gets message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Sets message
     *
     * @param string $message
     * @return $this
     */
    public function setMessage( $message )
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Gets to_customer
     *
     * @return int
     */
    public function getToCustomer()
    {
        return $this->to_customer;
    }

    /**
     * Sets to_customer
     *
     * @param int $to_customer
     * @return $this
     */
    public function setToCustomer( $to_customer )
    {
        $this->to_customer = $to_customer;

        return $this;
    }

    /**
     * Gets attach_ics
     *
     * @return bool
     */
    public function getAttachIcs()
    {
        return $this->attach_ics;
    }

    /**
     * Sets attach_ics
     *
     * @param bool $attach_ics
     * @return $this
     */
    public function setAttachIcs( $attach_ics )
    {
        $this->attach_ics = $attach_ics;

        return $this;
    }

    /**
     * Gets attach_invoice
     *
     * @return bool
     */
    public function getAttachInvoice()
    {
        return $this->attach_invoice;
    }

    /**
     * Sets attach_invoice
     *
     * @param bool $attach_invoice
     * @return $this
     */
    public function setAttachInvoice( $attach_invoice )
    {
        $this->attach_invoice = $attach_invoice;

        return $this;
    }

    /**
     * Gets settings
     *
     * @return string
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Sets settings
     *
     * @param string $settings
     * @return $this
     */
    public function setSettings( $settings )
    {
        $this->settings = $settings;

        return $this;
    }

    /**************************************************************************
     * Overridden Methods                                                     *
     **************************************************************************/

    /**
     * Save entity.
     *
     * @return false|int
     */
    public function save()
    {
        if ( is_array( $this->settings ) ) {
            $this->settings = json_encode( $this->settings );
        }

        $return = parent::save();

        return $return;
    }

}