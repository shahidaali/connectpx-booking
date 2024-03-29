<?php
namespace ConnectpxBooking\Lib\Slots;

use ConnectpxBooking\Lib;

/**
 * Class Finder
 * @package ConnectpxBooking\Lib\Slots
 */
class Finder
{
    // Input parameters.
    /** @var Lib\UserBookingData */
    protected $userData;
    /** @var array|null */
    protected $last_fetched_slot;
    /** @var string|null */
    protected $selected_date;

    // Configuration.
    /** @var int */
    protected $slot_length;
    /** @var bool */
    protected $srv_duration_as_slot_length;
    /** @var bool */
    protected $show_calendar;
    /** @var bool */
    protected $show_blocked_slots;
    /** @var bool */
    protected $waiting_list_enabled;
    /** @var array */
    protected $ignore_appointments = array();
    /** @var callable */
    protected $callback_group;
    /** @var callable */
    protected $callback_stop;

    // Data for generator.
    /** @var Staff[] */
    protected $staff = array();
    /** @var Schedule[] */
    protected $service_schedule = array();

    // Dates in WP time zone.
    /** @var DatePoint */
    public $start_dp;
    /** @var DatePoint */
    public $end_dp;

    // Dates in client time zone.
    /** @var DatePoint */
    public $client_start_dp;
    /** @var DatePoint */
    public $client_end_dp;

    // Result.
    /** @var array */
    protected $slots;
    /** @var bool */
    protected $has_more_slots;
    /** @var int */
    protected $srv_duration_days;

    /**
     * Constructor.
     *
     * @param Lib\UserBookingData $userData
     * @param callable $callback_group
     * @param callable $callback_stop
     * @param bool     $waiting_list_enabled
     * @param array    $ignore_appointments
     */
    public function __construct( Lib\UserBookingData $userData, $callback_group = null, $callback_stop = null, $waiting_list_enabled = null, $ignore_appointments = array() )
    {
        $this->userData = $userData;
        $this->slot_length = Lib\Config::getTimeSlotLength();
        $this->srv_duration_as_slot_length = Lib\Config::useServiceDurationAsSlotLength();
        $this->show_calendar = Lib\Config::showCalendar();
        $this->show_blocked_slots = Lib\Config::showBlockedTimeSlots();
        $this->waiting_list_enabled = false;
        $this->ignore_appointments = $ignore_appointments;

        // Prepare group callback.
        if ( is_callable( $callback_group ) ) {
            $this->callback_group = $callback_group;
        } else {
            $this->callback_group = array( $this, '_groupDefault' );
        }

        // Prepare stop callback.
        $this->callback_stop = array( $this, '_stopCalendar' );
    }

    /**
     * Prepare dates and staff data.
     *
     * @return $this
     */
    public function prepare()
    {
        $this->_prepareDates();
        $this->_prepareStaffData();

        return $this;
    }

    /**
     * Init slots generator.
     *
     * @return Generator
     */
    private function _generate()
    {
        $connection = Generator::CONNECTION_CONSECUTIVE;
        $generator = new Generator(
            $this->slot_length,
            300,
            0,
            0,
            1,
            0,
            $this->start_dp,
            $this->userData->getTimeFrom(),
            $this->userData->getTimeTo(),
            0,
            0,
            null,
            $connection
        );

        return $generator;
    }

    /**
     * Load and init.
     *
     * @param callable $callback_break
     */
    public function load( $callback_break = null )
    {
        $this->slots = array();
        $this->has_more_slots = false;

        // Prepare break callback.
        if ( ! is_callable( $callback_break ) ) {
            if ( Lib\Config::showSingleTimeSlot() ) {
                $callback_break = array( $this, '_breakOneSlot' );
            } else {
                $callback_break = array( $this, '_breakDefault' );
            }
        }

        // Do search.
        $slots_count = 0;
        $available_slots_count = 0;
        $do_break = false;
        $weekdays = $this->userData->getDays();
        $generator = $this->_generate();
        foreach ( $generator as $slots ) {
            // Workaround for PHP < 5.5.
            $dp = $generator->key();
            // For empty slots check client end date here.
            if ( call_user_func( $callback_break, $dp, $this->srv_duration_days, $slots_count, $available_slots_count ) ) {
                break;
            }
            foreach ( $slots->all() as $slot ) {
                if ( $do_break ) {
                    // Flag there are more slots.
                    $this->has_more_slots = true;
                    break 2;
                }
                /** @var DatePoint $client_dp */
                $client_dp = $slot->start()->toClientTz();
                if ( $client_dp->lt( $this->client_start_dp ) ) {
                    // Skip slots earlier than requested time.
                    continue;
                }
                if ( ! in_array( (int) $client_dp->format( 'w' ) + 1, $weekdays ) ) {
                    // Skip slots outside of requested weekdays.
                    continue;
                }

                // Decide how to group slots.
                $group = call_user_func( $this->callback_group, $client_dp );

                // Decide when to stop.
                if ( ! isset ( $this->slots[ $group ] ) ) {
                    switch ( call_user_func( $this->callback_stop, $client_dp, count( $this->slots ), $slots_count, $available_slots_count ) ) {
                        case 0:  // Continue search.
                            break;
                        case 1:  // Immediate stop.
                            break 3;
                        case 2:  // Check whether there are more slots and then stop.
                            $do_break = true;
                            continue 2;
                    }
                }

                if ( $slot->notFullyBooked() || $this->show_blocked_slots ) {
                    // Add slot to result.
                    $this->slots[ $group ][] = $slot;

                    ++ $slots_count;
                    if ( $slot->notFullyBooked() ) {
                        ++ $available_slots_count;
                    }
                }
            }

        }
    }

    /**
     * Callback for making decision whether to stop generator loop.
     *
     * @param DatePoint $dp
     * @param int $srv_duration_days
     * @param int $slots_count
     * @param int $available_slots_count
     * @return bool
     */
    private function _stopOneSlot( DatePoint $dp, $srv_duration_days, $slots_count, $available_slots_count )
    {
        return $available_slots_count > 0;
    }

    /**
     * Callback for making decision whether to stop generator loop.
     *
     * @param DatePoint $dp
     * @param int $srv_duration_days
     * @param int $slots_count
     * @param int $available_slots_count
     * @return int
     */
    private function _breakOneSlot( DatePoint $dp, $srv_duration_days, $slots_count, $available_slots_count )
    {
        return $available_slots_count > 0 ? 2 : $this->_breakDefault( $dp, $srv_duration_days, $slots_count );
    }

    /**
     * Callback for making decision whether to break generator loop.
     *
     * @param DatePoint $dp
     * @param int $srv_duration_days
     * @param int $slots_count
     * @return bool
     */
    private function _breakDefault( DatePoint $dp, $srv_duration_days, $slots_count )
    {
        return $dp->modify( - ( $srv_duration_days > 1 ? $srv_duration_days - 1 : 0 ) . ' days' )->gte( $this->client_end_dp );
    }

    /**
     * Callback for computing slot's group.
     *
     * @param DatePoint $client_dp
     * @return string
     */
    private function _groupDefault( DatePoint $client_dp )
    {
        return $client_dp
            ->modify( $this->srv_duration_days && ! $this->show_calendar ? 'first day of this month' : null )
            ->format('Y-m-d' );
    }

    /**
     * Callback for making decision whether to stop when calendar is enabled.
     *
     * @param DatePoint $client_dp
     * @param int $groups_count
     * @param int $slots_count
     * @return bool
     */
    private function _stopCalendar( DatePoint $client_dp, $groups_count, $slots_count  )
    {
        return (int) $client_dp->gte( $this->client_end_dp );
    }

    /**
     * Callback for making decision whether to stop when days are displayed in one column.
     *
     * @param DatePoint $client_dp
     * @param int $groups_count
     * @param int $slots_count
     * @return int
     */
    private function _stopDayPerColumn( DatePoint $client_dp, $groups_count, $slots_count )
    {
        // Stop when groups count has reached 10.
        return $groups_count >= 10 ? 2 : 0;
    }

    /**
     * Callback for making decision whether to stop for default mode.
     *
     * @param DatePoint $client_dp
     * @param int $groups_count
     * @param int $slots_count
     * @return int
     */
    private function _stopDefault( DatePoint $client_dp, $groups_count, $slots_count )
    {
        return $slots_count >= 100 ? 2 : 0;
    }

    /**
     * Find start and end dates.
     */
    private function _prepareDates()
    {
        // Initial constraints in WP time zone.
        $now = DatePoint::now();

        // Calculate min time prior booking.
        $min_time_prior_booking = Lib\Config::getMinimumTimePriorBooking( $service->getId() );
        $min_start = $now->modify( $min_time_prior_booking );
        $max_end = $now->modify( Lib\Config::getMaximumAvailableDaysForBooking() . ' days midnight' );

        // Find start date.
        if ( Lib\Config::showSingleTimeSlot() ) {
            if ( $this->show_calendar ) {
                $start_of_month = DatePoint::fromStrInClientTz( $this->selected_date )->modify( 'first day of this month midnight' );
                $this->client_start_dp = $start_of_month->lt( $min_start->toClientTz() ) ? $min_start->toClientTz() : $start_of_month;
            } else {
                $this->client_start_dp = $min_start->toClientTz();
            }
        } elseif ( $this->last_fetched_slot ) {
            // Set start date to the next day after last fetched slot.
            $this->client_start_dp = DatePoint::fromStr( $this->last_fetched_slot[0][2] )->toClientTz()->modify( 'tomorrow' );
        } else {
            // Requested date.
            if ( $this->show_calendar && ( $this->selected_date > $this->userData->getDateFrom() ) ) {
                // Example case:
                // The client chose the 3rd day of the following month on time step.
                $this->client_start_dp = DatePoint::fromStrInClientTz( $this->selected_date )->modify( 'first day of this month midnight' );
            } else {
                $this->client_start_dp = DatePoint::fromStrInClientTz( $this->userData->getDateFrom() );
            }

            if ( $this->client_start_dp->lt( $min_start ) ) {
                $this->client_start_dp = $min_start->toClientTz();
            }
        }

        // Find end date.
        $this->client_end_dp = $max_end->toClientTz();
        if ( $this->show_calendar ) {
            $client_next_month = $this->client_start_dp->modify( 'first day of next month midnight' );
            if ( $this->client_end_dp->gt( $client_next_month ) ) {
                $this->client_end_dp = $client_next_month;
            }
        }

        // Start and end dates in WP time zone.
        $this->start_dp = $this->client_start_dp->toWpTz();
        $this->end_dp   = $max_end;
    }

    /**
     * Prepare data for staff.
     */
    private function _prepareStaffData()
    {
        // Prepare staff IDs for each service.
        $staff_ids = array();
        foreach ( $this->userData->chain->getItems() as $chain_item ) {
            $parent_service_id = $chain_item->getService()->withSubServices()
                ? $chain_item->getService()->getId()
                : null;
            $sub_services = $chain_item->getSubServices();
            foreach ( $sub_services as $sub_service ) {
                $_staff_ids = $chain_item->getStaffIdsForSubService( $sub_service );
                $service_id = $sub_service->getId();
                if ( ! isset ( $staff_ids[ $service_id ] ) ) {
                    $staff_ids[ $service_id ] = array();
                }
                $staff_ids[ $service_id ] = array_unique( array_merge( $staff_ids[ $service_id ], $_staff_ids ) );
                // Service schedule.
                if ( Lib\Config::serviceScheduleActive() && $service_id ) {
                    $this->_prepareServiceSchedule( $parent_service_id, $service_id );
                }
            }
        }

        // Service price, capacity and preference rule for each staff member.
        $where = array( 'FALSE' );
        foreach ( $staff_ids as $service_id => $_staff_ids ) {
            if ( $service_id ) {
                $where[] = sprintf(
                    'ss.service_id = %d AND ss.staff_id IN (%s)',
                    $service_id,
                    empty ( $_staff_ids ) ? 'NULL' : implode( ',', $_staff_ids )
                );
            } else {
                // Custom service.
                foreach ( $_staff_ids as $_staff_id ) {
                    if ( ! isset ( $this->staff[ $_staff_id ] ) ) {
                        $this->staff[ $_staff_id ] = new Staff();
                    }
                    $this->staff[ $_staff_id ]->addService(
                        null,
                        0,
                        0,
                        1,
                        1,
                        Lib\Entities\Service::PREFERRED_MOST_EXPENSIVE,
                        array(),
                        0
                    );
                }
            }
        }
        $query = Lib\Entities\StaffService::query( 'ss' )
            ->select( 'ss.service_id, ss.price, ss.staff_id' )
            ->addSelect( sprintf( '%s AS staff_preference, %s AS staff_preference_settings, %s AS capacity_min, %s AS capacity_max',
                Lib\Proxy\Shared::prepareStatement( '\'' . Lib\Entities\Service::PREFERRED_LEAST_EXPENSIVE . '\'', 's.staff_preference', 'Service' ),
                Lib\Proxy\Shared::prepareStatement( '\'{}\'', 's.staff_preference_settings', 'Service' ),
                Lib\Proxy\Shared::prepareStatement( 1, 'ss.capacity_min', 'StaffService' ),
                Lib\Proxy\Shared::prepareStatement( 1, 'ss.capacity_max', 'StaffService' )
            ) )
            ->leftJoin( 'Service', 's', 's.id = ss.service_id' )
            ->whereRaw( implode( ' OR ', $where ), array() );

        $query = Lib\Proxy\Shared::prepareStaffServiceQuery( $query );

        if ( ! Lib\Proxy\Locations::servicesPerLocationAllowed() ) {
            $query
                ->addSelect( 'ss.location_id' )
                ->where( 'ss.location_id', null );
        }

        if ( ! Lib\Config::proActive() ) {
            // Staff preference order
            $query->addSelect( '1 AS position' );
        }

        // Calculate the days in the past which need to be taken in consideration for staff preference period.
        $staff_preference_period_before = 0;

        $rows = $query->fetchArray();
        foreach ( $rows as $row ) {
            $staff_id = $row['staff_id'];
            if ( ! isset ( $this->staff[ $staff_id ] ) ) {
                $this->staff[ $staff_id ] = new Staff();
            }
            $staff_preference_settings = (array) json_decode( $row['staff_preference_settings'], true );
            if (
                $row['staff_preference'] == Lib\Entities\Service::PREFERRED_LEAST_OCCUPIED_FOR_PERIOD ||
                $row['staff_preference'] == Lib\Entities\Service::PREFERRED_MOST_OCCUPIED_FOR_PERIOD
            ) {
                $staff_preference_period_before = max( $staff_preference_period_before, $staff_preference_settings['period']['before'] );
            }
            $this->staff[ $staff_id ]->addService(
                $row['service_id'],
                (int) $row['location_id'],
                $row['price'],
                $row['capacity_min'],
                $row['capacity_max'],
                $row['staff_preference'],
                $staff_preference_settings,
                $row['position']
            );
            Lib\Proxy\Locations::addServices( $this->staff[ $staff_id ], $staff_id, $row['service_id'] );
        }

        // Working schedule.
        $working_schedule = Lib\Entities\StaffScheduleItem::query( 'ssi' )
            ->select( 'ssi.*, break.start_time AS break_start, break.end_time AS break_end' )
            ->leftJoin( 'ScheduleItemBreak', 'break', 'break.staff_schedule_item_id = ssi.id' )
            ->whereIn( 'ssi.staff_id', array_keys( $this->staff ) )
            ->where( 'ssi.location_id', null )
            ->whereNot( 'ssi.start_time', null )
        ;
        $working_schedule = Lib\Proxy\Locations::prepareWorkingSchedule( $working_schedule, array_keys( $this->staff ) );
        foreach ( $working_schedule->fetchArray() as $item ) {
            $location_id = $item['location_id'] ?: 0;
            if ( ! in_array( $location_id, $this->staff[ $item['staff_id'] ]->getScheduleLocations() ) ) {
                $this->staff[ $item['staff_id'] ]->setSchedule( new Schedule(), $location_id );
            }
            $weekday  = $item['day_index'] - 1;
            $schedule = $this->staff[ $item['staff_id'] ]->getSchedule( $location_id );
            if ( ! $schedule->hasDay( $weekday ) ) {
                $schedule->addDay( $weekday, $item['start_time'], $item['end_time'] );
            }
            if ( $item['break_start'] ) {
                $schedule->addBreak( $item['day_index'] - 1, $item['break_start'], $item['break_end'] );
            }
        }

        // Holidays.
        $holidays = Lib\Entities\Holiday::query( 'h' )
            ->select( 'IF(h.repeat_event, DATE_FORMAT(h.date, \'%%m-%%d\'), h.date) as date, h.staff_id' )
            ->whereIn( 'h.staff_id', array_keys( $this->staff ) )
            ->whereRaw( 'h.repeat_event = 1 OR h.date >= %s', array( $this->start_dp->format( 'Y-m-d' ) ) )
            ->fetchArray();
        foreach ( $holidays as $holiday ) {
            $this->staff[ $holiday['staff_id'] ]->addHoliday( $holiday['date'] );
        }

        // Special days.
        $special_days = Lib\Proxy\SpecialDays::getSchedule( array_keys( $this->staff ), $this->start_dp->value(), $this->end_dp->value() ) ?: array();
        foreach ( $special_days as $day ) {
            $this->staff[ $day['staff_id'] ]->addSpecialDay( $day );
        }

        $padding_left = 0;

        if ( Lib\Config::proActive() ) {
            // Timezone and hour limits
            /** @var Lib\Entities\Staff[] $staff_members */
            $staff_members = Lib\Entities\Staff::query( 'st' )
                ->whereIn( 'id', array_keys( $this->staff ) )
                ->find();
            foreach ( $staff_members as $staff ) {
                $this->staff[ $staff->getId() ]->setTimeZone( $staff->getTimeZone() );
                $this->staff[ $staff->getId() ]->setWorkingTimeLimit( $staff->getWorkingTimeLimit() );
            }

            // Prepare padding_left for first service.
            $chain         = $this->userData->chain->getItems();
            $first_item    = $chain[0];
            $services      = $first_item->getSubServices();
            $first_service = $services[0];
            $padding_left  = $first_service->getPaddingLeft();
        }

        // Take into account the statuses.
        $statuses = Lib\Proxy\CustomStatuses::prepareBusyStatuses( array(
            Lib\Entities\CustomerAppointment::STATUS_PENDING,
            Lib\Entities\CustomerAppointment::STATUS_APPROVED,
        ) );
        if ( Lib\Config::waitingListActive() ) {
            $statuses[] = Lib\Entities\CustomerAppointment::STATUS_WAITLISTED;
        }

        // Bookings.
        $bookings = Lib\Entities\Appointment::query( 'a' )
            ->select( sprintf(
                '`a`.`id`,
                `a`.`location_id`,
                `a`.`staff_id`,
                `a`.`service_id`,
                `a`.`start_date`,
                DATE_ADD(`a`.`end_date`, INTERVAL IF(`ca`.`extras_consider_duration`, `a`.`extras_duration`, 0) SECOND) AS `end_date`,
                `a`.`extras_duration`,
                `s`.`one_booking_per_slot`,
                %s AS `padding_left`,
                %s AS `padding_right`,
                SUM(`ca`.`number_of_persons`) AS `number_of_bookings`,
                SUM(IF(`ca`.`status` = "%s", `ca`.`number_of_persons`, 0)) AS `waitlisted`',
                Lib\Proxy\Shared::prepareStatement( 0, 'COALESCE(s.padding_left,0)', 'Service' ),
                Lib\Proxy\Shared::prepareStatement( 0, 'COALESCE(s.padding_right,0)', 'Service' ),
                Lib\Entities\CustomerAppointment::STATUS_WAITLISTED
            ) )
            ->leftJoin( 'CustomerAppointment', 'ca', '`ca`.`appointment_id` = `a`.`id`' )
            ->leftJoin( 'Service', 's', '`s`.`id` = `a`.`service_id`' )
            ->whereIn( 'a.staff_id', array_keys( $this->staff ) )
            ->whereNotIn( 'a.id', $this->ignore_appointments )
            ->whereRaw( sprintf( 'ca.status IN ("%s") OR ca.status IS NULL', implode('","', $statuses ) ), array() )
            ->whereRaw( 'DATE_ADD(a.end_date, INTERVAL (' . Lib\Proxy\Shared::prepareStatement( 0, 'COALESCE(s.padding_right,0)', 'Service' ) . ' + %d) SECOND) >= %s',
                array(
                    $padding_left,
                    $this->start_dp->modify( sprintf( '-%d days', $staff_preference_period_before) )->format( 'Y-m-d' ),
                ) )
            ->groupBy( 'a.id' )
            ->fetchArray();
        foreach ( $bookings as $booking ) {
            $this->staff[ $booking['staff_id'] ]->addBooking( new Booking(
                $booking['location_id'],
                $booking['service_id'],
                $booking['number_of_bookings'],
                $booking['waitlisted'],
                $booking['start_date'],
                $booking['end_date'],
                $booking['padding_left'],
                $booking['padding_right'],
                $booking['extras_duration'],
                $booking['one_booking_per_slot'],
                false
            ) );
        }

        // Cart bookings.
        $this->handleCartBookings();

        // Google Calendar events.
        foreach ( Lib\Proxy\Pro::getGoogleCalendarBookings( array_keys( $this->staff ), $this->start_dp ) ?: array() as $staff_id => $bookings ) {
            foreach ( $bookings as $booking ) {
                $this->staff[ $staff_id ]->addBooking( $booking );
            }
        }

        // Outlook Calendar events.
        foreach ( Lib\Proxy\OutlookCalendar::getBookings( array_keys( $this->staff ), $this->start_dp ) ?: array() as $staff_id => $bookings ) {
            foreach ( $bookings as $booking ) {
                $this->staff[ $staff_id ]->addBooking( $booking );
            }
        }
    }

    /**
     * Prepare service schedule.
     *
     * @param int $parent_service_id
     * @param int $service_id
     */
    private function _prepareServiceSchedule( $parent_service_id, $service_id )
    {
        if ( ! isset ( $this->service_schedule[ $parent_service_id ][ $service_id ] ) ) {
            $schedule = new Schedule();
            // Working schedule.
            $working_schedule = Lib\Proxy\ServiceSchedule::getSchedule( $parent_service_id ?: $service_id ) ?: array();
            foreach ( $working_schedule as $item ) {
                $weekday = $item['day_index'] - 1;
                if ( ! $schedule->hasDay( $weekday ) ) {
                    $schedule->addDay( $weekday, $item['start_time'], $item['end_time'] );
                }
                if ( $item['break_start'] ) {
                    $schedule->addBreak( $weekday, $item['break_start'], $item['break_end'] );
                }
            }
            // Service special days.
            $special_days = Lib\Proxy\SpecialDays::getServiceSchedule(
                $parent_service_id ?: $service_id,
                $this->start_dp->value(),
                $this->end_dp->value()
            ) ?: array();
            foreach ( $special_days as $day ) {
                if ( ! $schedule->hasSpecialDay( $day['date'] ) ) {
                    $schedule->addSpecialDay( $day['date'], $day['start_time'], $day['end_time'] );
                }
                if ( $day['break_start'] ) {
                    $schedule->addSpecialBreak( $day['date'], $day['break_start'], $day['break_end'] );
                }
            }
            // Add schedule to array.
            $this->service_schedule[ $parent_service_id ][ $service_id ] = $schedule;
        }
    }

    /**
     * Add cart items to staff bookings arrays.
     */
    public function handleCartBookings()
    {
        foreach ( $this->userData->cart->getItems() as $cart_key => $cart_item ) {
            if ( ! in_array( $cart_key, $this->userData->getEditCartKeys() ) ) {
                $extras_duration = (int) Lib\Proxy\ServiceExtras::getTotalDuration( $cart_item->getExtras() );
                foreach ( $cart_item->getSlots() as $slot ) {
                    list ( $service_id, $staff_id, $datetime ) = $slot;
                    if ( isset ( $this->staff[ $staff_id ] ) ) {
                        $service = Lib\Entities\Service::find( $service_id );
                        $range   = Range::fromDates( $datetime, $datetime );
                        $range   = $range->resize( $service->getDuration() * $cart_item->getUnits() + $extras_duration );
                        $extras_duration = 0;
                        $booking_exists = false;
                        foreach ( $this->staff[ $staff_id ]->getBookings() as $booking ) {
                            // If such booking exists increase number_of_bookings.
                            if ( $booking->external() == false
                                && $booking->serviceId() == $service_id
                                && $booking->range()->wraps( $range )
                            ) {
                                $booking->incNop( $cart_item->getNumberOfPersons() );
                                $booking_exists = true;
                                break;
                            }
                        }
                        if ( ! $booking_exists ) {
                            // Add cart item to staff bookings array.
                            $this->staff[ $staff_id ]->addBooking( new Booking(
                                $cart_item->getLocationId(),
                                $service_id,
                                $cart_item->getNumberOfPersons(),
                                0,
                                $range->start()->format( 'Y-m-d H:i:s' ),
                                $range->end()->format( 'Y-m-d H:i:s' ),
                                Lib\Config::proActive() ? $service->getPaddingLeft() : 0,
                                Lib\Config::proActive() ? $service->getPaddingRight(): 0,
                                $extras_duration,
                                $service->getOneBookingPerSlot(),
                                false
                            ) );
                        }
                    }
                }
            }
        }
    }

    /**
     * Get disabled days in Pickadate format.
     *
     * @return array
     * @throws
     */
    public function getDisabledDaysForPickadate()
    {
        $one_day = new \DateInterval( 'P1D' );
        $result = array();
        $date = new \DateTime( $this->selected_date ?: $this->userData->getDateFrom() );
        $date->modify( 'first day of this month' );
        $end_date = clone $date;
        $end_date->modify( 'first day of next month' );
        $Y = (int) $date->format( 'Y' );
        $n = (int) $date->format( 'n' ) - 1;
        while ( $date < $end_date ) {
            if ( ! array_key_exists( $date->format( 'Y-m-d' ), $this->slots ) ) {
                $result[] = array( $Y, $n, (int) $date->format( 'j' ) );
            }
            $date->add( $one_day );
        }

        return $result;
    }

    /**
     * Set last fetched slot.
     *
     * @param string $last_fetched_slot
     * @return $this
     */
    public function setLastFetchedSlot( $last_fetched_slot )
    {
        $slots = json_decode( $last_fetched_slot, true );
        $this->last_fetched_slot = array( $slots[0] );

        return $this;
    }

    /**
     * Set selected date.
     *
     * @param string $selected_date
     * @return $this
     */
    public function setSelectedDate( $selected_date )
    {
        $this->selected_date = $selected_date;

        return $this;
    }

    /**
     * Gets selected date
     *
     * @return string|null
     */
    public function getSelectedDate()
    {
        return $this->selected_date;
    }

    public function getSelectedDateForPickadate()
    {
        if ( $this->selected_date ) {
            foreach ( $this->slots as $group => $slots ) {
                if ( $group >= $this->selected_date ) {
                    return $group;
                }
            }

            if ( empty( $this->slots ) ) {
                return $this->selected_date;
            } else {
                reset( $this->slots );
                return key( $this->slots );
            }
        }

        if ( ! empty ( $this->slots ) ) {
            reset( $this->slots );
            return key( $this->slots );
        }

        return $this->userData->getDateFrom();
    }

    /**
     * @return array
     */
    public function getSlots()
    {
        return $this->slots;
    }

    /**
     * @return bool
     */
    public function hasMoreSlots()
    {
        return $this->has_more_slots;
    }

    /**
     * Whether the first service in chain has duration in days.
     *
     * @return bool
     */
    public function isServiceDurationInDays()
    {
        return $this->srv_duration_days >= 1;
    }
}