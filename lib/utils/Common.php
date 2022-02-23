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
            self::$csrf = wp_create_nonce( 'bookly' );
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
}
