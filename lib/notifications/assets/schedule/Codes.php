<?php
namespace ConnectpxBooking\Lib\Notifications\Assets\Schedule;

use ConnectpxBooking\Lib\Entities\Schedule;
use ConnectpxBooking\Lib\Notifications\Assets\Base;
use ConnectpxBooking\Lib\Utils;
use ConnectpxBooking\Lib\Notifications\Assets\Appointment\Codes as AppointmentCodes;
use ConnectpxBooking\Lib\Notifications\Assets\Customer\Codes as CustomerCodes;

/**
 * Class Codes
 * @package ConnectpxBooking\Lib\Notifications\Assets\Schedule
 */
class Codes extends Base\Codes
{
    // Core
    public $schedule_no;
    public $schedule_start_date;
    public $schedule_end_date;
    public $schedule_status;
    public $schedule_repeat_info;
    public $service_name;
    public $service_description;
    public $current_date_time;
    public $cancellation_reason;

    public $schedule;
    public $appointment;
    public $appointments;
    public $customer;
    public $service;

    /**
     * Constructor.
     *
     * @param Schedule $customer
     */
    public function __construct( Schedule $schedule, $appointments = [] )
    {
        $this->schedule = $schedule;
        $this->appointment = $schedule->loadFirstAppointment();
        $this->appointments = $appointments;
        $this->customer = $schedule->getCustomer();
        $this->service = $schedule->getService();

        $this->schedule_no = $schedule->getId();
        $this->schedule_start_date = $schedule->getStartDate();
        $this->schedule_end_date = $schedule->getEndDate();
        $this->schedule_status = $schedule->getStatus();
        $this->schedule_repeat_info = $schedule->getScheduleRepeatInfo();
        $this->service_name = $this->service->getTitle();
        $this->service_description = $this->service->getDescription();
        $this->current_date_time = new \DateTime();
    }

    /**
     * @inheritDoc
     */
    protected function getReplaceCodes( $format )
    {
        $replace_codes = parent::getReplaceCodes( $format );
        $replace_codes += (new AppointmentCodes($this->appointment))->getReplaceCodes( $format );
        $replace_codes += (new CustomerCodes($this->customer))->getReplaceCodes( $format );

        // Add replace codes.
        $replace_codes += array(
            'schedule_no' => $this->schedule_no,
            'schedule_start_date' => Utils\DateTime::formatDate( $this->schedule_start_date ),
            'schedule_end_date' => Utils\DateTime::formatDate( $this->schedule_end_date ),
            'schedule_status' => Schedule::statusToString( $this->schedule_status ),
            'schedule_repeat_info' => $this->schedule_repeat_info,
            'service_name' => $this->service_name,
            'service_description' => $this->service_description,
            'current_date_time' => Utils\DateTime::formatDate( $this->current_date_time->format('Y-m-d H:i:s') ),
            'cancellation_reason' => $this->cancellation_reason,
        );

        return $replace_codes;
    }
}