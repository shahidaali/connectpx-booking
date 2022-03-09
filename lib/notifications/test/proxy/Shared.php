<?php
namespace ConnectpxBooking\Lib\Notifications\Test\Proxy;

use ConnectpxBooking\Lib;

/**
 * Class Shared
 * @package ConnectpxBooking\Lib\Notifications\Test\Proxy
 *
 * @method static void send( string $to_email, Lib\Entities\Notification $notification, $codes, $attachments, $reply_to, string $send_as, $from)
 */
abstract class Shared extends Lib\Base\Proxy
{

}