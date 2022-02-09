<?php
namespace ConnectpxBooking\Backend\Modules\Forms;

use ConnectpxBooking\Lib;

/**
 * Class Customer
 * @method Lib\Entities\Customer getObject
 *
 * @package ConnectpxBooking\Backend\Modules\Forms
 */
class Customer extends Lib\Base\Form
{
    protected static $entity_class = 'Customer';

    public function configure()
    {
        $this->setFields( array(
            'wp_user_id',
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
            'additional_address',
            'notes',
            'sub_services',
        ) );
    }

    /**
     * Bind values to form.
     *
     * @param array $params
     * @param array $files
     */
    public function bind( array $params, array $files = array() )
    {
        if( isset( $params['sub_services'] ) && is_array($params['sub_services']) ) {
            $params['sub_services'] = json_encode($params['sub_services']);
        } else {
            $params['sub_services'] = '[]';
        }

        parent::bind( $params, $files );
    }

}