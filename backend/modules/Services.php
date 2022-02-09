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
class Services extends Lib\Base\Component {
    protected static $pageSlug = 'connectpx_booking_services';

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
                $params['tpl'] = 'backend/templates/partials/services/add';
                break;

            case 'edit':
                $service = static::getEditEntity();
                if( ! $service ) {
                    echo __('Service not found.');
                    return;
                }

                self::save();
                $params['params']['service'] = $service;
                $params['tpl'] = 'backend/templates/partials/services/add';
                break;
            
            default:
                $query = Lib\Entities\Service::query( 's' )
                    ->select( 's.*' );
                $total = $query->count();

                $params['params']['services'] = $query->fetchArray();
                $params['params']['total'] = $total;
                $params['tpl'] = 'backend/templates/partials/services/list';
                break;
        }

        self::renderTemplate( 'backend/templates/services',  $params);
    }

    /**
     * Register settings page
     *
     * @since    1.0.0
     */
    public static function save() {
        if ( ! isset( $_POST['connectpx_save_service'] ) ) {
            return;
        }

        $form = new Forms\Service();
        $form->bind( self::postParameters() );
        $service = $form->save();
        if( $service ) {
            Utils\Session::set_flash(__( 'Service saved' ), 'success');

            wp_safe_redirect(self::escAdminUrl(self::pageSlug(), [
                'tab' => 'edit',
                'id' => $service->getId(),
            ]));
            exit;
        } else {
            Utils\Session::set_flash(__( 'Error saving service.' ), 'success');
        }
    }

    public static function isEdit() {
        if( self::parameter('tab') == 'edit' ) {
            return true;
        }
    }

    public static function getEditEntity() {
        $service = null;
        if(self::parameter('tab') == 'edit' && self::parameter('id')) {
            $service = Lib\Entities\Service::find(self::parameter('id'));
            if( $service ) {
                return $service;
            }
        }
    }
}
