<?php
namespace ConnectpxBooking\Backend\Modules\Forms;

use ConnectpxBooking\Lib;

/**
 * Class Service
 * @method Lib\Entities\Service getObject
 *
 * @package ConnectpxBooking\Backend\Modules\Forms
 */
class Service extends Lib\Base\Form
{
    protected static $entity_class = 'Service';

    public function configure()
    {
        $fields = array(
            'title',
            'description',
            'sub_services',
        );

        $this->setFields( $fields );
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

    /**
     * @return Lib\Entities\Service
     */
    public function save()
    {
        return parent::save();
    }

}