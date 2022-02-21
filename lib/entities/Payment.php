<?php
namespace ConnectpxBooking\Lib\Entities;

use ConnectpxBooking\Lib;

/**
 * Class Payment
 * @package ConnectpxBooking\Lib\Entities
 */
class Payment extends Lib\Base\Entity
{
    const TYPE_LOCAL        = 'local';
    const TYPE_FREE         = 'free';
    const TYPE_WOOCOMMERCE  = 'woocommerce';

    const STATUS_COMPLETED  = 'completed';
    const STATUS_PENDING    = 'pending';
    const STATUS_REJECTED   = 'rejected';

    /** @var string */
    protected $type;
    /** @var float */
    protected $total;
    /** @var string */
    protected $status = self::STATUS_COMPLETED;
    /** @var string */
    protected $details;
    /** @var string */
    protected $created_at;
    /** @var string */
    protected $updated_at;

    protected static $table = 'connectpx_booking_payments';

    protected static $schema = array(
        'id'          => array( 'format' => '%d' ),
        'type'        => array( 'format' => '%s' ),
        'total'       => array( 'format' => '%f' ),
        'status'      => array( 'format' => '%s' ),
        'details'     => array( 'format' => '%s' ),
        'created_at'  => array( 'format' => '%s' ),
        'updated_at'  => array( 'format' => '%s' ),
    );

    /**
     * Get display name for given payment type.
     *
     * @param string $type
     * @return string
     */
    public static function typeToString( $type )
    {
        switch ( $type ) {
            case self::TYPE_LOCAL:        return __( 'Local', 'connectpx_booking' );
            case self::TYPE_FREE:         return __( 'Free', 'connectpx_booking' );
            case self::TYPE_WOOCOMMERCE:  return 'WooCommerce';
            default:                      return '';
        }
    }

    /**
     * Get status of payment.
     *
     * @param string $status
     * @return string
     */
    public static function statusToString( $status )
    {
        switch ( $status ) {
            case self::STATUS_COMPLETED:  return __( 'Completed', 'connectpx_booking' );
            case self::STATUS_PENDING:    return __( 'Pending',   'connectpx_booking' );
            case self::STATUS_REJECTED:   return __( 'Rejected',  'connectpx_booking' );
            default:                      return '';
        }
    }

    /**
     * @param Entities\Appointment $appointment
     * @param Lib\CartInfo $cart_info
     * @param array $extra
     * @return $this
     */
    public function setDetailsFromAppointment( Appointment $appointment, Lib\CartInfo $cart_info, $extra = array() )
    {
        $details = array(
            'items' => array(),
            'subtotal' => array( 'price' => 0, 'deposit' => 0 ),
            'customer' => $appointment->getCustomer()->getFullName(),
        );

        $details['items'][] = array(
            'appointment_id'    => $appointment->getId(),
            'appointment_date'  => $appointment->getPickupDateTime(),
            'service_name'      => $appointment->getService()->getTitle(),
            'service_price'     => $appointment->getServicePrice(),
            'extras'            => $extras,
        );

        $this->details = json_encode( $details );

        return $this;
    }

    /**
     * Payment data for rendering payment details and invoice.
     *
     * @return array
     */
    public function getPaymentData()
    {
        $customer = Lib\Entities\Customer::query( 'c' )
            ->select( 'c.full_name' )
            ->leftJoin( 'Appointment', 'a', 'a.customer_id = c.id' )
            ->where( 'a.payment_id', $this->getId() )
            ->fetchRow();

        $details = json_decode( $this->getDetails(), true );

        return array(
            'payment' => array(
                'id' => (int) $this->id,
                'status' => $this->status,
                'type' => $this->type,
                'created_at' => $this->created_at,
                'customer' => empty ( $customer)  ? $details['customer'] : $customer['full_name'],
                'items' => $details['items'],
                'subtotal' => $details['subtotal'],
                'total' => $this->total,
            ),
            'adjustments' => isset( $details['adjustments'] ) ? $details['adjustments'] : array(),
        );
    }

    /**
     * Get HTML for payment info displayed in a popover in the edit appointment form
     *
     * @param float $paid
     * @param float $total
     * @param string $type
     * @param string $status
     * @return string
     */
    public static function paymentInfo( $paid, $total, $type, $status )
    {
        $result = Lib\Utils\Price::format( $paid );
        if ( $paid != $total ) {
            $result = sprintf( __( '%s of %s', 'connectpx_booking' ), $result, Lib\Utils\Price::format( $total ) );
        }
        $result .= sprintf(
            ' %s <span%s>%s</span>',
            self::typeToString( $type ),
            $status == self::STATUS_PENDING ? ' class="text-danger"' : '',
            self::statusToString( $status )
        );

        return $result;
    }

    /**************************************************************************
     * Entity Fields Getters & Setters                                        *
     **************************************************************************/

    /**
     * Gets details
     *
     * @return string
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * Sets details
     *
     * @param string $details
     * @return $this
     */
    public function setDetails( $details )
    {
        $this->details = $details;

        return $this;
    }

    /**
     * Gets type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets type
     *
     * @param string $type
     * @return $this
     */
    public function setType( $type )
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Gets total
     *
     * @return float
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Sets total
     *
     * @param float $total
     * @return $this
     */
    public function setTotal( $total )
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Gets status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets status
     *
     * @param string $status
     * @return $this
     */
    public function setStatus( $status )
    {
        $this->status = $status;

        return $this;
    }


    /**
     * Gets created_at
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Sets created_at
     *
     * @param string $created_at
     * @return $this
     */
    public function setCreatedAt( $created_at )
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * @param Lib\CartInfo $cart_info
     * @return $this
     */
    public function setCartInfo( Lib\CartInfo $cart_info )
    {
        $this
            ->setTotal( $cart_info->getTotal() );

        return $this;
    }

    /**
     * Gets updated_at
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * Sets updated_at
     *
     * @param string $updated_at
     * @return $this
     */
    public function setUpdatedAt( $updated_at )
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /**************************************************************************
     * Overridden Methods                                                     *
     **************************************************************************/

    public function save()
    {
        if ( $this->getId() == null ) {
            $this
                ->setCreatedAt( current_time( 'mysql' ) )
                ->setUpdatedAt( current_time( 'mysql' ) );
        } elseif ( $this->getModified() ){
            $this->setUpdatedAt( current_time( 'mysql' ) );
        }

        return parent::save();
    }
}