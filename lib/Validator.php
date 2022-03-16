<?php
namespace ConnectpxBooking\Lib;

use ConnectpxBooking\Lib\Notifications\Verification\Sender;

/**
 * Class Validator
 * @package ConnectpxBooking\Lib
 */
class Validator
{
    private $errors = array();

    /**
     * Validate email.
     *
     * @param string $field
     * @param array $data
     */
    public function validateEmail( $field, $data )
    {
        if ( $data['email'] == '' ) {
            $this->errors[ $field ] = __('Email is required', 'connectpx_booking');
        } else {
            if ( $data['email'] != '' && ! is_email( trim( $data['email'] ) ) ) {
                $this->errors[ $field ] = __( 'Invalid email', 'connectpx_booking' );
            }
            // Check email for uniqueness when a new WP account is going to be created.
            if ( ! get_current_user_id() ) {
                $customer = new Entities\Customer();
                // Try to find customer by phone or email.
                $customer->loadBy(array( 'email' => $data['email'] ));
                if ( ( ! $customer->isLoaded() || ! $customer->getWpUserId() ) && email_exists( $data['email'] ) ) {
                    $this->errors[ $field ] = __("This email is already registered.", 'connectpx_booking');
                }
            }
        }
    }

    /**
     * @param string $field_name
     * @param string $value
     * @param bool $required
     */
    public function validateRequired( $field_name, $value, $required = false )
    {
        $value = trim( $value );
        if ( empty( $value ) && $required ) {
            $this->errors[ $field_name ] = __( sprintf('%s is required.', ucwords(str_replace("_", " ", $field_name))), 'connectpx_booking' );;
        }
    }

    /**
     * Validate cart.
     *
     * @param array $cart
     * @param int $form_id
     */
    public function validatePrivateCustomerFields( $customer, $field_name, $field_value )
    {
        if( ! $customer->isContractCustomer() ) {
            $this->validateRequired( $field_name, $field_value, true );
        }
    }

    /**
     * @param string $field_name
     * @param string $value
     * @param bool $required
     */
    public function validateDistance( $field_name, $value, $required = false )
    {
        $value = trim( $value );
        if ( empty( $value ) && $required ) {
            $this->errors[ $field_name ] = __( sprintf('Please select a valid route.', ucwords(str_replace("_", " ", $field_name))), 'connectpx_booking' );;
        }
    }

    /**
     * @param string $field_name
     * @param string $value
     * @param bool $required
     */
    public function validateRouteAddress( $field_name, $value, $required = false )
    {
        $value = json_decode($value, true);
        $value = array_map('trim', $value);
        if ( empty( $value ) && $required ) {
            $this->errors[ $field_name ] = __( sprintf('%s is required.', ucwords(str_replace("_", " ", $field_name))), 'connectpx_booking' );;
        }
        else if ( $required ) {
            foreach (['country', 'city', 'state', 'address', 'lat', 'lng'] as $field) {
                if(empty($value[$field])) {
                    $this->errors[ $field_name ] = __( sprintf('%s in address is missing.', ucwords(str_replace("_", " ", $field))), 'connectpx_booking' );;
                    break;
                }
            }
        }
    }

    /**
     * @param string $field_name
     * @param string $value
     * @param bool $required
     */
    public function validateAddress( $field_name, $value, $required = false )
    {
        $value = trim( $value );
        if ( empty( $value ) && $required ) {
            $this->errors[ $field_name ] = __( sprintf('%s is required.', ucwords(str_replace("_", " ", $field_name))), 'connectpx_booking' );;
        }
    }

    /**
     * Validate phone.
     *
     * @param string $field
     * @param string $phone
     * @param bool $required
     */
    public function validatePhone( $field, $phone, $required = false )
    {
        if ( $phone == '' && $required ) {
            $this->errors[ $field ] = __('Phone is required', 'connectpx_booking');
        }
    }

    /**
     * Validate name.
     *
     * @param string $field
     * @param string $name
     */
    public function validateName( $field, $name )
    {
        if ( $name != '' ) {
            $max_length = 255;
            if ( preg_match_all( '/./su', $name, $matches ) > $max_length ) {
                $this->errors[ $field ] = sprintf(
                    __( '"%s" is too long (%d characters max).', 'bookly' ),
                    $name,
                    $max_length
                );
            }
        } else {
            switch ( $field ) {
                case 'full_name' :
                    $this->errors[ $field ] = __("Name is required.", 'connectpx_booking');
                    break;
                case 'first_name' :
                    $this->errors[ $field ] = __("First Name is required.", 'connectpx_booking');
                    break;
                case 'last_name' :
                    $this->errors[ $field ] = __("Last Name is required.", 'connectpx_booking');
                    break;
            }
        }
    }

    /**
     * Validate number.
     *
     * @param string $field
     * @param mixed $number
     * @param bool $required
     */
    public function validateNumber( $field, $number, $required = false )
    {
        if ( $number != '' ) {
            if ( ! is_numeric( $number ) ) {
                $this->errors[ $field ] = __( 'Invalid number', 'bookly' );
            }
        } elseif ( $required ) {
            $this->errors[ $field ] = __( 'Required', 'bookly' );
        }
    }

    /**
     * Validate date.
     *
     * @param string $field
     * @param string $date
     * @param bool $required
     */
    public function validateDate( $field, $date, $required = false )
    {
        if ( $date != '' ) {
            if ( date_create( $date ) === false ) {
                $this->errors[ $field ] = __( 'Invalid date', 'bookly' );
            }
        } elseif ( $required ) {
            $this->errors[ $field ] = __( 'Required', 'bookly' );
        }
    }

    /**
     * Validate time.
     *
     * @param string $field
     * @param string $time
     * @param bool $required
     */
    public function validateTime( $field, $time, $required = false )
    {
        if ( $time != '' ) {
            if ( ! preg_match( '/^-?\d{2}:\d{2}$/', $time ) ) {
                $this->errors[ $field ] = __( 'Invalid time', 'bookly' );
            }
        } elseif ( $required ) {
            $this->errors[ $field ] = __( 'Required', 'bookly' );
        }
    }

    /**
     * Post-validate customer.
     *
     * @param array $data
     * @param UserBookingData $userData
     */
    public function postValidateCustomer( $data, UserBookingData $userData )
    {
        if ( empty ( $this->errors ) ) {
            $user_id  = get_current_user_id();
            $customer = new Entities\Customer();
            if ( $user_id > 0 ) {
                // Try to find customer by WP user ID.
                $customer->loadBy( array( 'wp_user_id' => $user_id ) );
            }
            $verify_customer_details = get_option( 'bookly_cst_verify_customer_details', false );
            if ( ! $customer->isLoaded() ) {
                if ( ! $customer->isLoaded() ) {
                    // Try to find customer by 'primary' identifier.
                    $identifier = 'email';
                    if ( $data[ $identifier ] !== '' ) {
                        $customer->loadBy( array( $identifier => $data[ $identifier ] ) );
                    }
                    if ( Config::allowDuplicates() ) {
                        if ( Config::showFirstLastName() ) {
                            $customer_data = array(
                                'first_name' => $data['first_name'],
                                'last_name'  => $data['last_name'],
                            );
                        } else {
                            $customer_data = array( 'full_name' => $data['full_name'] );
                        }
                        if ( $data['email'] != '' ) {
                            $customer_data['email'] = $data['email'];
                        }
                        if ( $data['phone'] != '' ) {
                            $customer_data['phone'] = $data['phone'];
                        }
                        $customer->loadBy( $customer_data );
                    } elseif ( $customer->isLoaded() ) {
                        // Find difference between new and existing data.
                        $diff = array();
                        $fields = array(
                            'phone' => Utils\Common::getTranslatedOption( 'bookly_l10n_label_phone' ),
                            'email' => Utils\Common::getTranslatedOption( 'bookly_l10n_label_email' ),
                        );
                        $current = $customer->getFields();
                        if ( Config::showFirstLastName() ) {
                            $fields['first_name'] = Utils\Common::getTranslatedOption( 'bookly_l10n_label_first_name' );
                            $fields['last_name'] = Utils\Common::getTranslatedOption( 'bookly_l10n_label_last_name' );
                        } else {
                            $fields['full_name'] = Utils\Common::getTranslatedOption( 'bookly_l10n_label_name' );
                        }
                        foreach ( $fields as $field => $name ) {
                            if (
                                $data[ $field ] != '' &&
                                $current[ $field ] != '' &&
                                $data[ $field ] != $current[ $field ]
                            ) {
                                $diff[] = $name;
                            }
                        }
                        if ( ! empty ( $diff ) ) {
                            if ( $verify_customer_details === 'on_update' && $data['verification_code'] != $userData->getVerificationCode() ) {
                                $this->errors['verify'] = $identifier;
                            } else {
                                $this->errors['customer'] = sprintf(
                                    __( 'Your %s: %s is already associated with another %s.<br/>Press Update if we should update your user data, or press Cancel to edit entered data.', 'bookly' ),
                                    $fields[ $identifier ],
                                    $data[ $identifier ],
                                    implode( ', ', $diff )
                                );
                            }
                        }
                    }
                }
            }
            // Add customer name, email and phone to send notification
            if ( ! $customer->isLoaded() ) {
                if ( Config::showFirstLastName() ) {
                    $customer->setFirstName( $data['first_name'] );
                    $customer->setLastName( $data['last_name'] );
                } else {
                    $customer->setFullName( $data['full_name'] );
                }
                $customer->setEmail( $data['email'] );
                $customer->setPhone( $data['phone'] );
            }

            // Verify customer details
            if ( in_array( $verify_customer_details, array( 'always_phone', 'always_email' ) ) && $data['verification_code'] != $userData->getVerificationCode() ) {
                $this->errors['verify'] = $verify_customer_details === 'always_phone' ? 'phone' : 'email';
            }

            // Send message with verification code
            if ( isset( $this->errors['verify'] ) ) {
                $recipient = $this->errors['verify'] == 'phone' ? $customer->getPhone() : $customer->getEmail();
                $this->errors['verify_text'] = $this->errors['verify'] == 'phone' ? __( 'Enter verification code from SMS', 'bookly' ) : __( 'Enter verification code from email', 'bookly' );
                if ( $userData->getVerificationCodeSent() !== $recipient ) {
                    Sender::send( $customer, $userData->getVerificationCode(), $this->errors['verify'] );
                    $userData->setVerificationCodeSent( $recipient );
                }
            }

            // Check "skip payment" custom groups settings
            if ( Proxy\CustomerGroups::getSkipPayment( $customer ) ) {
                $this->errors['group_skip_payment'] = true;
            }
            // Check appointments limit
            $data = array();
            foreach ( $userData->cart->getItems() as $cart_item ) {
                if ( $cart_item->toBePutOnWaitingList() ) {
                    // Skip waiting list items.
                    continue;
                }

                $service = $cart_item->getService();
                $slots   = $cart_item->getSlots();

                $data[ $service->getId() ]['service'] = $service;
                $data[ $service->getId() ]['dates'][] = $slots[0][2];
            }
            foreach ( $data as $service_data ) {
                if ( $service_data['service']->appointmentsLimitReached( $customer->getId(), $service_data['dates'] ) ) {
                    $this->errors['appointments_limit_reached'] = true;
                    break;
                }
            }
        }
    }

    /**
     * Validate info fields.
     *
     * @param array $info_fields
     */
    public function validateInfoFields( array $info_fields )
    {
        $this->errors = Proxy\CustomerInformation::validate( $this->errors, $info_fields );
    }

    /**
     * Validate cart.
     *
     * @param array $cart
     * @param int $form_id
     */
    public function validateCart( $cart )
    {
        // foreach ( $cart as $cart_key => $cart_parameters ) {
        //     foreach ( $cart_parameters as $parameter => $value ) {
        //         switch ( $parameter ) {
        //             case 'custom_fields':
        //                 $this->errors = Proxy\CustomFields::validate( $this->errors, $value, $form_id, $cart_key );
        //                 break;
        //         }
        //     }
        // }
    }

    /**
     * Validate cart.
     *
     * @param array $cart
     * @param int $form_id
     */
    public function validateService( $service )
    {
        if( ! $service ) {
            $this->errors['service_id'] = __('This service is not available. Please contact service provide.', 'connectpx_booking');
            return;
        }
    }

    /**
     * Validate cart.
     *
     * @param array $cart
     * @param int $form_id
     */
    public function validateSubServices( $customer, $service, $sub_service_key )
    {
        if( ! $service || !$service->isLoaded() ) {
            $this->errors['sub_service_error'] = __('This service is not available. Please contact service provide.', 'connectpx_booking');
            return;
        }

        if( $customer->isContractCustomer() ) {
            $subService = $customer->loadSubService( $service->getId(), $sub_service_key );
        } else {
            $subService = $service->loadSubService( $sub_service_key );
        }

        if( ! $subService || $subService->getFlatRate() <= 0 || ! $subService->isEnabled() ) {
            $this->errors['sub_service_error'] = __('This service is not configured for customer. Please contact service provide.', 'connectpx_booking');
        }
    }

    /**
     * Get errors.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}