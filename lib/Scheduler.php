<?php
namespace ConnectpxBooking\Lib;

use ConnectpxBooking\Lib;
use ConnectpxBooking\Lib\Slots\DatePoint;

/**
 * Class Scheduler
 * @package ConnectpxBookingRecurringAppointments\Lib
 */
class Scheduler
{
    const REPEAT_DAILY    = 'daily';
    const REPEAT_WEEKLY   = 'weekly';
    const REPEAT_BIWEEKLY = 'biweekly';
    const REPEAT_MONTHLY  = 'monthly';
    const REPEAT_YEARLY   = 'yearly';//not implemented yet

    private $client_from;

    private $time;

    private $client_until;

    private $repeat;

    private $params;

    private $slots;

    private $index;

    /** @var Lib\UserBookingData */
    private $userData;

    /**
     * Constructor.
     *
     * @param Lib\Chain $chain chain to repeat
     * @param string    $datetime first appointment date and time (in WP time zone)
     * @param string    $until the last appointment date (in client time zone)
     * @param string    $repeat repeat period, could be one of self::REPEAT_*
     * @param array     $params additional params we should know to build schedule
     * @param array     $exclude slots we can't use for schedule
     * @param bool      $waiting_list_enabled
     * @param array     $ignore_appointments
     */
    public function __construct( $userData, $datetime, $pickup_time, $return_pickup_time, $until, $repeat, array $params )
    {
        $this->pickup_time   = $pickup_time;
        $this->return_pickup_time   = $return_pickup_time;
        $this->client_from   = DatePoint::fromStr( $datetime )->toClientTz();
        $this->client_until  = DatePoint::fromStrInClientTz( $until );
        $this->time          = $this->client_from->format( 'H:i:s' );
        $this->repeat        = $repeat;
        $this->params        = $params;
    }

    /**
     * Build schedule based on timeslots
     *
     * @param int[] $slots array of unix timestamps with appointment time
     * @return array
     */
    public function build( array $slots )
    {
        $this->index = 0;

        for ( $i = 0; $i < count( $slots ); $i++ ) {
            $dp         = DatePoint::fromStr( $slots[ $i ][0] );
            $client_dp  = $dp->toClientTz();
            $this->time = $client_dp->format( 'H:i:s' );
            $this->_addSlot( $client_dp->modify( 'today' ), true );
        }

        return $this->_schedule();
    }

    /**
     * Create schedule.
     *
     * @return array
     */
    private function _schedule()
    {
        $start_dp  = $this->client_from->modify( 'today' );
        $client_dp = $this->client_from->modify( 'today' );

        $weekdays = array( 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun' );
        $ordinals = array( 'first', 'second', 'third', 'fourth', 'last' );

        switch ( $this->repeat ) {
            case self::REPEAT_DAILY:
                if ( isset ( $this->params['every'] ) ) {
                    while ( $client_dp->lte( $this->client_until ) ) {
                        $this->_addSlot( $client_dp, $this->params['every'] < 7 );
                        $client_dp = $client_dp->modify( sprintf( '+%d days', $this->params['every'] ) );
                    }
                }
                break;
            case self::REPEAT_WEEKLY:
                if ( isset ( $this->params['on'] ) && is_array( $this->params['on'] ) && ! empty ( $this->params['on'] ) ) {
                    self::sortWeekdays( $this->params['on'] );
                    while ( true ) {
                        foreach ( $this->params['on'] as $weekday ) {
                            if ( in_array( $weekday, $weekdays ) ) {
                                $client_dp = $client_dp
                                    ->modify( 'previous sun' )  // In PHP 5.6.23 & 7.0.8 this would be just '$weekday this week'
                                    ->modify( 'next mon' )      // (@see https://bugs.php.net/bug.php?id=63740).
                                    ->modify( $weekday );
                                if ( $client_dp->gt( $this->client_until ) ) {
                                    break ( 2 );
                                }
                                if ( $client_dp->gte( $start_dp  ) ) {
                                    $this->_addSlot( $client_dp );
                                }
                            }
                        }
                        $client_dp = $client_dp->modify( '+1 week' );
                    }
                }
                break;
            case self::REPEAT_BIWEEKLY:
                if ( isset ( $this->params['on'] ) && is_array( $this->params['on'] ) && ! empty ( $this->params['on'] ) ) {
                    self::sortWeekdays( $this->params['on'] );
                    while ( true ) {
                        foreach ( $this->params['on'] as $weekday ) {
                            if ( in_array( $weekday, $weekdays ) ) {
                                $client_dp = $client_dp
                                    ->modify( 'previous sun' )  // In PHP 5.6.23 & 7.0.8 this would be just '$weekday this week'
                                    ->modify( 'next mon' )      // (@see https://bugs.php.net/bug.php?id=63740).
                                    ->modify( $weekday );
                                if ( $client_dp->gt( $this->client_until ) ) {
                                    break ( 2 );
                                }
                                if ( $client_dp->gte( $start_dp  ) ) {
                                    $this->_addSlot( $client_dp );
                                }
                            }
                        }
                        $client_dp = $client_dp->modify( '+2 weeks' );
                    }
                }
                break;
            case self::REPEAT_MONTHLY:
                if ( isset ( $this->params['on'] ) ) {
                    if ( $this->params['on'] == 'day' && isset ( $this->params['day'] ) ) {
                        while ( $client_dp->lte( $this->client_until ) ) {
                            if ( $this->params['day'] <= $client_dp->format( 't' ) ) {
                                $client_dp = $client_dp
                                    ->modify( 'last day of previous month' )
                                    ->modify( sprintf( '+%d days', $this->params['day'] ) );
                                if ( $client_dp->gte( $start_dp  ) ) {
                                    $this->_addSlot( $client_dp );
                                }
                            }
                            $client_dp = $client_dp->modify( 'first day of next month' );
                        }
                    } elseif ( in_array( $this->params['on'], $ordinals ) && isset ( $this->params['weekday'] ) && in_array( $this->params['weekday'], $weekdays ) ) {
                        while ( $client_dp->lte( $this->client_until ) ) {
                            $client_dp = $client_dp
                                ->modify( sprintf( '%s %s of', $this->params['on'], $this->params['weekday'] ) );
                            if ( $client_dp->gte( $start_dp  ) ) {
                                $this->_addSlot( $client_dp );
                            }
                            $client_dp = $client_dp->modify( 'first day of next month' );
                        }
                    }
                }
                break;
            case self::REPEAT_YEARLY:
                break;
        }

        return $this->slots;
    }

    /**
     * Add slot.
     *
     * @param DatePoint $client_dp
     * @param boolean   $skip_days_off
     */
    private function _addSlot( DatePoint $client_dp, $skip_days_off = false )
    {
        $slot_date = $client_dp->format('Y-m-d');

        foreach ($this->slots as $key => $slot) {
            if( $slot['slot'][0] == $slot_date ) {
                return;
            }
        }

        $result['index'] = ++ $this->index;
        $result['slot'] = [$slot_date, $this->pickup_time, $this->return_pickup_time];
        $result['display_date'] = $client_dp->formatI18n( 'D, M d' );

        $this->slots[] = $result;
    }

    /**
     * Sort days considering start_of_week.
     *
     * @param array $input
     */
    public static function sortWeekdays( array &$input )
    {
        $weekdays = array( 'mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6, 'sun' => 7 );

        usort( $input, function ( $a, $b ) use ( $weekdays ) {
            return $weekdays[ $a ] - $weekdays[ $b ];
        } );
    }

}