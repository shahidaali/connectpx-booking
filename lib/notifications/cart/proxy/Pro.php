<?php
namespace ConnectpxBooking\Lib\Notifications\Cart\Proxy;

use ConnectpxBooking\Lib;
use ConnectpxBooking\Lib\DataHolders\Booking\Order;

/**
 * Class Pro
 * @package ConnectpxBooking\Lib\Notifications\Cart\Proxy
 *
 * @method static array|bool sendCombinedToClient( array|bool $queue, Order $order ) Send combined notifications to client.
 */
abstract class Pro extends Lib\Base\Proxy
{

}