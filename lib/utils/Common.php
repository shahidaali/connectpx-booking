<?php
namespace ConnectpxBooking\Lib\Utils;

use ConnectpxBooking\Lib;
use ConnectpxBooking\Lib\Entities\Customer;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    ConnectpxBooking
 * @subpackage ConnectpxBooking/includes
 * @author     Shahid Hussain <shahidhussainaali@gmail.com>
 */
abstract class Common {

	/** @var string CSRF token */
    private static $csrf;

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Optimalsort_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected static $options = [];


	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public static function resetOptions($options = []) {
		$options = $options ? $options : get_option('connectpx_booking_options', true);
		if(empty($options) || !is_array($options)) {
			$options = [];
		}

		self::$options = $options;
		return self::$options;
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public static function getOptions() {
		if( empty(self::$options) ) {
			self::resetOptions();
		}

		return self::$options;
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public static function getOption($name, $default = null) {
		$options = self::getOptions();
		return self::getData($options, $name, $default);
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public static function getOptionMediaSource($name, $size = 'full', $use_placeholder = true) {
		$attachment_id = self::getOption( $name, 0 );
		$attachment = $attachment_id ? wp_get_attachment_image_src($attachment_id, $size) : null;

		if( $attachment && !empty($attachment[0]) ) {
			return $attachment[0];
		}
	}
	
	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public static function getData($data, $key, $default = null) {
		return isset($data[$key]) 
			? (is_string($data[$key]) ? wp_kses_post(stripslashes($data[$key])) : $data[$key]) 
			: $default;
	}

    /**
     * Get e-mails of WP & Bookly admins
     *
     * @return array
     */
    public static function getAdminEmails()
    {
        global $wpdb;

        // Add to filter capability manage_options or manage_bookly
        $meta_query = array(
            'relation' => 'OR',
            array( 'key' => $wpdb->prefix . 'capabilities', 'compare' => 'LIKE', 'value' => '"manage_options"', ),
            array( 'key' => $wpdb->prefix . 'capabilities', 'compare' => 'LIKE', 'value' => '"manage_bookly"', ),
        );
        $roles = new \WP_Roles();
        // Find roles with capabilities manage_options or manage_bookly
        foreach ( $roles->role_objects as $role ) {
            if ( $role->has_cap( 'manage_options' ) || $role->has_cap( 'manage_connectpx_booking' ) ) {
                $meta_query[] = array( 'key' => $wpdb->prefix . 'capabilities', 'compare' => 'LIKE', 'value' => '"' . $role->name . '"', );
            }
        }

        return array_map(
            function ( $a ) { return $a->data->user_email; },
            get_users( compact( 'meta_query' ) )
        );
    }

    /**
     * Generates email's headers FROM: Sender Name < Sender E-mail >
     *
     * @param array $extra
     * @return array
     */
    public static function getEmailHeaders( $extra = array() )
    {
        $headers = array();
        $headers[] = 'Content-Type: text/html; charset=utf-8';
        $headers[] = 'From: ' . self::getOption('email_sender_name') . ' <' . self::getOption('email_sender') . '>';
        if ( isset ( $extra['reply-to'] ) ) {
            $headers[] = 'Reply-To: ' . $extra['reply-to']['name'] . ' <' . $extra['reply-to']['email'] . '>';
        }

        return apply_filters( 'connectpx_booking_email_headers', $headers );
    }

    /**
     * @inheritDoc
     */
    public static function logEmail( $to, $subject, $body, $headers, $attachments, $type_id )
    {
        if ( self::getOption('save_email_logs', 'yes') ) {
            $log = new Lib\Entities\EmailLog();
            $log->setTo( $to )
                ->setSubject( $subject )
                ->setBody( $body )
                ->setHeaders( json_encode( $headers ) )
                ->setAttach( json_encode( $attachments ) )
                ->setType( Lib\Entities\Notification::getTypeString( $type_id ) )
                ->setCreatedAt( current_time( 'mysql' ) )
                ->save();
        }
    }
	
	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public static function getCustomerTypes() {
		return [
			'private' => __('Private', 'connectpx_booking'),
			'contract' => __('Contract', 'connectpx_booking'),
		];
	}
	
	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public static function getSteps() {
		return [
		    'service' => [
		        'title' => __('Service', 'connectpx_booking'),
		    ],
		    'time' => [
		        'title' => __('Date & Time', 'connectpx_booking'),
		    ],
		    'repeat' => [
		        'title' => __('Repeat', 'connectpx_booking'),
		    ],
		    'details' => [
		        'title' => __('Details', 'connectpx_booking'),
		    ],
		    'payment' => [
		        'title' => __('Payment', 'connectpx_booking'),
		    ],
		    'done' => [
		        'title' => __('Done', 'connectpx_booking'),
		    ],
		];
	}
	
	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public static function getSubServices() {
		return [
			'oneway' => [
				'title' => __('One Way', 'connectpx_booking'),
			],
			'roundtrip_regular' => [
				'title' => __('Round Trip - Regular', 'connectpx_booking'),
			],
			'roundtrip_dialysis' => [
				'title' => __('Round Trip - Dialysis', 'connectpx_booking'),
			],
		];
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public static function getSubServicesFields() {
		return [
			[
				'label' => __('Flat Rate', 'connectpx_booking'),
				'name' => 'flat_rate',
			],
			[
				'label' => __('Min Miles', 'connectpx_booking'),
				'name' => 'min_miles',
			],
			[
				'label' => __('Rate Per Mile', 'connectpx_booking'),
				'name' => 'rate_per_mile',
			],
			[
				'label' => __('Min Waiting Time (Minutes)', 'connectpx_booking'),
				'name' => 'min_waiting_time',
			],
			[
				'label' => __('Per Minute Fee', 'connectpx_booking'),
				'name' => 'rate_per_waiting_time',
			],
			[
				'label' => __('After hours fee', 'connectpx_booking'),
				'name' => 'after_hours_fee',
			],
			[
				'label' => __('No show fee', 'connectpx_booking'),
				'name' => 'no_show_fee',
			],
		];
	}

	/**
     * @inheritDoc
     */
    public static function createWPUserByCustomer( Customer $customer, $password )
    {
        if ( ! $customer->getWpUserId() ) {
            $wp_user_id = get_current_user_id();
            // if ( BooklyLib\Config::wooCommerceEnabled() && is_admin() ) {
            //     // If WC administrator manually changes the order status,
            //     // it is not allowed for new client to tie administrator's ID,
            //     // but create a new wp_user
            //     $wp_user_id = 0;
            // }

            $params = array(
                'first_name' => $customer->getFirstName(),
                'last_name' => $customer->getLastName(),
                'full_name' => $customer->getFullName(),
                'email' => $customer->getEmail(),
            );
            // Create new WP user and send email notification.
            try {
                $wp_user = self::createWPUser( $params, $password, 'customer' );
                $wp_user->set_role( 'customer' );
                $wp_user_id = $wp_user->ID;

                // Save entity for fill name, first_name, last_name
                $customer->setWpUserId( $wp_user_id )->save();

                // Send email/sms notification.
                // Lib\Notifications\NewWpUser\Sender::sendAuthToClient( $customer, $wp_user->display_name, $password );
            } catch ( \Exception $e ) {
                $wp_user_id = null;
            }

            $customer->setWpUserId( $wp_user_id );
        }

        return $customer;
    }

	/**
     * Create WordPress user
     *
     * @param array $params expected ['first_name', 'last_name', 'full_name', 'email' ]
     * @param string $password
     * @param string $alt_base
     * @return \WP_User
     * @throws BooklyLib\Base\ValidationException
     */
    public static function createWPUser( array $params, &$password, $alt_base = 'customer' )
    {
        if ( $params['email'] == '' ) {
            throw new Lib\Base\ValidationException( __( 'Email required', 'connectpx_booking' ), 'email' );
        }

        if ( email_exists( $params['email'] ) ) {
            throw new Lib\Base\ValidationException( __( 'Email already exists', 'connectpx_booking' ), 'email' );
        }

        $base = sanitize_user( sprintf( '%s %s', $params['first_name'], $params['last_name'] ), true );
        $base     = $base != '' ? $base : $alt_base;
        $username = $base;
        $i        = 1;
        while ( username_exists( $username ) ) {
            $username = $base . $i;
            ++ $i;
        }
        // Generate password.
        $password = $password ? $password : wp_generate_password( 6, true );
        
        // Create WordPress user.
        $wp_user_id = wp_create_user( $username, $password, $params['email'] );
        if ( is_wp_error( $wp_user_id ) ) {
            throw new Lib\Base\ValidationException( implode( $wp_user_id->get_error_messages(), PHP_EOL ), 'wp_user' );
        }

        return get_user_by( 'id', $wp_user_id );
    }

    /**
     * @param array $data
     * @return string
     */
    public static function getFullAddressByCustomerData( array $data )
    {
    	return Lib\Utils\Codes::replace( self::getOption( 'address_format' ), $data, false );
    }

    /**
     * @param array $data
     * @return string
     */
    public static function mergeFromCustomerAddress( array $address, array $customerAddress )
    {
    	foreach ( $address as $key => $value ) {
    		if( empty($value) && !empty( $customerAddress[ $key ] ) ) {
    			$address[ $key ] = $customerAddress[ $key ];
    		}
    	}

    	return $address;
    }

    /**
     * Get CSRF token.
     *
     * @return string
     */
    public static function getCsrfToken()
    {
        if ( self::$csrf === null ) {
            self::$csrf = wp_create_nonce( 'connectpx_booking' );
        }

        return self::$csrf;
    }

    /**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public static function getDistanceInMiles( $meters ) {
		return ceil($meters * 0.000621371192);
	}

    /**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public static function getTimeInMinutes( $seconds ) {
		return floor($seconds/3600) . " h " . floor($seconds/60%60) . " m";
	}

    /**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public static function isOffTimeService( $slot ) {
        $officeHours = self::getOption('business_hours', []);
        if( empty($officeHours) || !is_array($officeHours) ) {
        	return true;
        }

        list($date, $pickupTime, $returnTime) = $slot;

        $datePoint = Lib\Slots\DatePoint::fromStr( $date );
        $weekDay = $datePoint->format('w') + 1;
        $officeDay = $officeHours[ $weekDay ];

        if($officeDay['from'] == 'off' || $officeDay['to'] == 'off') {
        	return true;
        }

        $fromTp = Lib\Slots\TimePoint::fromStr( $officeDay['from'] );
        $toTp = Lib\Slots\TimePoint::fromStr( $officeDay['to'] );
        $pickupTp = Lib\Slots\TimePoint::fromStr( $pickupTime );

        if(  $pickupTp->lt( $fromTp ) || $pickupTp->gt( $toTp ) ) {
        	return true;
        }

        if( $returnTime ) {
        	$returnTp = Lib\Slots\TimePoint::fromStr( $returnTime );

        	if(  $returnTp->lt( $fromTp ) || $returnTp->gt( $toTp ) ) {
	        	return true;
	        }
        }
	}

	/**
     * @inheritDoc
     */
    public static function getLastCustomerTimezone( $customer_id )
    {
        $timezone = Lib\Entities\Appointment::query( 'a' )
            ->select( 'a.time_zone, a.time_zone_offset' )
            ->where( 'a.customer_id', $customer_id )
            ->whereNot( 'a.time_zone_offset', null )
            ->sortBy( 'created_at' )
            ->order( 'DESC' )
            ->limit( 1 )
            ->fetchArray();

        if ( ! empty( $timezone ) ) {
            $timezone = current( $timezone );

            return self::getCustomerTimezone( $timezone['time_zone'], $timezone['time_zone_offset'] );
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public static function getCustomerTimezone( $time_zone, $time_zone_offset )
    {
        if ( $time_zone ) {
            return $time_zone;
        } elseif ( $time_zone_offset !== null ) {
            return sprintf( 'UTC%s%s', $time_zone_offset > 0 ? '-' : '+', abs( $time_zone_offset ) / 60 );
        } else {
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public static function getTimeZoneOffset( $time_zone_value )
    {
        $time_zone        = null;
        $time_zone_offset = null;  // in minutes

        // WordPress value.
        if ( $time_zone_value ) {
            if ( preg_match( '/^UTC[+-]/', $time_zone_value ) ) {
                $offset           = preg_replace( '/UTC\+?/', '', $time_zone_value );
                $time_zone        = null;
                $time_zone_offset = - $offset * 60;
            } else {
                $time_zone        = $time_zone_value;
                $time_zone_offset = - timezone_offset_get( timezone_open( $time_zone_value ), new \DateTime() ) / 60;
            }
        }

        return compact( 'time_zone', 'time_zone_offset' );
    }

    /**
     * Check whether the current user is administrator or not.
     *
     * @return bool
     */
    public static function isCurrentUserAdmin()
    {
        return current_user_can( 'manage_options' );
    }

    /**
     * Check whether the current user is administrator or not.
     *
     * @return bool
     */
    public static function isCurrentUserSupervisor()
    {
        return current_user_can( 'manage_options' );
    }

    /**
     * Check whether the current user is customer or not.
     *
     * @return bool
     */
    public static function isCurrentUserCustomer()
    {
        return Lib\Entities\Customer::query()->where( 'wp_user_id', get_current_user_id() )->count() > 0;
    }

    /**
     * Determine the current user time zone which may be the staff or WP time zone
     *
     * @return string
     */
    public static function getCurrentUserTimeZone()
    {
        // Use WP time zone by default
        return Lib\Config::getWPTimeZone();
    }

    /**
     * Determine the current user time zone which may be the staff or WP time zone
     *
     * @return string
     */
    public static function getGoogleMapLink( $info, $width = "100%", $height = "300" )
    {
        $origin = implode(",", $info['from']);
        $destination = implode(",", $info['to']);

        return [
        	'link' => 'https://www.google.com/maps/dir/?api=1&origin='. $origin .'&destination='. $destination,
        	'iframe' => sprintf(
        		'<iframe 
        			width="%s" 
        			height="%s" 
        			frameborder="0" 
        			style="border:0" 
        			src="https://www.google.com/maps/embed/v1/directions?key=%s&origin=%s&destination=%s" a
        			llowfullscreen>
        		</iframe>', 
        		$width, 
        		$height, 
        		self::getOption('google_api_key', ''), 
        		$origin, 
        		$destination
        	)
        ];
    }

    /**
     * Determine the current user time zone which may be the staff or WP time zone
     *
     * @return string
     */
    public static function formatedItemsList( $items )
    {      
        $html = "";

        foreach ( $items as $item ) {
        	$html .= sprintf("<div class='list-item'><strong>%s: </strong> <span>%s</span></div>", $item['label'], $item['value']);
        }

        return $html;
    }

    /**
     * Determine the current user time zone which may be the staff or WP time zone
     *
     * @return string
     */
    public static function formatedPickupInfo( $info )
    {      
        $items = [
            [
                'label' => __('Patient Name', 'connectpx_booking'),
                'value' => $info['patient_name'],
            ],
            [
                'label' => __('Room No', 'connectpx_booking'),
                'value' => $info['room_no'],
            ],
            [
                'label' => __('Contact Person', 'connectpx_booking'),
                'value' => $info['contact_person'],
            ],
            [
                'label' => __('Contact No', 'connectpx_booking'),
                'value' => $info['contact_no'],
            ],
            [
                'label' => __('Address', 'connectpx_booking'),
                'value' => $info['address']['address'],
            ],
        ];

        return self::formatedItemsList( $items );
    }

    /**
     * Determine the current user time zone which may be the staff or WP time zone
     *
     * @return string
     */
    public static function formatedDestinationInfo( $info )
    {        
        $items = [
            [
                'label' => __('Hospital Name', 'connectpx_booking'),
                'value' => $info['hospital'],
            ],
            [
                'label' => __('Contact No', 'connectpx_booking'),
                'value' => $info['contact_no'],
            ],
            [
                'label' => __('Dr. Name', 'connectpx_booking'),
                'value' => $info['dr_name'],
            ],
            [
                'label' => __('Dr. Contact No', 'connectpx_booking'),
                'value' => $info['dr_contact_no'],
            ],
            [
                'label' => __('Room No', 'connectpx_booking'),
                'value' => $info['room_no'],
            ],
            [
                'label' => __('Address', 'connectpx_booking'),
                'value' => $info['address']['address'],
            ],
        ];

        return self::formatedItemsList( $items );
    }

    /**
     * Determine the current user time zone which may be the staff or WP time zone
     *
     * @return string
     */
    public static function formatedServiceInfo( $appointment, $service, $subService )
    {        
        $items = [
            [
                'label' => __('Service', 'connectpx_booking'),
                'value' => $service->getTitle(),
            ],
            [
                'label' => __('Trip Type', 'connectpx_booking'),
                'value' => $subService->getTitle(),
            ],
            [
                'label' => __('Distance', 'connectpx_booking'),
                'value' => sprintf("%s miles - one side", $appointment->getDistance()),
            ],
            [
                'label' => __('Estimated Time', 'connectpx_booking'),
                'value' => sprintf("%s - one side", $appointment->getEstimatedTimeInMins()),
            ],
        ];

        return self::formatedItemsList( $items );
    }

    /**
     * Get common settings for Bookly calendar
     *
     * @return array
     */
    public static function getCalendarSettings()
    {
        $slot_length_minutes = self::getOption('slot_length', 15);
        $slot = new \DateInterval( 'PT' . $slot_length_minutes . 'M' );

        $hidden_days = array();
        $min_time = '00:00:00';
        $max_time = '24:00:00';
        $scroll_time = '08:00:00';
        // Find min and max business hours
        $min = $max = null;
        foreach ( Lib\Config::getBusinessHours() as $day => $bh ) {
            if ( $bh['start'] === null ) {
                continue;
            }
            if ( $min === null || $bh['start'] < $min ) {
                $min = $bh['start'];
            }
            if ( $max === null || $bh['end'] > $max ) {
                $max = $bh['end'];
            }
        }
        if ( $min !== null ) {
            $scroll_time = $min;
            if ( $max > '24:00:00' ) {
                $min_time = DateTime::buildTimeString( DateTime::timeToSeconds( $max ) - DAY_IN_SECONDS );
                $max_time = $max;
            }
        }

        return array(
            'hiddenDays' => $hidden_days,
            'slotDuration' => $slot->format( '%H:%I:%S' ),
            'slotMinTime' => $min_time,
            'slotMaxTime' => $max_time,
            'scrollTime' => $scroll_time,
            'locale' => Lib\Config::getShortLocale(),
            'monthDayMaxEvents' => 0,
            'mjsTimeFormat' => DateTime::convertFormat( 'time', DateTime::FORMAT_MOMENT_JS ),
            'datePicker' => DateTime::datePickerOptions(),
            'dateRange' => DateTime::dateRangeOptions(),
            'today' => __( 'Today', 'connectpx_booking' ),
            'week' => __( 'Week', 'connectpx_booking' ),
            'day' => __( 'Day', 'connectpx_booking' ),
            'month' => __( 'Month', 'connectpx_booking' ),
            'list' => __( 'List', 'connectpx_booking' ),
            'noEvents' => __( 'No appointments for selected period.', 'connectpx_booking' ),
            'more' => __( '+%d more', 'connectpx_booking' ),
        );
    }

    /**
     * Get services grouped by categories for drop-down list.
     *
     * @param string $raw_where
     * @return array
     */
    public static function getServiceDataForDropDown( $raw_where = null )
    {
        $result = array();

        $query = Lib\Entities\Service::query( 's' )
            ->select( 's.id, s.title' ); 

        if ( $raw_where !== null ) {
            $query->whereRaw( $raw_where, array() );
        }

        foreach ( $query->fetchArray() as $row ) {
            $result[] = array(
                'id'    => $row['id'],
                'title' => $row['title'],
            );
        }

        return $result;
    }

    /**
     * Set nocache constants.
     *
     * @param bool $forcibly
     */
    public static function noCache( $forcibly = false )
    {
        if ( $forcibly ) {
            if ( ! defined( 'DONOTCACHEPAGE' ) ) {
                define( 'DONOTCACHEPAGE', true );
            }
            if ( ! defined( 'DONOTCACHEOBJECT' ) ) {
                define( 'DONOTCACHEOBJECT', true );
            }
            if ( ! defined( 'DONOTCACHEDB' ) ) {
                define( 'DONOTCACHEDB', true );
            }
        }
    }

    public static function getWeekDates( $startDateStr, $endDateStr = null ) {
        $startDate = Lib\Slots\DatePoint::fromStr( $startDateStr );
        $endDate = $endDateStr ? Lib\Slots\DatePoint::fromStr( $endDateStr ) : Lib\Slots\DatePoint::now();

        $monday = clone $startDate->modify('Monday this week');
        $sunday = clone $startDate->modify('Sunday this week');
        $week = [
            'start' => $monday, 
            'end' => $sunday
        ];

        $weeks[] = $week;
        while ( $week['end']->lt( $endDate ) ) {
            $monday = clone $week['end']->modify('+1 day');
            $sunday = clone $week['end']->modify('+7 days');
            $week = [
                'start' => $monday, 
                'end' => $sunday
            ];
            $weeks[] = $week;
        }

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'weeks' => $weeks,
        ];
    }

    public static function getInvoicePeriodOptions() {
        $periods = [
            [
                'key' => 'this_week',
                'start' => date('Y-m-d'),
                'end' => null,
                'label' => 'This Week (%s - %s)',
            ],
            [
                'key' => 'last_week',
                'start' => date('Y-m-d', strtotime('Monday last week')),
                'end' => date('Y-m-d', strtotime('Monday last week')),
                'label' => 'Last Week (%s - %s)',
            ],
            [
                'key' => 'last_three_months',
                'start' => date('Y-m-d', strtotime('-3 months')),
                'end' => null,
                'label' => 'Last 3 Months (%s - %s)',
            ],
            [
                'key' => 'last_six_months',
                'start' => date('Y-m-d', strtotime('-6 months')),
                'end' => null,
                'label' => 'Last 6 Months (%s - %s)',
            ],
            [
                'key' => 'this_year',
                'start' => date('Y-01-01'),
                'end' => null,
                'label' => 'This Year (%s - %s)',
            ],
            [
                'key' => 'last_year',
                'start' => date('Y-m-d', strtotime('first day of january last year')),
                'end' => null,
                'label' => 'Last Year (%s - %s)',
            ],
        ];

        $options = [];

        foreach( $periods as $period ) {
            $dates = Lib\Utils\Common::getWeekDates( $period['start'], $period['end'] );
            $options[ $period['key'] ] = [
                'weeks' => $dates['weeks'],
                'label' => __( sprintf( $period['label'], $dates['start_date']->format('d/m/Y'), $dates['end_date']->format('d/m/Y') ), 'connectpx_booking' ),
            ];
        }

        return $options;
    }

    /**
     * @inheritDoc
     */
    public static function getCompanyDetails()
    {
        $company_logo = self::getOptionMediaSource('company_logo_attachment_id');
        $codes = [
            'company_address' => nl2br( self::getOption('company_address', '') ),
            'company_logo' => $company_logo,
            'company_name' => self::getOption('company_name', ''),
            'company_phone' => self::getOption('company_phone', ''),
            'company_website' => self::getOption('company_website', ''),
        ];
        return $codes;
    }

    /**
     * @inheritDoc
     */
    public static function getCompanyDetailCodes()
    {
        $company_logo = self::getOptionMediaSource('company_logo_attachment_id');
        $codes = [
            '{company_address}' => nl2br( self::getOption('company_address', '') ),
            '{company_logo}' => $company_logo ? sprintf( '<img src="%s"/>', esc_attr( $company_logo ) ) : '',
            '{company_name}' => self::getOption('company_name', ''),
            '{company_phone}' => self::getOption('company_phone', ''),
            '{company_website}' => self::getOption('company_website', ''),
        ];
        return $codes;
    }
}
