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

}
