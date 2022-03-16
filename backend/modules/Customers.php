<?php
namespace ConnectpxBooking\Backend\Modules;

use ConnectpxBooking\Lib;
use ConnectpxBooking\Backend\Modules\Forms;
use ConnectpxBooking\Lib\Utils;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    ConnectpxBooking
 * @subpackage ConnectpxBooking\Backend\Modules
 * @author     Shahid Hussain <shahidhussainaali@gmail.com>
 */
class Customers extends Lib\Base\Component {
    protected static $pageSlug = 'connectpx_booking_customers';

	/**
     * Render page.
     */
    public static function render()
    {
        $params = [
            'tpl' => '',
            'params' => [],
        ];
        switch ( self::parameter('tab') ){ 
            case 'add':
                self::save();
                $params['params']['wp_users'] = self::getWpUsers();
                $params['params']['services'] = self::getServices();
                $params['tpl'] = 'backend/templates/partials/customers/add';
                break;

            case 'edit':
                $customer = static::getEditEntity();
                if( ! $customer ) {
                    echo __('Customer not found.');
                    return;
                }

                self::save();
                $params['params']['wp_users'] = self::getWpUsers();
                $params['params']['services'] = self::getServices();
                $params['params']['customer'] = $customer;
                $params['tpl'] = 'backend/templates/partials/customers/add';
                break;
            
            default:
                $query = Lib\Entities\Customer::query( 'c' )
                    ->select( 'c.*' );
                $total = $query->count();

                $params['params']['customers'] = $query->fetchArray();
                $params['params']['total'] = $total;
                $params['tpl'] = 'backend/templates/partials/customers/list';
                break;
        }

        self::renderTemplate( 'backend/templates/customers',  $params);
    }

    /**
     * Register settings page
     *
     * @since    1.0.0
     */
    public static function save() {
        if ( ! isset( $_POST['connectpx_save_customer'] ) ) {
            return;
        }

        $customer = self::getEditEntity();

        $errors = [];

        $requiredFields = [
            'first_name',
            'last_name',
            'phone',
            'email',
            'country',
            'state',
            'postcode',
            'city',
            'street',
            'street_number',
        ];

        foreach ($requiredFields as $field) {
            if( empty($_POST[$field]) ) {
                $errors[] = sprintf(__("%s field is required."), ucwords(str_replace("_", " ", $field)));
            }
        }

        if( !empty($errors) ) {
            Utils\Session::set_flash($errors, 'error');
            return;
        }

        $email = $_POST['email'];

        if ( !is_email( $email ) ) {
            Utils\Session::set_flash(__('Email is not valid.'), 'error');
            return;
        }

        $check_existing = self::isAdd() || ( self::isEdit() && $customer->getEmail() != $email );
        if( $check_existing && email_exists($email) ) {
            Utils\Session::set_flash(__('Email already registered.'), 'error');
            return;
        }

        $wp_user = $_POST['wp_user'] ?? '';
        $password = $_POST['account_password'] ?? '';
        $password = trim($password);

        if( $wp_user == 'create_new' && empty($password) ) {
            Utils\Session::set_flash(__('Please enter password for account.'), 'error');
            return;
        }

        $form = new Forms\Customer();
        $form->bind( self::postParameters() );
        $customer = $form->save();

        if( $customer ) {

            // Create wordpress user
            if( $wp_user == 'create_new' ) {
                $customer = Utils\Common::createWPUserByCustomer($customer, $password);

                if( $wp_user = $customer->getWpUser() ) {
                    Lib\Notifications\Customer\Sender::send( $customer, $customer->getCustomerEmail(), $password );
                }
            }

            // Update wp password
            if( self::isEdit() && $customer->getWpUserId() && $password ) {
                wp_set_password( $password, $customer->getWpUserId() ); 
            }

            Utils\Session::set_flash(__( 'Customer saved' ), 'success');
            wp_safe_redirect(self::escAdminUrl(self::pageSlug(), [
                'tab' => 'edit',
                'id' => $customer->getId(),
            ]));
            exit;
        } else {
            Utils\Session::set_flash(__( 'Error saving customer.' ), 'success');
        }
    }

    public static function isEdit() {
        if( self::parameter('tab') == 'edit' ) {
            return true;
        }
    }

    public static function isAdd() {
        if( self::parameter('tab') == 'add' ) {
            return true;
        }
    }

    public static function getEditEntity() {
        $customer = null;
        if(self::parameter('tab') == 'edit' && self::parameter('id')) {
            $customer = Lib\Entities\Customer::find(self::parameter('id'));
            if( $customer ) {
                return $customer;
            }
        }
    }

    public static function getWpUsers() {
        $args = array(
            'role'    => 'customer',
            'orderby' => 'user_nicename',
            'order'   => 'ASC'
        );

        $users = get_users( $args );

        $user_list = [];
        foreach ($users as $key => $user) {
            $user_list[$user->ID] = $user->display_name;
        }
        return $user_list;
    }

    public static function getServices() {
        return $query = Lib\Entities\Service::query( 's' )
                    ->select( 's.*' )
                    ->fetchArray();
    }
}
