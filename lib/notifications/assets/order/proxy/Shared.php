<?php
namespace ConnectpxBooking\Lib\Notifications\Assets\Order\Proxy;

use ConnectpxBooking\Lib;
use ConnectpxBooking\Lib\Notifications\Assets\Order;

/**
 * Class Shared
 * @package ConnectpxBooking\Lib\Notifications\Assets\Order\Proxy
 *
 * @method static void  prepareCodes( Order\Codes $codes ) Prepare codes data for order.
 * @method static array prepareReplaceCodes( array $replace_codes, Order\Codes $codes, $format ) Prepare replacement codes for order.
 */
abstract class Shared extends Lib\Base\Proxy
{

}