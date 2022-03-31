<?php
namespace ConnectpxBooking\Frontend;

use ConnectpxBooking\Frontend\Modules;
use ConnectpxBooking\Lib;
use ConnectpxBooking\Lib\Plugin;
use ConnectpxBooking\Lib\Utils;

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    ConnectpxBooking
 * @subpackage ConnectpxBooking\Frontend
 * @author     Shahid Hussain <shahidhussainaali@gmail.com>
 */
class Frontend {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public static function run() {
		Utils\Session::start();

		Ajax::init();
		ShortCode::init();
		WooCommerce::init();

		Modules\Dashboard::run();
		Components\Dialogs\Appointment\Cancel\Ajax::init();

		add_action( 'wp_enqueue_scripts', array(__CLASS__, 'enqueueScripts'), 10 );

		add_filter( 'body_class', array(__CLASS__, 'bodyClass') );
		// add_shortcode( 'connectpx_booking', array(__CLASS__, 'Shortcode') );
		add_action( 'wp_ajax_connectpx_booking_ajax', array(__CLASS__, 'Ajax') );
		add_action( 'wp_ajax_nopriv_connectpx_booking_ajax', array(__CLASS__, 'Ajax') );
		add_filter( 'wp_authenticate_user', array(__CLASS__, 'CheckBlockedUser') );

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public static function enqueueScripts() {
		Plugin::globalScripts();

		$front_resources = plugin_dir_url( __FILE__ );

        $api_key = Utils\Common::getOption('google_api_key', '');
        if( $api_key ) {
			wp_register_script( 
	            'connectpx_booking_google_maps', 
	            'https://maps.googleapis.com/maps/api/js?key='.$api_key.'&libraries=places', 
	            array(), 
	            Plugin::version(), 
	            false 
	        );
        }
        wp_enqueue_style( 
			'connectpx_booking_main', 
			$front_resources . 'resources/css/main.css', 
			array(), 
			Plugin::version(), 
			'all' 
		);
		wp_enqueue_script( 
			'connectpx_booking_main', 
			$front_resources . 'resources/js/main.js', 
			array('jquery', 'connectpx_booking_global'), 
			Plugin::version(), 
			false 
		);
		wp_localize_script( 'connectpx_booking_main', 'ConnectpxBookingOptions',
            array( 
            	'ajax_url' => admin_url( 'admin-ajax.php' )
            ) 
        );
		wp_register_style( 
			'connectpx_booking_customer_bookings', 
			$front_resources . 'resources/css/customer-bookings.css', 
			array(
				'connectpx_booking_bootstrap',
				'connectpx_booking_main',
			), 
			Plugin::version(), 
			'all' 
		);
		wp_register_script( 
			'connectpx_booking_customer_bookings', 
			$front_resources . 'resources/js/customer-bookings.js', 
			array( 
				'jquery', 
				'connectpx_booking_global',
				'connectpx_booking_bootstrap',
				'connectpx_booking_datatables', 
				'connectpx_booking_moment', 
				'connectpx_booking_daterangepicker',
				'connectpx_booking_select2' 
			), 
			Plugin::version(), 
			false 
		);
		wp_register_style( 
			'connectpx_booking_customer_invoices', 
			$front_resources . 'resources/css/customer-invoices.css', 
			array(
				'connectpx_booking_bootstrap',
				'connectpx_booking_main',
			), 
			Plugin::version(), 
			'all' 
		);
		wp_register_script( 
			'connectpx_booking_customer_invoices', 
			$front_resources . 'resources/js/customer-invoices.js', 
			array( 
				'jquery', 
				'connectpx_booking_global',
				'connectpx_booking_bootstrap',
				'connectpx_booking_datatables', 
				'connectpx_booking_moment', 
				'connectpx_booking_daterangepicker',
				'connectpx_booking_select2' 
			), 
			Plugin::version(), 
			false 
		);
		wp_register_style( 
			'connectpx_booking_customer_account', 
			$front_resources . 'resources/css/customer-account.css', 
			array(
				'connectpx_booking_bootstrap',
				'connectpx_booking_main',
			), 
			Plugin::version(), 
			'all' 
		);
		wp_register_script( 
			'connectpx_booking_customer_account', 
			$front_resources . 'resources/js/customer-account.js', 
			array( 
				'jquery', 
				'connectpx_booking_global',
				'connectpx_booking_bootstrap',
				'connectpx_booking_select2' 
			), 
			Plugin::version(), 
			false 
		);
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public static function bodyClass($classes) {

		global $post;

	    if( isset($post->post_content) && has_shortcode( $post->post_content, 'connectpx-booking-form' ) ) {
	        $classes[] = 'connectpx_booking-page surveys ';
	    }
	    return $classes;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public static function Shortcode($atts) {
		wp_enqueue_script('connectpx_booking');
		wp_enqueue_style('connectpx_booking');

		$atts = shortcode_atts([

		], $atts);

		extract($atts);

		$args = array(
		    'post_type' => 'connectpx_booking_cards',
		    'posts_per_page' => -1,
		    'post_status' => 'publish',
		    'orderby' => 'menu_order', 
		    'order' => 'DESC',
		);
		$arr_posts = new WP_Query($args);

		$cards = [];
		if ($arr_posts->have_posts()) : 

			$counter = 0;
			while ($arr_posts->have_posts()) : $arr_posts->the_post();

				$post_id = get_the_ID();
				$thumbnail = get_the_post_thumbnail_url(get_the_ID(), 'full');

				$thumbnail_name = "";
				if($thumbnail) {
					$thumbnail_parts = explode("/", $thumbnail);
					$thumbnail_name = end($thumbnail_parts);
				}

				$cards[] = [
					'id' => $post_id,
					'survey_id' => 1,
					'label' => get_the_title(),
					'description' => esc_attr(get_the_content()),
					'image_url' => $thumbnail,
					'uploaded_image_thumbnail_url' => $thumbnail,
					'uploaded_image' => $thumbnail_name,
					'position' => $counter,
					'migrating' => false,
				];
			endwhile;
			wp_reset_postdata();
		endif;

		$terms = get_terms( array(
		    'taxonomy' => Optimalsort_Utill::get_taxonomy_name(),
		    'hide_empty' => false,
		) );

		$categories = [];
		$counter = 0;
		foreach ($terms as $key => $term) {
			$categories[] = [
				'id' => $term->term_id,
				'label' => $term->name,
				'position' => $counter,
				'description' => '',
				'card_limit' => NULL,
			];
		}

		$cards_data = [
			'type' => 'closed',
			'cards' => $cards,
			'categories' => $categories,
			'require_cards_sorted' => true,
			'require_categories_named' => true,
			'cards_with_positions' => true,
			'cards_with_descriptions' => true,
			'cards_with_images' => true,
			'categories_with_descriptions' => false,
			'hide_labels' => false,
			'skip_instructions' => false,
			'display_unsorted_cards_progress' => true,
			'categories_with_card_limits' => false,
			'custom_color' => '',
			'panel_participant' => NULL,
		];

		if(empty(Utils\Session::get('email', ''))) {
			Utils\Session::set('step', 'welcome');
		}
		
		//Utils\Session::set('step', 'welcome');

		$form_data = [
			'email' => Utils\Session::get('email', ''),
			'name' => Utils\Session::get('name', ''),
			'financial' => Utils\Session::get('financial', ''),
			'email_copy' => Utils\Session::get('email_copy', ''),
			'comment' => Utils\Session::get('comment', ''),
			'sorted_cards' => Utils\Session::get('sorted_cards', []),
			'step' => Utils\Session::get('step', 'welcome'),
		];

		ob_start();
		include_once 'partials/shortcode.php';
		$html = ob_get_clean();

		return $html;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public static function Ajax() {
		$json_data = [
			'status' => 'error',
			'message' => 'Nothing to process',
			'data' => []
		];

		$request_type = !empty($_REQUEST['request_type']) ? $_REQUEST['request_type'] : "";
		if($request_type == "save_cards") {
			$cards_json = !empty($_REQUEST['card_sort_json']) ? json_decode(str_replace("\\", "", $_REQUEST['card_sort_json']), true) : null;

			$sorted_cards = [];

			if(!empty($cards_json['cardsort'])) {
				foreach ($cards_json['cardsort'] as $key => $value) {
					if(strpos($key, 'category') !== false) {
						$category = str_replace("category-", "", $key);
						$sorted_cards[$category] = $value;
					}
				}
			}
			Utils\Session::set('sorted_cards', $sorted_cards);

			$json_data = [
				'status' => 'success',
				'message' => 'Categories saved',
				'data' => [
					'sorted_cards' => $sorted_cards,
				]
			];

			if(!empty($cards_json['comment'])) {
				Utils\Session::set('comment', $cards_json['comment']);
				$json_data['data']['comment'] = $cards_json['comment'];
			}
			else {
				Utils\Session::set('comment', '');
			}

			if(!empty($_REQUEST['final']) && $_REQUEST['final'] == 1) {
				Utils\Session::set('step', 'after');
			}
			else {
				Utils\Session::set('step', 'cardsort');
			}
		}

		if($request_type == "save_data") {
			$form_data = !empty($_REQUEST['form_data']) ? $_REQUEST['form_data'] : [];

			foreach ($form_data as $key => $value) {
				Utils\Session::set($key, $value);
			}

			if(!empty($form_data['completed']) && $form_data['completed']) {
				Utils\Session::set('completed_time', date('Y-m-d H:i:s'));

				if($this->step_completed()) {
					Utils\Session::reset();

					$json_data = [
						'status' => 'success',
						'message' => 'Data saved',
						'data' => [
							'form_data' => $form_data,
						]
					];
				} else {
					$json_data = [
						'status' => 'error',
						'message' => 'Error sending emails.',
						'data' => [
							'form_data' => $form_data,
						]
					];
				}
			} else {
				$json_data = [
					'status' => 'success',
					'message' => 'Data saved',
					'data' => [
						'form_data' => $form_data,
					]
				];
			}

			
		}

		$json_data['data']['request'] = $_REQUEST;

		header('Content-Type: application/json');
		echo json_encode($json_data);
		exit();
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public static function CheckBlockedUser( $user ) {
		if( is_wp_error( $user ) ){
            return $user;
        }

        $customer = new Lib\Entities\Customer();

        if( is_object( $user ) && isset( $user->ID ) && $customer->loadBy( array( 'wp_user_id' => $user->ID ) ) && ! $customer->isEnabled() ){
            $error_message = Utils\Common::getOption('customer_account_disabled_message');
            return new \WP_Error( 'disabled', ( $error_message ) ? $error_message : __( 'Your account is disabled!', 'connectpx_booking' ) );
        }
        else{
            return $user;
        }
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function step_completed() {
		$client_emmail_status = true;
		if(Utils\Session::get('email_copy', "Yes") == "Yes") {
			$client_emmail_status = $this->send_client_email();
		}

		$admin_emmail_status = $this->send_admin_email();

		if( $client_emmail_status &&  $admin_emmail_status ) {
			return true;
		}
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function send_client_email() {
		$client_cc_emails = explode(",", Utils\Common::getOption('client_cc_emails', ''));
		$client_cc_emails = array_map("trim", $client_cc_emails);

		$client_email_subject = $this->filter_tokens(Utils\Common::getOption('client_email_subject', ''));
		$client_email_template = $this->filter_tokens(wpautop(Utils\Common::getOption('client_email_template', '')));
		
		$to = Utils\Session::get('email', "");
		$subject = $client_email_subject;
		$body = $client_email_template;
		$headers = array('Content-Type: text/html; charset=UTF-8;');

		$email_status = wp_mail( $to, $subject, $body, $headers );

		if( $email_status ) {
			return true;
		}
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function send_admin_email() {
		$admin_email = explode(",", Utils\Common::getOption('admin_email', ''));
		$admin_email = array_map("trim", $admin_email);

		if(empty($admin_email)) {
			return false;
		}

		$admin_email_subject = $this->filter_tokens(Utils\Common::getOption('admin_email_subject', ''));
		$admin_email_template = $this->filter_tokens(wpautop(Utils\Common::getOption('admin_email_template', '')));
		
		$to = $admin_email[0];
		$subject = $admin_email_subject;
		$body = $admin_email_template;
		$headers = array('Content-Type: text/html; charset=UTF-8;');

		$email_status = wp_mail( $to, $subject, $body, $headers );
		print_r($email_status);

		if( $email_status ) {
			return true;
		}
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function filter_tokens($content) {
		$sorted_cards = Utils\Session::get('sorted_cards', []);
		$cards_list = $this->create_cards_html($sorted_cards, "list");
		$cards_pictures = $this->create_cards_html($sorted_cards);

		$tokens = [
			'[DATE_TIME]' => Utils\Session::get('completed_time', ""),
			'[NAME]' => Utils\Session::get('name', ""),
			'[EMAIL]' => Utils\Session::get('email', ""),
			'[SORTED_CARDS_LIST]' => $cards_list,
			'[SORTED_CARDS_PICTURES]' => $cards_pictures,
		];
		
		$content = str_replace(array_keys($tokens), array_values($tokens), $content);
		return $content;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function create_cards_html($sorted_cards, $type = "pictures") {
		$html = "";
		foreach ($sorted_cards as $term_id => $post_ids) {
			$category = get_term( $term_id, Optimalsort_Utill::get_taxonomy_name() );

			$args = array(
			    'post_type' => Optimalsort_Utill::get_post_type_name(),
			    'posts_per_page' => -1,
			    'orderby' => 'post__in', 
			    'post__in' => $post_ids,
			); 

			$arr_posts = new WP_Query($args);

			$cards = "";
			if ($arr_posts->have_posts()) : 

				$counter = 0;
				while ($arr_posts->have_posts()) : $arr_posts->the_post();

					$post_id = get_the_ID();
					$thumbnail = get_the_post_thumbnail_url(get_the_ID(), 'full');

					$cards .= '<tr>';
						if( $type =="pictures" && trim($category->name) != "Not Applicable" ) {
							$cards .= '<td width="30%"><img src="'. $thumbnail .'" alt="'. get_the_title() .'" /></td>';
							$cards .= '<td width="30%">'. get_the_title() .'</td>';
							$cards .= '<td width="40%">'. get_the_excerpt() .'</td>';
						} else {
							$cards .= '<td>'. get_the_title() .'</td>';
						}
					$cards .= '</tr>';

				endwhile;
				wp_reset_postdata();
			endif;

			$html .= '<p>'. $category->description .'</p>';
			$html .= '<p><strong>'. $category->name .'</strong></p>';
			$html .= '<table border="2" cellpadding="5" cellspacing="0" width="100%">';
			$html .=  $cards;
			$html .= '</table>';
		}

		return $html;
	}
}
