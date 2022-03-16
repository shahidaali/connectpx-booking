<?php
namespace ConnectpxBooking\Lib\Notifications\Base;

use ConnectpxBooking\Lib\Entities\Customer;
use ConnectpxBooking\Lib\Entities\Notification;
use ConnectpxBooking\Lib\Notifications\Assets\Base\Attachments;
use ConnectpxBooking\Lib\Notifications\Assets\Base\Codes;
use ConnectpxBooking\Lib\Utils;

/**
 * Class Reminder
 * @package ConnectpxBooking\Lib\Notifications\Base
 */
abstract class Reminder
{
    const RECIPIENT_ADMINS = 'admins';
    const RECIPIENT_CLIENT = 'client';

    const SEND_AS_HTML = 'html';
    const SEND_AS_TEXT = 'text';

    /**
     * Send notification to administrators.
     *
     * @param Notification $notification
     * @param Codes $codes
     * @param Attachments $attachments
     * @param array $reply_to
     * @param array|bool $queue
     * @return bool
     */
    public static function sendToAdmins( Notification $notification, Codes $codes, $attachments = null, $reply_to = null, &$queue = false )
    {
        if ( ! $notification->getToAdmin() ) {
            // No recipient.
            return false;
        }

        if ( $notification->getGateway() == 'sms' ) {
            return static::_sendSmsTo(
                self::RECIPIENT_ADMINS,
                get_option( 'connectpx_booking_sms_administrator_phone', '' ),
                $notification,
                $codes,
                array( 'name' => __( 'Admins', 'connectpx_booking' ) ),
                $queue
            );
        } else {
            $result = false;
            foreach ( Utils\Common::getAdminEmails() as $email ) {
                if ( static::_sendEmailTo(
                        self::RECIPIENT_ADMINS,
                        $email,
                        $notification,
                        $codes,
                        $attachments,
                        $reply_to,
                        null,
                        null,
                        array( 'name' => __( 'Admins', 'connectpx_booking' ) ),
                        $queue
                ) ) {
                    $result = true;
                }
            }

            return $result;
        }
    }

    /**
     * Send notification to custom recipients
     *
     * @param Notification $notification
     * @param Codes $codes
     * @param Attachments $attachments
     * @param array $reply_to
     * @param array|bool $queue
     * @return bool
     */
    public static function sendToCustom( Notification $notification, Codes $codes, $attachments = null, $reply_to = null, &$queue = false )
    {
        $result = false;
        if ( ! $notification->getToCustom() ) {
            // No recipient.
            return $result;
        }
        if ( $notification->getGateway() == 'sms' ) {
            foreach ( array_map( 'trim', array_filter( explode( "\n", $notification->getCustomRecipients() ), 'trim' ) ) as $phone ) {
                if ( static::_sendSmsTo(
                    self::RECIPIENT_ADMINS,
                    $phone,
                    $notification,
                    $codes,
                    array( 'name' => __( 'Custom', 'connectpx_booking' ) ),
                    $queue
                ) ) {
                    $result = true;
                }
            }
        } else {
            foreach ( array_map( 'trim', array_filter( explode( "\n", $notification->getCustomRecipients() ), 'trim' ) ) as $email ) {
                if ( static::_sendEmailTo(
                    self::RECIPIENT_ADMINS,
                    $email,
                    $notification,
                    $codes,
                    $attachments,
                    $reply_to,
                    null,
                    null,
                    array( 'name' => __( 'Custom', 'connectpx_booking' ) ),
                    $queue
                ) ) {
                    $result = true;
                }
            }
        }
        return $result;
    }

    /**
     * Send notification to client.
     *
     * @param Customer $customer
     * @param Notification $notification
     * @param Codes $codes
     * @param Attachments $attachments
     * @param bool|array $queue
     * @return bool
     */
    public static function sendToClient( Customer $customer, Notification $notification, Codes $codes, $attachments = null, &$queue = false )
    {
        if ( ! $notification->getToCustomer() ) {
            // No recipient.
            return false;
        }

        if ( $notification->getGateway() == 'sms' ) {
            return static::_sendSmsTo(
                self::RECIPIENT_CLIENT,
                $customer->getPhone(),
                $notification,
                $codes,
                array( 'name' => $customer->getFullName() ),
                $queue
            );
        } else {
            return static::_sendEmailTo(
                self::RECIPIENT_CLIENT,
                $customer->getEmail(),
                $notification,
                $codes,
                $attachments,
                null,
                null,
                null,
                array( 'name' => $customer->getFullName() ),
                $queue
            );
        }
    }

    /**
     * Send email.
     *
     * @param string $recipient
     * @param string|array $to_email
     * @param Notification $notification
     * @param Codes $codes,
     * @param Attachments $attachments
     * @param array $reply_to
     * @param string $force_send_as
     * @param array $force_from
     * @param array $queue_data
     * @param bool|array $queue
     * @return bool
     */
    protected static function _sendEmailTo(
        $recipient,
        $to_email,
        Notification $notification,
        Codes $codes,
        $attachments = null,
        $reply_to = null,
        $force_send_as = null,
        $force_from = null,
        $queue_data = array(),
        &$queue = false
    )
    {
        if ( empty ( $to_email ) ) {
            return false;
        }

        $send_as = $force_send_as ?: self::SEND_AS_HTML;
        $from    = $force_from    ?: array(
            'name'  => Utils\Common::getOption('email_sender_name'),
            'email' => Utils\Common::getOption('email_sender'),
        );

        // Subject & message.
        $subject = $notification->getSubject();
        $message = $notification->getMessage();
        
        $subject = $codes->replace( $subject );
        if ( $send_as == self::SEND_AS_HTML ) {
            $message = wpautop( $codes->replace( $message, 'html' ) );
        } else {
            $message = $codes->replace( $message );
        }

        // Headers.
        $headers = array();
        $headers[] = strtr( 'Content-Type: content_type; charset=utf-8', array(
            'content_type' => $send_as == self::SEND_AS_HTML ? 'text/html' : 'text/plain'
        ) );
        $headers[] = strtr( 'From: name <email>', $from );
        if ( isset ( $reply_to ) ) {
            $headers[] = strtr( 'Reply-To: name <email>', $reply_to );
        }

        // Do send.
        if ( $queue !== false ) {
            $queue[] = array(
                'data' => $queue_data,
                'gateway' => $notification->getGateway(),
                'name' => $notification->getName(),
                'address' => $to_email,
                'subject' => $subject,
                'message' => $message,
                'headers' => $headers,
                'type_id' => $notification->getTypeId(),
                'attachments' => $attachments ? $attachments->createFor( $notification ) : array(),
            );

            return true;
        } else {
            // print_r($message);
            // exit();
            Utils\Common::logEmail( $to_email, $subject, $message, $headers, $attachments ? $attachments->createFor( $notification ) : array(), $notification->getTypeId() );
            return wp_mail( $to_email, $subject, $message, $headers, $attachments ? $attachments->createFor( $notification ) : array() );
        }
    }

    /**
     * Send SMS.
     *
     * @param string $recipient
     * @param string $phone
     * @param Notification $notification
     * @param Codes $codes
     * @param array $queue_data,
     * @param array|bool $queue
     * @return bool
     */
    protected static function _sendSmsTo( $recipient, $phone, $notification, Codes $codes, $queue_data = array(), &$queue = false )
    {
        if ( get_option( 'connectpx_booking_cloud_token' ) == '' || $phone == '' || ! Cloud\API::getInstance()->account->productActive( 'sms' ) ) {
            return false;
        }

        // Message.
        if ( $recipient == self::RECIPIENT_CLIENT ) {
            $message = $notification->getTranslatedMessage();
        } else {
            $message = Proxy\Pro::prepareNotificationMessage( $notification->getMessage(), $recipient, 'sms' );
        }
        $message = $codes->replaceForSms( $message );

        // Do send.
        if ( $queue !== false ) {
            $queue[] = array(
                'data'       => $queue_data,
                'gateway'    => $notification->getGateway(),
                'name'       => $notification->getName(),
                'address'    => $phone,
                'message'    => $message['personal'],
                'impersonal' => $message['impersonal'],
                'type_id'    => $notification->getTypeId(),
            );

            return true;
        } else {
            return Cloud\API::getInstance()->sms->sendSms( $phone, $message['personal'], $message['impersonal'], $notification->getTypeId() );
        }

    }
}