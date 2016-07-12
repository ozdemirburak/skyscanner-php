<?php

namespace OzdemirBurak\SkyScanner\Travel\Flights;

use Carbon\Carbon;
use OzdemirBurak\SkyScanner\BaseRequest;
use OzdemirBurak\SkyScanner\Traits\ImageTrait;

class LivePricing extends BaseRequest
{
    use ImageTrait;

    /**
     * Agent variables that will be stored
     *
     * @var array
     */
    protected $agentVariables = [
        'id' => 'Id', 'name' => 'Name', 'status' => 'Status',
        'optimised_for_mobile' => 'OptimisedForMobile', 'type' => 'Type',
        'image_path' => 'ImageUrl'
    ];

    /**
     * The number of adult passengers
     *
     * @var int
     */
    protected $adults = 1;

    /**
     * The Cabin Class
     *
     * Supported values are: Economy, PremiumEconomy, Business, First
     *
     * @var string
     */
    protected $cabinclass = 'Economy';

    /**
     * The code schema to use for carriers
     *
     * Supported values are: Iata, Icao, Skyscanner
     *
     * @var string
     */
    protected $carrierschema = 'Iata';

    /**
     * Carrier variables that will be stored
     *
     * @var array
     */
    protected $carrierVariables = ['id' => 'Id', 'code' => 'Code', 'name' => 'Name', 'display_code' => 'DisplayCode'];

    /**
     * The number of children passengers
     *
     * @var int
     */
    protected $children = 0;

    /**
     * Destination airports to filter on
     *
     * List of airport codes delimited by ';'
     *
     * @var string
     */
    protected $destinationairports;

    /**
     * The destination city or airport
     *
     * Specified location schema, or Skyscanner Rnid
     *
     * @var string
     */
    protected $destinationplace = 'IST';

    /**
     * Filter for maximum duration in minutes
     *
     * Supported values are: Between 0 and 1800
     *
     * @var int
     */
    protected $duration;

    /**
     * Filter flights by any but the specified carriers
     *
     * Must be semicolon-separated Iata carrier codes.
     *
     * @link http://www.iata.org/publications/Pages/code-search.aspx
     *
     * @var string
     */
    protected $excludecarriers;

    /**
     * Flights that are parsed via API call
     *
     * @var array
     */
    protected $flights = [];

    /**
     * Show price-per-adult (false), or price for all passengers (true)
     *
     * @var bool
     */
    protected $groupPricing = false;

    /**
     * The return date
     *
     * Formatted as YYYY-mm-dd
     *
     * @var string
     */
    protected $inbounddate;

    /**
     * Filter for end of range for inbound departure time
     *
     * Formatted as 'hh:mm'
     *
     * @var string
     */
    protected $inbounddepartendtime;

    /**
     * Filter for start of range for inbound departure time
     *
     * Formatted as 'hh:mm'
     *
     * @var string
     */
    protected $inbounddepartstarttime;

    /**
     * Filter for inbound departure time by time period of the day (i.e. morning, afternoon, evening)
     *
     * List of day time period delimited by ';' (acceptable values are M, A, E)
     *
     * @var string
     */
    protected $inbounddeparttime;

    /**
     * Filter flights by the specified carriers
     *
     * Must be semicolon-separated Iata carrier codes.
     *
     * @link http://www.iata.org/publications/Pages/code-search.aspx
     *
     * @var string
     */
    protected $includecarriers;

    /**
     * The number of infant passengers
     *
     * @var int
     */
    protected $infants = 0;

    /**
     * Itinerary variables parsed from API with the edited key as the key and the original key as the value
     *
     * @var array
     */
    protected $itineraryPricingVariables = [
        'price' => 'Price', 'url' => 'DeeplinkUrl', 'age' => 'QuoteAgeInMinutes', 'detail' => 'Agents'
    ];

    /**
     * Variables to store within legs
     *
     * @var array
     */
    protected $legVariables = [
        'id' => 'Id', 'origin' => 'OriginStation', 'destination' => 'DestinationStation', 'departs_at' => 'Departure',
        'arrives_at' => 'Arrival', 'duration' => 'Duration', 'direction' => 'Directionality'
    ];

    /**
     ** The code schema used for locations
     *
     * Supported values are: Iata, GeoNameCode, GeoNameId, Rnid, Sky
     *
     * @var string
     */
    protected $locationschema = 'Iata';

    /**
     * Origin airports to filter on
     *
     * List of airport codes delimited by ';'
     *
     * @var string
     */
    protected $originairports;

    /**
     * The origin city or airport
     *
     * Specified location schema, or Skyscanner Rnid
     *
     * @var string
     */
    protected $originplace = 'LHR';

    /**
     * The departure date
     *
     * Formatted as YYYY-mm-dd
     *
     * @var string
     */
    protected $outbounddate;

    /**
     * Filter for end of range for outbound departure time
     *
     * Formatted as 'hh:mm'
     *
     * @var string
     */
    protected $outbounddepartendtime;

    /**
     * Filter for start of range for outbound departure time
     *
     * Formatted as 'hh:mm'
     *
     * @var string
     */
    protected $outbounddepartstarttime;

    /**
     * Filter for outbound departure time by time period of the day (i.e. morning, afternoon, evening)
     *
     * List of day time period delimited by ';' (acceptable values are M, A, E)
     *
     * @var string
     */
    protected $outbounddeparttime;

    /**
     * Image save path for agents and carriers, optional
     *
     * @var string
     */
    protected $savePath = '/tmp/images/';

    /**
     * Filter for maximum number of stops
     *
     * Supported values are: 0, 1, 2, 3
     *
     * @var string
     */
    protected $stops = 0;

    /**
     * The Session key to identify the session.
     *
     * @var string
     */
    protected $session;

    /**
     * The property to sort on. If specified, you must also specify sortorder
     *
     * Supported values are: carrier, duration, outboundarrivetime, outbounddeparttime, inboundarrivetime,
     *                       inbounddeparttime, price
     *
     * @var string
     */
    protected $sorttype = 'price';

    /**
     * Sort direction
     *
     * Supported values are: asc, desc
     *
     * @var string
     */
    protected $sortorder = 'asc';

    /**
     * SkyScanner Request Provider
     *
     * @var string
     */
    protected $url = 'http://partners.api.skyscanner.net/apiservices/pricing/v1.0/';

    /**
     * @param bool $resetSession
     *
     * @return string
     */
    public function getUrl($resetSession = false)
    {
        if (empty($this->session) || $resetSession === true) {
            $this->makeRequest('POST');
            $locationParameters = explode('/', $this->getLocation());
            $this->session = end($locationParameters);
            $this->url .= $this->session . '?apiKey=' . $this->getParameter('apiKey') . '&' .
                          http_build_query($this->getOptionalPollingParameters());
            $this->flights = [];
        }
        return $this->url;
    }

    /**
     * @param bool $onlyCheapestAgentPerItinerary
     *
     * @return array
     */
    public function parseFlights($onlyCheapestAgentPerItinerary = true)
    {
        $this->makeRequest('GET', $this->getUrl());
        if ($this->getResponseStatus() === 200) {
            $data = json_decode($this->response->getBody());
            foreach ($data->Itineraries as $key => $itinerary) {
                foreach (['outbound_leg_id' => 'OutboundLegId', 'inbound_leg_id' => 'InboundLegId'] as $new => $old) {
                    if (isset($itinerary->$old)) {
                        $this->flights[$key][$new] = $itinerary->$old;
                    }
                }
                if ($onlyCheapestAgentPerItinerary) {
                    $this->addAgent($key, $itinerary->PricingOptions[0]);
                } else {
                    foreach ($itinerary->PricingOptions as $itineraryKey => $agent) {
                        $this->addAgent($key, $agent, $itineraryKey);
                    }
                }
            }
            $this->beautifyFlights(
                $this->getCarriersOrAgents($data->Agents, $this->agentVariables),
                $this->getCarriersOrAgents($data->Carriers, $this->carrierVariables),
                $this->getLegs($data)
            );
        }
        $this->cleanVariables();
        return $this->flights;
    }

    /**
     * @param         $key
     * @param         $itinerary
     * @param integer $itineraryKey
     */
    private function addAgent($key, $itinerary, $itineraryKey = 0)
    {
        foreach ($this->itineraryPricingVariables as $new => $original) {
            $this->flights[$key]['agents'][$itineraryKey][$new] = $itinerary->$original;
        }
    }

    /**
     * @param       $data
     * @param array $legs
     *
     * @return array
     */
    private function getLegs($data, $legs = [])
    {
        foreach ($data->Legs as $legKey => $leg) {
            foreach ($this->legVariables as $key => $variable) {
                $legs[$legKey][$key] = $leg->$variable;
            }
            foreach ($leg->FlightNumbers as $singleLegKey => $singleLeg) {
                $legs[$legKey]['flight_numbers'][$singleLeg->FlightNumber]['carrier_id'] = $singleLeg->CarrierId;
            }
        }
        return $legs;
    }

    /**
     * @param       $objects
     * @param       $variables
     * @param bool  $saveCarrierImage
     * @param array $results
     *
     * @return array
     */
    private function getCarriersOrAgents($objects, $variables, $saveCarrierImage = false, $results = [])
    {
        foreach ($objects as $resultKey => $result) {
            foreach ($variables as $key => $variable) {
                $results[$resultKey][$key] = $result->$variable;
            }
            $results[$resultKey]['image_path'] = $saveCarrierImage ?
                $this->saveImage($result->ImageUrl, $this->savePath) :
                $result->ImageUrl;
        }
        return $results;
    }

    /**
     * Assign flight specific agents, carriers and legs to the each
     *
     * @param $agents
     * @param $carriers
     * @param $legs
     */
    private function beautifyFlights($agents, $carriers, $legs)
    {
        foreach ($this->flights as $flightKey => $flight) {
            foreach ($flight['agents'] as $key => $agent) {
                $agent = $agents[array_search($agent['detail'][0], array_column($agents, 'id'))];
                foreach (array_keys($this->agentVariables) as $agentKey) {
                    $this->flights[$flightKey]['agents'][$key][$agentKey] = $agent[$agentKey];
                }
                unset($this->flights[$flightKey]['agents'][$key]['detail']);
            }
            foreach (['outbound_leg' => 'outbound_leg_id', 'inbound_leg' => 'inbound_leg_id'] as $key => $search) {
                if (isset($this->flights[$flightKey][$search])) {
                    $legId = array_search($this->flights[$flightKey][$search], array_column($legs, 'id'));
                    foreach ($legs[$legId]['flight_numbers'] as $flight_number => $leg_information) {
                        $carrierId = array_search($leg_information['carrier_id'], array_column($carriers, 'id'));
                        $legs[$legId]['flight_numbers'][$flight_number]['carrier'] = $carriers[$carrierId];
                    }
                    $this->flights[$flightKey][$key] = $legs[$legId];
                }
            }
        }
    }

    /**
     * Remove duplicated id variables that were used to find legs and carriers
     */
    private function cleanVariables()
    {
        foreach ($this->flights as &$flight) {
            foreach (['outbound_leg_id', 'inbound_leg_id'] as $unset) {
                if (isset($flight[$unset])) {
                    unset($flight[$unset]);
                }
            }
            foreach (['outbound_leg', 'inbound_leg'] as $unset) {
                if (isset($flight[$unset])) {
                    foreach ($flight[$unset]['flight_numbers'] as $flight_number => &$information) {
                        unset($information['carrier_id']);
                    }
                }
            }
            if (count($flight['agents']) === 1) {
                $flight['agent'] = $flight['agents'][0];
                unset($flight['agents']);
            }
        }
    }

    /**
     * @return string
     */
    protected function getLocation()
    {
        return $this->getResponseHeader('Location');
    }

    /**
     * @return array
     */
    protected function getSpecificSessionParameters()
    {
        return $this->filterArray([
            'adults'                  => $this->adults,
            'cabinclass'              => $this->cabinclass,
            'children'                => $this->children,
            'destinationplace'        => $this->destinationplace,
            'groupPricing'            => $this->groupPricing,
            'inbounddate'             => $this->inbounddate,
            'infants'                 => $this->infants,
            'locationschema'          => $this->locationschema,
            'originplace'             => $this->originplace,
            'outbounddate'            => !empty($this->outbounddate) ? $this->outbounddate
                                                                     : Carbon::now()->addWeek(1)->format('Y-m-d'),
        ]);
    }

    /**
     * @return array
     */
    protected function getOptionalPollingParameters()
    {
        return $this->filterArray([
            'destinationairports'     => $this->destinationairports,
            'duration'                => $this->duration,
            'excludecarriers'         => $this->excludecarriers,
            'inbounddepartendtime'    => $this->inbounddepartendtime,
            'inbounddepartstarttime'  => $this->inbounddepartstarttime,
            'inbounddeparttime'       => $this->inbounddeparttime,
            'includecarriers'         => $this->includecarriers,
            'outbounddepartendtime'   => $this->outbounddepartendtime,
            'outbounddepartstarttime' => $this->outbounddepartstarttime,
            'outbounddeparttime'      => $this->outbounddeparttime,
            'originairports'          => $this->originairports,
            'stops'                   => $this->stops,
            'sorttype'                => $this->sorttype,
            'sortorder'               => $this->sortorder
        ]);
    }
}
