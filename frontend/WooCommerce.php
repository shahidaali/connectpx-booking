<?php
namespace ConnectpxBooking\Frontend;

use ConnectpxBooking\Lib;

/**
 * Class Controller
 * @package ConnectpxBooking\Frontend\Modules\WooCommerce
 */
class WooCommerce extends MyAccountPages
{
    /**
     * Event data structure.
     *
     * @since connectpx_booking-addon-pro v1.0
     *
     * @version 1.1
     *  add key 'wc_checkout' with values 'billing_country', 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_state', 'billing_postcode', 'billing_first_name', 'billing_last_name', 'billing_email', 'billing_phone'
     */
    const VERSION = '1.1';
    const CONTRACT_CUSTOMER_PAYMENT_METHOD = 'cod';
    const PRIVATE_CUSTOMER_PAYMENT_METHOD = 'square_credit_card';

    protected static $checkout_info = array();

    /**
     * @inheritDoc
     */
    public static function init()
    {
        /** @var self $self */
        $self = get_called_class();

        add_action( 'woocommerce_check_cart_items', array( $self, 'checkAvailableTimeForCart' ), 10, 0 );
        add_action( 'woocommerce_checkout_create_order_line_item', array( $self, 'createOrderLineItem' ), 10, 4 );
        add_action( 'woocommerce_after_order_itemmeta', array( $self, 'orderItemMeta' ), 11, 1 );
        add_action( 'woocommerce_before_calculate_totals', array( $self, 'beforeCalculateTotals' ), 10, 1 );
        add_action( 'woocommerce_order_item_meta_end', array( $self, 'orderItemMeta' ), 10, 1 );
        add_action( 'woocommerce_order_status_cancelled', array( $self, 'cancelOrder' ), 10, 1 );
        add_action( 'woocommerce_order_status_completed', array( $self, 'paymentComplete' ), 100, 1 );
        add_action( 'woocommerce_order_status_on-hold', array( $self, 'paymentComplete' ), 100, 1 );
        add_action( 'woocommerce_order_status_processing', array( $self, 'paymentComplete' ), 100, 1 );
        add_action( 'woocommerce_order_status_refunded', array( $self, 'cancelOrder' ), 10, 1 );
        add_action( 'woocommerce_after_calculate_totals', array( $self, 'afterCalculateTotals' ), 10, 1 );

        add_filter( 'woocommerce_checkout_get_value', array( $self, 'checkoutValue' ), 10, 2 );
        add_filter( 'woocommerce_get_item_data', array( $self, 'getItemData' ), 10, 2 );
        add_filter( 'woocommerce_quantity_input_args', array( $self, 'quantityArgs' ), 10, 2 );
        add_filter( 'woocommerce_cart_item_price', array( $self, 'getCartItemPrice' ), 10, 3 );
        // add_filter( 'woocommerce_calculate_item_totals_taxes', array( $self, 'calculateItemTotalsTaxes' ), 99, 3 );

        add_action( 'template_redirect', array( $self, 'skipWoocommercePages' ) );
        add_action( 'woocommerce_checkout_billing', array( $self, 'checkoutDetails' ) );
        add_filter( 'woocommerce_checkout_fields', array( $self, 'removeCheckoutFields' ) );
        add_filter( 'woocommerce_add_cart_item_data', array( $self, 'removeExistingCartItems' ), 12, 3 );
        add_filter( 'woocommerce_available_payment_gateways', array( $self, 'availablePaymentGateways' ), 12, 1 );
        add_filter( 'woocommerce_order_button_text', array( $self, 'orderButtonText' ), 12, 1 );
        parent::init();

        // add_action( 'init', function(){
        //     $appointment = new Lib\Entities\Appointment();
        //     $appointment->load(1);
        //     $response = $appointment->refund();
        //     dd($response);
        // });
    }

    /**
     * Calculate item tax.
     *
     * @param array           $taxes
     * @param \stdClass       $item
     * @param \WC_Cart_Totals $wc_cart_totals
     * @return array
     */
    public static function calculateItemTotalsTaxes( $taxes, $item, $wc_cart_totals )
    {
        if ( property_exists( $item, 'object' )
            && array_key_exists( 'connectpx_booking', $item->object )
            && Lib\Config::taxesActive()
        ) {
            $userData = new Lib\UserBookingData( null );
            $userData->fillData( $item->object['connectpx_booking'] );
            $userData->cart->setItemsData( $item->object['connectpx_booking']['items'] );
            $info = $userData->cart->getInfo();
            $tax_rates_id = 1;
            if ( ! empty( $taxes ) ) {
                $tax_rates_id = key( $taxes );
            }

            return array( $tax_rates_id => $info->getTotalTax() * 100 );
        }

        return $taxes;
    }

    /**
     * Verifies the availability of all appointments that are in the cart
     */
    public static function checkAvailableTimeForCart()
    {
        $recalculate_totals = false;
        foreach ( WC()->cart->get_cart() as $wc_key => $wc_item ) {
            if ( array_key_exists( 'connectpx_booking', $wc_item ) ) {
                $userData = new Lib\UserBookingData();
                $userData->fillData( $wc_item['connectpx_booking'] );
                $userData->cart->setItemsData( $wc_item['connectpx_booking']['items'] );
                if ( self::checkIfServiceIsProvided( $userData ) ) {
                    // Check if appointment's time is still available
                    $failed_cart_key = $userData->cart->getFailedKey();
                    if ( $failed_cart_key !== null ) {
                        $cart_item = $userData->cart->get( $failed_cart_key );
                        $slot      = $cart_item->getSlot();
                        wc_add_notice( strtr( __( 'Sorry, the time slot %date_time% for %service% has been already occupied.', 'connectpx_booking' ),
                            array(
                                '%service%'   => '<strong>' . $cart_item->getService()->getTitle() . '</strong>',
                                '%date_time%' => Lib\Utils\DateTime::formatDateTime( $slot[0] ),
                            ) ), 'error' );
                        WC()->cart->set_quantity( $wc_key, 0, false );
                        $recalculate_totals = true;
                    }
                } else {
                    wc_add_notice( __( 'This service is no longer provided.', 'connectpx_booking' ), 'error' );
                    WC()->cart->set_quantity( $wc_key, 0, false );
                    $recalculate_totals = true;
                }
            }
        }
        if ( $recalculate_totals ) {
            WC()->cart->calculate_totals();
        }
    }

    /**
     * Set subtotal and subtotal_tax for connectpx_booking items.
     *
     * @param \WC_Cart $cart_object
     */
    public static function afterCalculateTotals( $cart_object )
    {
        // if ( Lib\Config::taxesActive() ) {
        //     foreach ( $cart_object->cart_contents as $wc_key => &$wc_item ) {
        //         if ( isset ( $wc_item['connectpx_booking'] ) ) {
        //             $wc_item['line_subtotal']     = $wc_item['line_total'];
        //             $wc_item['line_subtotal_tax'] = $wc_item['line_tax'];
        //         }
        //     }
        // }
    }

    /**
     * Assign checkout value from appointment.
     *
     * @param $null
     * @param $field_name
     * @return string|null
     */
    public static function checkoutValue( $null, $field_name )
    {
        $dummy_fields = [];

        if ( empty( self::$checkout_info ) ) {
            foreach ( WC()->cart->get_cart() as $wc_key => $wc_item ) {
                if ( array_key_exists( 'connectpx_booking', $wc_item ) ) {
                    foreach ( $wc_item['connectpx_booking']['wc_checkout'] as $key => $value ) {
                        if ( $value != '' ) {
                            self::$checkout_info[ $key ] = $value;
                        }
                    }
                    break;
                }
            }
        }
        if ( array_key_exists( $field_name, self::$checkout_info ) ) {
            return self::$checkout_info[ $field_name ];
        }

        return null;
    }

    /**
     * Do bookings after checkout.
     *
     * @param $order_id
     */
    public static function paymentComplete( $order_id )
    {
        $wc_order = new \WC_Order( $order_id );
        $address = array(
            // WC address | ConnectpxBooking Customer address
            'country'    => 'country',
            'state'      => 'state',
            'city'       => 'city',
            'address_1'  => 'street',
            'address_2'  => 'additional_address',
            'postcode'   => 'postcode',
        );
        /** @var \WC_Countries $countries */
        $countries = WC()->countries;
        foreach ( $wc_order->get_items() as $item_id => $order_item ) {
            $data = wc_get_order_item_meta( $item_id, 'connectpx_booking' );
            if ( $data && ! isset ( $data['processed'] ) ) {
                $userData = new Lib\UserBookingData( null );
                foreach ( $address as $wc_part => $connectpx_booking_part ) {
                    $method_name = 'get_billing_' . $wc_part;
                    if ( method_exists( $wc_order, $method_name ) ) {
                        // WC checkout address
                        $value = $wc_order->$method_name();
                        if ( $wc_part == 'country' ) {
                            $value = isset( $countries->countries[ $value ] )
                                ? $countries->countries[ $value ]
                                : $value;
                        } elseif ( $wc_part == 'state' ) {
                            $country_code = $wc_order->get_billing_country();
                            $value   = isset( $countries->states[ $country_code ][ $value ] )
                                ? $countries->states[ $country_code ][ $value ]
                                : $value;
                        }
                        $data[ $connectpx_booking_part ] = $value;
                    }
                }
                $data['street_number'] = '';
                $userData->fillData( $data );
                $userData->setWcOrderId( $order_id );
                $userData->cart->setItemsData( $data['items'] );
                $appointment_ids = $userData->save( $wc_order );

                // Mark item as processed.
                $data['processed'] = true;
                $data['appointment_ids'] = $appointment_ids;
                $data['schedule_id'] = $userData->getScheduleId();

                wc_update_order_item_meta( $item_id, 'connectpx_booking', $data );
                Lib\Notifications\Cart\Sender::send( $wc_order );
            }
        }
    }

    /**
     * Cancel appointments on WC order cancelled.
     *
     * @param $order_id
     */
    public static function cancelOrder( $order_id )
    {
        $order = new \WC_Order( $order_id );
        foreach ( $order->get_items() as $item_id => $order_item ) {
            $data = wc_get_order_item_meta( $item_id, 'connectpx_booking' );
            if ( isset ( $data['processed'], $data['ca_ids'] ) && $data['processed'] ) {
                /** @var Lib\Entities\CustomerAppointment[] $ca_list */
                $ca_list = Lib\Entities\CustomerAppointment::query()->whereIn( 'id', $data['ca_ids'] )->find();
                foreach ( $ca_list as $ca ) {
                    $ca->cancel();
                }
                $data['ca_ids'] = array();
                wc_update_order_item_meta( $item_id, 'connectpx_booking', $data );
            }
        }
    }

    /**
     * Change attr for WC quantity input
     *
     * @param array       $args
     * @param \WC_Product $product
     * @return array
     */
    public static function quantityArgs( $args, $product )
    {
        // $wc_product_ids = self::getFromCache( 'wc_product_ids', null );
        // if ( $wc_product_ids === null ) {
        //     $wc_product_ids = Lib\Entities\Service::query()
        //         ->whereNot( 'wc_product_id', 0 )
        //         ->fetchCol( 'DISTINCT(wc_product_id)' );
        //     $wc_product_ids[] = get_option( 'connectpx_booking_wc_product' );
        //     self::putInCache( 'wc_product_ids', $wc_product_ids );
        // }

        // if ( in_array( $product->get_id(), $wc_product_ids ) ) {
        //     $args['max_value'] = $args['input_value'];
        //     $args['min_value'] = $args['input_value'];
        // }

        return $args;
    }

    /**
     * Change item price in cart.
     *
     * @param \WC_Cart $cart_object
     */
    public static function beforeCalculateTotals( $cart_object )
    {
        foreach ( $cart_object->cart_contents as $wc_key => $wc_item ) {
            if ( isset ( $wc_item['connectpx_booking'] ) ) {
                $userData = new Lib\UserBookingData();
                $userData->fillData( $wc_item['connectpx_booking'] );
                $userData->cart->setItemsData( $wc_item['connectpx_booking']['items'] );
                $cart_info = $userData->cart->getInfo();

                /** @var \WC_Product $product */
                $product   = $wc_item['data'];
                $product->set_price( $cart_info->getPayNow() );
            }
        }
    }

    /**
     * Update meta data for current product.
     *
     * @param \WC_Order_Item_Product $item
     * @param string $cart_item_key
     * @param array $values
     * @param \WC_Order $order
     */
    public static function createOrderLineItem( $item, $cart_item_key, $values, $order )
    {
        if ( isset ( $values['connectpx_booking'] ) ) {
            $item->update_meta_data( 'connectpx_booking', $values['connectpx_booking'] );
        }
    }

    /**
     * Get item data for cart.
     *
     * @param $other_data
     * @param $wc_item
     * @return array
     */
    public static function getItemData( $other_data, $wc_item )
    {
        if ( isset ( $wc_item['connectpx_booking'] ) ) {
            $userData = new Lib\UserBookingData();
            $info = array();
            $userData->fillData( $wc_item['connectpx_booking'] );
            if ( Lib\Config::useClientTimeZone() ) {
                $userData->applyTimeZone();
            }
            $userData->cart->setItemsData( $wc_item['connectpx_booking']['items'] );
            $cart_info = $userData->cart->getInfo();
            $cart_items = $userData->cart->getItems();
            $distanceMiles = Lib\Utils\Common::getDistanceInMiles( $userData->getRouteDistance() );
            $subService = $userData->getSubService();

            $count = 0;
            foreach ( $cart_items as $cart_item ) {
                $service = $cart_item->getService();
                $slot = $cart_item->getSlot();
                $appointment_pickup_client_dp = $appointment_return_pickup_client_dp = null;
                if ( $slot[0] !== null && $slot[1] !== null ) {
                    $appointment_pickup_client_dp = Lib\Slots\DatePoint::fromStr( $slot[0] . " " . $slot[1] )->toClientTz();
                }
                if ( $slot[0] !== null && $slot[2] !== null ) {
                    $appointment_return_pickup_client_dp = Lib\Slots\DatePoint::fromStr( $slot[0] . " " . $slot[2] )->toClientTz();
                }
                $isAfterHours = Lib\Utils\Common::isOffTimeService( $slot );

                $lineItems = $subService->paymentLineItems(
                    $distanceMiles,
                    0,
                    $isAfterHours
                );

                $lineItemsHtml = "";
                foreach ($lineItems['items'] as $lineItem) {
                    $lineItemsHtml .= $lineItem['qty'] > 1 ? sprintf("%s: %d &times; %s <br>", $lineItem['label'], $lineItem['qty'], Lib\Utils\Price::format($lineItem['unit_price'])) : sprintf("%s: %s <br>", $lineItem['label'], Lib\Utils\Price::format( $lineItem['total']));
                }
                $lineItemsHtml .= sprintf("<b>Subtotal:</b> %s <br>", Lib\Utils\Price::format( $lineItems['totals']));

                $codes = array(
                    'amount_to_pay' => Lib\Utils\Price::format( $cart_info->getPayNow() ),
                    'appointment_date' => $appointment_pickup_client_dp ? $appointment_pickup_client_dp->formatI18nDate() : __( 'N/A', 'connectpx_booking' ),
                    'appointment_pickup_time' => $appointment_pickup_client_dp ? $appointment_pickup_client_dp->formatI18nTime() : __( 'N/A', 'connectpx_booking' ),
                    'appointment_return_pickup_time' => $appointment_return_pickup_client_dp ? $appointment_return_pickup_client_dp->formatI18nTime() : __( 'N/A', 'connectpx_booking' ),
                    'sub_service_title' => $subService->getTitle(),
                    'distance_miles' => $subService->getMilesToCharge( $distanceMiles ),
                    'per_mile_price' => Lib\Utils\Price::format( $subService->getRatePerMile() ),
                    'flat_rate' => Lib\Utils\Price::format( $subService->getFlatRate() ),
                    'after_hours_fee' => Lib\Utils\Price::format( $subService->getAfterHoursFee() ),
                    'is_after_hours' => $isAfterHours,
                    'service_info' => $service ? $service->getDescription() : '',
                    'service_name' => $service ? $service->getTitle() : __( 'Service was not found', 'connectpx_booking' ),
                    'service_price' => $service ? Lib\Utils\Price::format( $cart_item->getServicePrice() ) : '',
                    'line_items' => $lineItemsHtml,
                );
                $info[] = Lib\Utils\Codes::replace(Lib\Utils\Common::getOption('wc_cart_item_data', ''), $codes, false );
                $count++;
            }

            $other_data[] = array(
                'name' => sprintf("<b>%s (%d):</b>", Lib\Utils\Common::getOption('wc_cart_item_title', 'Bookings'), $count),
                'value' => implode( PHP_EOL . PHP_EOL, $info ),
            );
        }

        return $other_data;
    }

    /**
     * Print appointment details inside order items in the backend.
     *
     * @param int $item_id
     */
    public static function orderItemMeta( $item_id )
    {
        $data = wc_get_order_item_meta( $item_id, 'connectpx_booking' );
        if ( $data ) {
            $other_data = self::getItemData( array(), array( 'connectpx_booking' => $data ) );
            echo '<br/>' . $other_data[0]['name'] . '<br/>' . nl2br( $other_data[0]['value'] );
        }
    }

    /**
     * Get cart item price.
     *
     * @param $product_price
     * @param $wc_item
     * @param $cart_item_key
     * @return mixed
     */
    public static function getCartItemPrice( $product_price, $wc_item, $cart_item_key )
    {
        if ( isset ( $wc_item['connectpx_booking'] ) ) {
            $userData = new Lib\UserBookingData();
            $userData->fillData( $wc_item['connectpx_booking'] );
            $userData->cart->setItemsData( $wc_item['connectpx_booking']['items'] );
            $cart_info = $userData->cart->getInfo();
            $product_price = wc_price( $cart_info->getPayNow() );
        }

        return $product_price;
    }

    /**
     * Check if service can be provided, check if staff or service not deleted
     *
     * @param Lib\UserBookingData $userData
     * @return bool
     */
    protected static function checkIfServiceIsProvided( Lib\UserBookingData $userData )
    {
        foreach ( $userData->cart->getItems() as $cart_item ) {
            if ( $cart_item->getService() === false ) {
                return false;
            }
        }

        return true;
    }

    public static function skipWoocommercePages(){
        // Redirect to checkout (when cart is not empty)
        if ( ! WC()->cart->is_empty() && is_cart() ) {
            wp_safe_redirect( wc_get_checkout_url() ); 
            exit();
        }

        // Redirect to shop if cart is empty
        elseif ( WC()->cart->is_empty() && is_cart() ) {
            wp_safe_redirect( wc_get_page_permalink( 'shop' ) );
            exit();
        }

        // Redirect to shop if cart is empty
        if ( is_shop() || is_product() || is_post_type_archive( 'product' ) || is_tax( get_object_taxonomies( 'product' ) ) ) {
            wp_safe_redirect( home_url( '/new-booking-new' ) );
            exit();
        }
    }

    public static function checkoutDetails() {
        foreach ( WC()->cart->get_cart() as $wc_key => $wc_item ) {
            if ( array_key_exists( 'connectpx_booking', $wc_item ) ) {
                $userData = new Lib\UserBookingData();
                $userData->fillData( $wc_item['connectpx_booking'] );
                $userData->cart->setItemsData( $wc_item['connectpx_booking']['items'] );
                self::renderTemplate( 'frontend/templates/checkout-details', array(
                    'userData' => $userData,
                ));
            }
        }
    }

    public static function removeCheckoutFields( $fields ) {
        if( !empty($fields['billing']) ) {
            foreach ($fields['billing'] as $key => $field) {
                $fields['billing'][$key]['default'] = self::checkoutValue(null, $key);
            }
        }
        // unset($fields['billing']['billing_first_name']);
        // unset($fields['billing']['billing_last_name']);
        // unset($fields['billing']['billing_email']);
        // unset($fields['billing']['billing_company']);
        // unset($fields['billing']['billing_address_1']);
        // unset($fields['billing']['billing_address_2']);
        // unset($fields['billing']['billing_city']);
        // unset($fields['billing']['billing_postcode']);
        // unset($fields['billing']['billing_country']);
        // unset($fields['billing']['billing_state']);
        // unset($fields['billing']['billing_phone']);

        // add_filter( 'woocommerce_enable_order_notes_field', '__return_false' );
        return $fields;
    }

    public static function removeExistingCartItems( $cart_item_data, $product_id, $variation_id ) {
        global $woocommerce;
        $woocommerce->cart->empty_cart();

        // Do nothing with the data and return
        return $cart_item_data;
    }

    public static function availablePaymentGateways( $available_gateways ) {
        global $woocommerce;
        if ( is_admin() ) return;

        $is_contract_customer = false;
        if ( get_current_user_id() ) {
            $customer = new Lib\Entities\Customer();
            if ( $customer->loadBy( array( 'wp_user_id' => get_current_user_id() ) ) ) {
                $is_contract_customer = $customer->isContractCustomer();
            }
        }

        foreach ( $available_gateways as $key => $gateway ) {
            if ( $is_contract_customer && $key == self::CONTRACT_CUSTOMER_PAYMENT_METHOD ) {
                continue;
            }
            else if ( ! $is_contract_customer && $key == self::PRIVATE_CUSTOMER_PAYMENT_METHOD ) {
                continue;
            }

            unset( $available_gateways[$key] );
        }

        return $available_gateways;
    }

    public static function orderButtonText( $button_text ) {
        return __('Complete Booking', 'connectpx_booking');
    }

    public static function orderReceivedText( $text, $order ) {
        return esc_html__('Thank you. Your request for ride is received.', 'connectpx_booking');
    }

    /**
     * Handle a refund via the edit order screen.
     *
     * @throws Exception To return errors.
     */
    public static function refundAppointments( $order_id, $refund_amount, $refund_reason = '' ) {
        $order      = wc_get_order( $order_id );
        if( $order->get_payment_method() != self::PRIVATE_CUSTOMER_PAYMENT_METHOD ) {
            return;
        }

        $response = self::refund_order([
            'order_id' => $order_id,
            'refund_amount' => $refund_amount,
            'refunded_amount' => $order->get_total_refunded(),
            'refund_reason' => $refund_reason ?: 'Cancelled bookings',
            'api_refund' => true,
        ]);

        return $response;
    }

    /**
     * Handle a refund via the edit order screen.
     *
     * @throws Exception To return errors.
     */
    public static function refund_order( $args ) {
        $order_id               = isset( $args['order_id'] ) ? absint( $args['order_id'] ) : 0;
        $refund_amount          = isset( $args['refund_amount'] ) ? wc_format_decimal( sanitize_text_field( wp_unslash( $args['refund_amount'] ) ), wc_get_price_decimals() ) : 0;
        $refunded_amount        = isset( $args['refunded_amount'] ) ? wc_format_decimal( sanitize_text_field( wp_unslash( $args['refunded_amount'] ) ), wc_get_price_decimals() ) : 0;
        $refund_reason          = isset( $args['refund_reason'] ) ? sanitize_text_field( wp_unslash( $args['refund_reason'] ) ) : '';
        $line_item_qtys         = isset( $args['line_item_qtys'] ) ? json_decode( sanitize_text_field( wp_unslash( $args['line_item_qtys'] ) ), true ) : array();
        $line_item_totals       = isset( $args['line_item_totals'] ) ? json_decode( sanitize_text_field( wp_unslash( $args['line_item_totals'] ) ), true ) : array();
        $line_item_tax_totals   = isset( $args['line_item_tax_totals'] ) ? json_decode( sanitize_text_field( wp_unslash( $args['line_item_tax_totals'] ) ), true ) : array();
        $api_refund             = isset( $args['api_refund'] ) && true === $args['api_refund'];
        $restock_refunded_items = isset( $args['restock_refunded_items'] ) && true === $args['restock_refunded_items'];
        $refund                 = false;
        $response               = array( 'success' => false );

        try {
            $order      = wc_get_order( $order_id );
            $max_refund = wc_format_decimal( $order->get_total() - $order->get_total_refunded(), wc_get_price_decimals() );

            if ( ( ! $refund_amount && ( wc_format_decimal( 0, wc_get_price_decimals() ) !== $refund_amount ) ) || $max_refund < $refund_amount || 0 > $refund_amount ) {
                throw new \Exception( __( 'Invalid refund amount', 'woocommerce' ) );
            }

            if ( wc_format_decimal( $order->get_total_refunded(), wc_get_price_decimals() ) !== $refunded_amount ) {
                throw new \Exception( __( 'Error processing refund. Please try again.', 'woocommerce' ) );
            }

            // Prepare line items which we are refunding.
            $line_items = array();
            $item_ids   = array_unique( array_merge( array_keys( $line_item_qtys ), array_keys( $line_item_totals ) ) );

            foreach ( $item_ids as $item_id ) {
                $line_items[ $item_id ] = array(
                    'qty'          => 0,
                    'refund_total' => 0,
                    'refund_tax'   => array(),
                );
            }
            foreach ( $line_item_qtys as $item_id => $qty ) {
                $line_items[ $item_id ]['qty'] = max( $qty, 0 );
            }
            foreach ( $line_item_totals as $item_id => $total ) {
                $line_items[ $item_id ]['refund_total'] = wc_format_decimal( $total );
            }
            foreach ( $line_item_tax_totals as $item_id => $tax_totals ) {
                $line_items[ $item_id ]['refund_tax'] = array_filter( array_map( 'wc_format_decimal', $tax_totals ) );
            }

            // Create the refund object.
            $refund = wc_create_refund(
                array(
                    'amount'         => $refund_amount,
                    'reason'         => $refund_reason,
                    'order_id'       => $order_id,
                    'line_items'     => $line_items,
                    'refund_payment' => $api_refund,
                    'restock_items'  => $restock_refunded_items,
                )
            );

            if ( is_wp_error( $refund ) ) {
                throw new \Exception( $refund->get_error_message() );
            }

            if ( did_action( 'woocommerce_order_fully_refunded' ) ) {
                $response['status'] = 'fully_refunded';
            }

            $response = array( 
                'success' => true,
            );
        } catch ( \Exception $e ) {
            $response = array( 
                'success' => false,
                'error' => $e->getMessage() 
            );
        }

        return $response;
    }
}