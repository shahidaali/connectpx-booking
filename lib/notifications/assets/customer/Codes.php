<?php
namespace ConnectpxBooking\Lib\Notifications\Assets\Customer;

use ConnectpxBooking\Lib\Entities\Customer;
use ConnectpxBooking\Lib\Notifications\Assets\Base;

/**
 * Class Codes
 * @package ConnectpxBooking\Lib\Notifications\Assets\Customer
 */
class Codes extends Base\Codes
{
    // Core
    public $client_address;
    public $client_email;
    public $client_first_name;
    public $client_last_name;
    public $client_name;
    public $client_note;
    public $client_phone;
    public $new_username;
    public $new_password;
    public $site_address;

    /**
     * Constructor.
     *
     * @param Customer $customer
     */
    public function __construct( Customer $customer, $username = null, $password = null )
    {
        $this->client_address = $customer->getAddress();
        $this->client_email = $customer->getEmail();
        $this->client_first_name = $customer->getFirstName();
        $this->client_last_name = $customer->getLastName();
        $this->client_name = $customer->getFullName();
        $this->client_phone = $customer->getPhone();
        $this->client_note = $customer->getNotes();

        $this->new_username = $username;
        $this->new_password = $password;
        $this->site_address = site_url();
    }

    /**
     * @inheritDoc
     */
    protected function getReplaceCodes( $format )
    {
        $replace_codes = parent::getReplaceCodes( $format );

        // Add replace codes.
        $replace_codes += array(
            'client_email' => $this->client_email,
            'client_address' => $format === 'html' ? nl2br( $this->client_address ) : $this->client_address,
            'client_name' => $this->client_name,
            'client_first_name' => $this->client_first_name,
            'client_last_name' => $this->client_last_name,
            'client_phone' => $this->client_phone,
            'client_note' => $this->client_note,
            'new_password' => $this->new_password,
            'new_username' => $this->new_username,
            'site_address' => $this->site_address,
        );

        return $replace_codes;
    }
}