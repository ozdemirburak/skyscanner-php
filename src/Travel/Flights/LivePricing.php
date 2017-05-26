<?php

namespace OzdemirBurak\SkyScanner\Travel\Flights;

use OzdemirBurak\SkyScanner\TravelService;
use OzdemirBurak\SkyScanner\Traits\ImageTrait;

class LivePricing extends TravelService
{
    use ImageTrait;

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
     * Save remote agent images to local where urls are returned from the request
     *
     * @var bool
     */
    protected $saveAgentImages = false;

    /**
     * Save remote carrier images to local where urls are returned from the request
     *
     * @var bool
     */
    protected $saveCarrierImages = false;

    /**
     * Filter for maximum number of stops
     *
     * Supported values are: 0, 1
     *
     * @var string
     */
    protected $stops = 0;

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
     * Full URL
     *
     * @return string
     */
    public function getUrl()
    {
        $url = $this->url . 'pricing/v1.0/';
        return $url . $this->getPollingQueryUrl($this->getSessionKey($url));
    }

    /**
     * Get modified data where the each agent and carrier is assigned to each itinerary
     * If you only want to get get the first one, it will remove Agents property
     * Whereas Agent property will hold the first agent within the array sorted with given sorttype property
     *
     * @param bool $onlyFirstAgentPerItinerary
     *
     * @return array
     */
    public function getFlights($onlyFirstAgentPerItinerary = true)
    {
        if ($this->init()) {
            $this->addItineraries($onlyFirstAgentPerItinerary);
            $this->beautifyFlights($onlyFirstAgentPerItinerary);
        } else {
            $this->printErrorMessage($this->getResponseMessage());
        }
        return $this->flights;
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return [
            $this->saveImages($this->get('Agents'), $this->saveAgentImages),
            $this->saveImages($this->get('Carriers')),
            $this->get('Legs')
        ];
    }

    /**
     * @param bool $onlyFirstAgentPerItinerary
     */
    private function addItineraries($onlyFirstAgentPerItinerary)
    {
        foreach ($this->data->Itineraries as $key => $itinerary) {
            foreach (['OutboundLegId', 'InboundLegId'] as $leg) {
                if (isset($itinerary->$leg)) {
                    $this->flights[$key][$leg] = $itinerary->$leg;
                }
            }
            foreach ($itinerary->PricingOptions as $itineraryKey => $agent) {
                $this->flights[$key]['Agents'][$itineraryKey] = $agent;
                if ($onlyFirstAgentPerItinerary) {
                    break;
                }
            }
            if (isset($itinerary->BookingDetailsLink)) {
                $this->flights[$key]['BookingDetailsLink'] = $itinerary->BookingDetailsLink;
            }
        }
    }

    /**
     * Initialize and store data
     *
     * @return bool
     */
    private function init()
    {
        $this->flights = [];
        $this->get();
        return !empty($this->data->Itineraries);
    }

    /**
     * @param       $objects
     * @param bool  $saveCarrierImage
     *
     * @return array
     */
    private function saveImages($objects, $saveCarrierImage = false)
    {
        if ($saveCarrierImage === true) {
            foreach ($objects as &$object) {
                $object->ImageUrl = $this->saveImage($object->ImageUrl, $this->savePath);
            }
        }
        return $objects;
    }

    /**
     * Assign flight specific agents, carriers and legs to the each
     *
     * @param bool $onlyFirstAgentPerItinerary
     */
    private function beautifyFlights($onlyFirstAgentPerItinerary)
    {
        list($agents, $carriers, $legs) = $this->getMeta();
        foreach ($this->flights as &$flight) {
            // Find and assign each agent by ID
            foreach ($flight['Agents'] as $key => &$flightAgent) {
                $agent = $agents[$this->arraySearch($flightAgent->Agents[0], $agents, 'Id')];
                foreach ($agent as $property => $propertyValue) {
                    $flightAgent->$property = $propertyValue;
                }
                unset($flight['Agents'][$key]->Agents);
            }
            // Find and assign outbound and inbound legs
            foreach (['OutboundLeg' => 'OutboundLegId', 'InboundLeg' => 'InboundLegId'] as $key => $search) {
                if (isset($flight[$search])) {
                    $legId = $this->arraySearch($flight[$search], $legs, 'Id');
                    foreach ($legs[$legId]->FlightNumbers as $order => $legInformation) {
                        $carrierId = $this->arraySearch($legInformation->CarrierId, $carriers, 'Id');
                        $flightNumber = $legs[$legId]->FlightNumbers[$order]->FlightNumber;
                        $flightCode = $carriers[$carrierId]->Code . $flightNumber;
                        $legs[$legId]->FlightNumbers[$order]->FlightCode = $flightCode;
                        $legs[$legId]->FlightNumbers[$order]->Carrier = $carriers[$carrierId];
                    }
                    if ($this->removeIds === true) {
                        unset($flight[$search]);
                    }
                }
            }
            $flight['Agent'] = $flight['Agents'][0];
            if ($onlyFirstAgentPerItinerary === true) {
                unset($flight['Agents']);
            }
        }
    }

    /**
     * @return array
     */
    protected function getSpecificSessionParameters()
    {
        return $this->filterArray([
            'adults'           => $this->adults,
            'cabinclass'       => $this->cabinclass,
            'children'         => $this->children,
            'destinationplace' => $this->destinationplace,
            'groupPricing'     => $this->groupPricing,
            'inbounddate'      => $this->inbounddate,
            'infants'          => $this->infants,
            'locationschema'   => $this->locationschema,
            'originplace'      => $this->originplace,
            'outbounddate'     => !empty($this->outbounddate) ? $this->outbounddate : date('Y-m-d', strtotime('+1 week'))
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
