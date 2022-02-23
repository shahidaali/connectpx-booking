<?php
namespace ConnectpxBooking\Lib\Entities;

use ConnectpxBooking\Lib;

/**
 * Class Service
 * @package ConnectpxBooking\Lib\Entities
 */
class SubService
{
    /** @var  config */
    protected $config;
    /** @var  data */
    protected $data;
    /** @var  string */
    protected $key;
    /** @var  string */
    protected $context;
    /** @var  string */
    protected $enabled;
    /** @var  float */
    protected $flat_rate;
    /** @var  int */
    protected $min_miles;
    /** @var  float */
    protected $rate_per_mile;
    /** @var  int */
    protected $min_waiting_time;
    /** @var  float */
    protected $rate_per_waiting_time;
    /** @var  float */
    protected $after_hours_fee;
    /** @var  float */
    protected $no_show_fee;
    
    /**
     * construct
     *
     * @return string
     */
    public function __construct( $key, $data, $context = "service" ) {
        $this->config = Lib\Utils\Common::getSubServices();

        $this->setKey( $key );
        $this->setData( $data );
        $this->setContext( $context );

        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * Gets data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Sets data
     *
     * @param string $data
     * @return $this
     */
    public function setData( $data )
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Gets key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Sets key
     *
     * @param string $key
     * @return $this
     */
    public function setKey( $key )
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Gets key
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->config[$this->key]['title'];
    }

    /**
     * Gets context
     *
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Sets context
     *
     * @param string $context
     * @return $this
     */
    public function setContext( $context )
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Gets enabled
     *
     * @return string
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Sets enabled
     *
     * @param string $enabled
     * @return $this
     */
    public function setEnabled( $enabled )
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Gets flat_rate
     *
     * @return string
     */
    public function getFlatRate()
    {
        return (float) $this->flat_rate;
    }

    /**
     * Sets flat_rate
     *
     * @param string $flat_rate
     * @return $this
     */
    public function setFlatRate( $flat_rate )
    {
        $this->flat_rate = $flat_rate;

        return $this;
    }

    /**
     * Gets min_miles
     *
     * @return string
     */
    public function getMinMiles()
    {
        return (int) $this->min_miles;
    }

    /**
     * Sets min_miles
     *
     * @param string $min_miles
     * @return $this
     */
    public function setMinMiles( $min_miles )
    {
        $this->min_miles = $min_miles;

        return $this;
    }

    /**
     * Gets min_miles
     *
     * @return string
     */
    public function getMilesToCharge( $miles )
    {
        $totalMiles = $miles;
        $minMiles = $this->getMinMiles();

        if( $this->isRoundTrip() ) {
            $totalMiles *= 2;
        }

        $milesToCharge = $totalMiles - $minMiles;

        return $milesToCharge > 0 ? $milesToCharge : 0;
    }

    /**
     * Gets min_miles
     *
     * @return string
     */
    public function getMilesPrice( $miles )
    {
        $milesToCharge = $this->getMilesToCharge( $miles );
        return $milesToCharge * $this->getRatePerMile();
    }

    /**
     * Gets rate_per_mile
     *
     * @return string
     */
    public function getRatePerMile()
    {
        return (float) $this->rate_per_mile;
    }

    /**
     * Sets rate_per_mile
     *
     * @param string $rate_per_mile
     * @return $this
     */
    public function setRatePerMile( $rate_per_mile )
    {
        $this->rate_per_mile = $rate_per_mile;

        return $this;
    }

    /**
     * Gets min_waiting_time
     *
     * @return string
     */
    public function getMinWaitingTime()
    {
        return (int) $this->min_waiting_time;
    }

    /**
     * Sets min_waiting_time
     *
     * @param string $min_waiting_time
     * @return $this
     */
    public function setMinWaitingTime( $min_waiting_time )
    {
        $this->min_waiting_time = $min_waiting_time;

        return $this;
    }

    /**
     * Gets rate_per_waiting_time
     *
     * @return string
     */
    public function getRatePerWaitingTime()
    {
        return (float) $this->rate_per_waiting_time;
    }

    /**
     * Sets rate_per_waiting_time
     *
     * @param string $rate_per_waiting_time
     * @return $this
     */
    public function setRatePerWaitingTime( $rate_per_waiting_time )
    {
        $this->rate_per_waiting_time = $rate_per_waiting_time;

        return $this;
    }

    /**
     * Gets min_miles
     *
     * @return string
     */
    public function getWaitingTimeToCharge( $waitingTime )
    {
        $waitingTimeToCharge = ( $waitingTime - $this->getMinWaitingTime() );
        return $waitingTimeToCharge > 0 ? $waitingTimeToCharge : 0;
    }

    /**
     * Gets min_miles
     *
     * @return string
     */
    public function getWaitingTimePrice( $waitingTime )
    {
        return $this->getWaitingTimeToCharge( $waitingTime ) * $this->getRatePerWaitingTime();
    }

    /**
     * Gets after_hours_fee
     *
     * @return string
     */
    public function getAfterHoursFee()
    {
        return (float) $this->after_hours_fee;
    }

    /**
     * Sets no_show_fee
     *
     * @param string $no_show_fee
     * @return $this
     */
    public function setAfterHoursFee( $no_show_fee )
    {
        $this->no_show_fee = $no_show_fee;

        return $this;
    }

    /**
     * Gets no_show_fee
     *
     * @return string
     */
    public function getNoShowFee()
    {
        return (float) $this->no_show_fee;
    }

    /**
     * Sets no_show_fee
     *
     * @param string $no_show_fee
     * @return $this
     */
    public function setNoShowFee( $no_show_fee )
    {
        $this->no_show_fee = $no_show_fee;

        return $this;
    }

    /**
     * Gets enabled
     *
     * @return string
     */
    public function isEnabled()
    {
        return $this->enabled == "yes";
    }

    /**
     * Sets no_show_fee
     *
     * @param string $no_show_fee
     * @return $this
     */
    public function isRoundTrip()
    {
        return $this->getKey() != "oneway";
    }

    /**
     * Determine the current user time zone which may be the staff or WP time zone
     *
     * @return string
     */
    public function paymentLineItems( $miles, $waiting_time, $after_hours = false, $no_show = false, $adjustments = [] )
    {        
        $totals = 0;
        $items = [];

        if( ! $no_show ) {
            $totals += $this->getFlatRate();

            $items['flat_rate'] = [
                'label' => __('Flat Rate', 'connectpx_booking'),
                'qty' => 1,
                'unit_price' => $this->getFlatRate(),
                'total' => $this->getFlatRate(),
            ];

            $milesToCharge = $this->getMilesToCharge( $miles );
            $perMilePrice = $this->getRatePerMile();
            
            if( $milesToCharge && $perMilePrice ) {
                $milesTotal = ( $milesToCharge * $perMilePrice );
                $totals += $milesTotal;

                $items['milage'] = [
                    'label' => __('Miles', 'connectpx_booking'),
                    'qty' => $milesToCharge,
                    'unit_price' => $perMilePrice,
                    'total' => $milesTotal,
                ];
            }

            $waitingTimeToCharge = $this->getWaitingTimeToCharge( $waiting_time );
            $perMinPrice = $this->getRatePerWaitingTime();
            
            if( $waitingTimeToCharge && $perMinPrice ) {
                $waitingTimeTotal = ( $waitingTimeToCharge * $perMinPrice );
                $totals += $waitingTimeTotal;

                $items['waiting_time'] = [
                    'label' => __('Waiting Time (Mins.)', 'connectpx_booking'),
                    'qty' => $waitingTimeToCharge,
                    'unit_price' => $perMinPrice,
                    'total' => $waitingTimeTotal,
                ];
            }

            if( $after_hours ) {
                $totals += $this->getAfterHoursFee();

                $items['after_hours'] = [
                    'label' => __('After Hours Fee', 'connectpx_booking'),
                    'qty' => 1,
                    'unit_price' => $this->getAfterHoursFee(),
                    'total' => $this->getAfterHoursFee(),
                ];
            }
        } else {
            if( $no_show ) {
                $totals += $this->getNoShowFee();

                $items['no_show'] = [
                    'label' => __('No Show Fee', 'connectpx_booking'),
                    'qty' => 1,
                    'unit_price' => $this->getNoShowFee(),
                    'total' => $this->getNoShowFee(),
                ];
            }
        }
        
        foreach ($adjustments as $key => $adjustment) {
            $amount = (float) $adjustment['amount'];
            $totals += $amount;

            $items['adjustment_' . $key] = [
                'label' => __($adjustment['reason'], 'connectpx_booking'),
                'qty' => 1,
                'unit_price' => $amount,
                'total' => $amount,
            ];
        }

        $line_items = [
            'totals' => $totals,
            'items' => $items,
        ];

        return $line_items;
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public static function customerSubServices( $service, $customer ) {
        if( ! $service || ! $customer ) {
            return 0;
        }

        if( $customer->isContractCustomer() ) {
            $subServices = $customer->loadEnabledSubServices( $service->getId() );
        } else {
            $subServices = $service->loadEnabledSubServices();
        }

        return $subServices;
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public static function findSubService( $service, $customer, $sub_service_key ) {
        $subServices = self::customerSubServices( $service, $customer );

        $subService = $subServices[ $sub_service_key ] ?? null;
        if( ! $subService ) {
            return 0;
        }

        return $subService;
    }
}