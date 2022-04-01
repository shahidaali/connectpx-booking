<?php
namespace ConnectpxBooking\Lib\Notifications\Assets\Schedule;

use ConnectpxBooking\Lib\Entities\Schedule;
use ConnectpxBooking\Lib\Notifications\Assets\Base;
use ConnectpxBooking\Lib\Utils;

/**
 * Class Codes
 * @package ConnectpxBooking\Lib\Notifications\Assets\Schedule
 */
class Codes extends Base\Codes
{
    // Core
    public $schedule_start_date;
    public $schedule_end_date;
    public $schedule_status;

    public $schedule;

    /**
     * Constructor.
     *
     * @param Schedule $customer
     */
    public function __construct( Schedule $schedule )
    {
        $this->schedule = $schedule;

        $this->schedule_start_date = $schedule->getStartDate();
        $this->schedule_end_date = $schedule->getEndDate();
        $this->schedule_status = $schedule->getStatus();
    }

    /**
     * @inheritDoc
     */
    protected function getReplaceCodes( $format )
    {
        $replace_codes = parent::getReplaceCodes( $format );

        // Add replace codes.
        $replace_codes += array(
            'schedule_start_date' => Utils\DateTime::formatDate( $this->schedule_start_date ),
            'schedule_end_date' => Utils\DateTime::formatDate( $this->schedule_end_date ),
            'schedule_status' => Schedule::statusToString( $this->schedule_status ),
        );

        return $replace_codes;
    }
}