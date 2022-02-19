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
            'services',
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
        if( isset( $params['services'] ) && is_array($params['services']) ) {
            $params['services'] = json_encode($params['services']);
        } else {
            $params['services'] = '[]';
        }

        parent::bind( $params, $files );
    }

}