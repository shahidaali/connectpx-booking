<?php
namespace ConnectpxBooking\Backend\Components\Dialogs\Notifications;

use ConnectpxBooking\Lib;
use ConnectpxBooking\Backend\Modules\Lib\NotificationCodes;
use ConnectpxBooking\Lib\Entities\Notification;

/**
 * Class Dialog
 * @package ConnectpxBooking\Backend\Components\Dialogs\Notifications
 */
class Dialog extends Lib\Base\Component
{
    /**
     * Render payment details dialog.
     */
    public static function render()
    {
        // Add WP media button in tiny
        wp_enqueue_media();
        add_filter( 'mce_buttons', function ( $buttons ) {
            $mce_buttons = array(
                'array_unshift' => array( 'fontsizeselect', 'fontselect', ),
                'array_push' => array( 'wp_add_media', ),
            );

            foreach ( $mce_buttons as $method => $tools ) {
                foreach ( $tools as $tool ) {
                    if ( ! in_array( $tool, $buttons ) ) {
                        $method( $buttons, $tool );
                    }
                }
            }

            return $buttons;
        }, 10, 1 );

        add_filter( 'mce_buttons_2', function ( $buttons ) {
            $mce_buttons = array( 'backcolor', 'styleselect', );
            foreach ( $mce_buttons as $tool ) {
                if ( ! in_array( $tool, $buttons ) ) {
                    $buttons[] = $tool;
                }
            }

            return $buttons;
        }, 10, 1 );

        $codes = new NotificationCodes( 'email' );
        $codes_list = array();
        foreach ( Notification::getTypes() as $notification_type ) {
            $codes_list[ $notification_type ] = $codes->getCodes( $notification_type );
        }
        
        wp_enqueue_script( 'connectpx_booking_notification_dialog' );
        wp_localize_script( 'connectpx_booking_notification_dialog', 'ConnectpxBookingNotificationDialogL10n', array(
            'defaultNotification' => self::getDefaultNotification(),
            'codes' => $codes_list,
            'title' => array(
                'container' => __( 'Email', 'connectpx_booking' ),
                'new' => __( 'New email notification', 'connectpx_booking' ),
                'edit' => __( 'Edit email notification', 'connectpx_booking' ),
                'create' => __( 'Create notification', 'connectpx_booking' ),
                'save' => __( 'Save notification', 'connectpx_booking' ),
            ),
        ) );

        self::renderTemplate( 'backend/components/dialogs/notifications/templates/dialog', array( 'self' => __CLASS__, 'gateway' => 'email' ) );
    }

    public static function renderNewNotificationButton()
    {
        print '<div class="col-auto">';
        Buttons::renderAdd( 'connectpx_booking-js-new-notification', 'btn-success', __( 'New notification', 'connectpx_booking' ) );
        print '</div>';
    }

    /**
     * @return array
     */
    protected static function getDefaultNotification()
    {
        return array(
            'type' => Lib\Entities\Notification::TYPE_NEW_BOOKING,
            'active' => 1,
            'attach_ics' => 0,
            'attach_invoice' => 0,
            'message' => '',
            'name' => '',
            'subject' => '',
            'to_admin' => 0,
            'to_customer' => 1,
            'to_staff' => 0,
            'settings' => Lib\DataHolders\Notification\Settings::getDefault(),
        );
    }
}