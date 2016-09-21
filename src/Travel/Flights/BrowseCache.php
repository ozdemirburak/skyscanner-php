<?php

namespace OzdemirBurak\SkyScanner\Travel\Flights;

use Exception;
use OzdemirBurak\SkyScanner\BaseRequest;

class BrowseCache extends BaseRequest
{
    /**
     * Carrier variables that will be stored
     *
     * @var array
     */
    protected $carrierVariables = ['id' => 'CarrierId', 'name' => 'Name'];

    /**
     * Date variables that will be stored
     *
     * @var array
     */
    protected $dateVariables = ['outbound' => 'OutboundDates', 'inbound' => 'InboundDates'];

    /**
     * The destination city or airport
     *
     * Specified location schema, or Skyscanner Rnid
     *
     * @var string
     */
    protected $destinationPlace = 'JFK';

    /**
     * Flatten carrier information to single dimension if there exists only one carrier.
     * That also means that flight is direct
     *
     * @var bool
     */
    protected $flattenSingleCarrier = true;

    /**
     * Data that is parsed via API call
     *
     * @var array
     */
    protected $data = [];

    /**
     * Flight variables that will be stored
     *
     * @var array
     */
    protected $flightVariables = [
        'id' => 'QuoteId', 'is_direct' => 'Direct', 'minimum_price' => 'MinPrice', 'updated_at' => 'QuoteDateTime'
    ];

    /**
     * Needed for X-Forwarded-For
     *
     * @var string
     */
    protected $ip;

    /**
     * Valid Browse Cache methods
     *
     * @var array
     */
    protected $methods = ['browsequotes', 'browseroutes', 'browsedates', 'browsegrid'];

    /**
     * The return date
     *
     * Formatted as yyyy-MM-dd or yyyy-MM
     *
     * @var string
     */
    protected $inboundPartialDate;

    /**
     * The departure date
     *
     * Formatted as yyyy-MM-dd or yyyy-MM
     *
     * @var string
     */
    protected $outboundPartialDate;

    /**
     * The origin city or airport
     *
     * Specified location schema, or Skyscanner Rnid
     *
     * @var string
     */
    protected $originPlace = 'LHR';

    /**
     * Flight variables that will be stored
     *
     * @var array
     */
    protected $placeVariables = [
        'id' => 'PlaceId', 'iata' => 'IataCode', 'name' => 'Name', 'type' => 'Type',
        'skyscanner_code' => 'SkyscannerCode', 'city_name' => 'CityName', 'city_id' => 'CityId',
        'country_name' => 'CountryName'
    ];

    /**
     * SkyScanner Request Provider
     *
     * @var string
     */
    protected $url = 'http://partners.api.skyscanner.net/apiservices/{method}/v1.0/{country}/{currency}/{locale}/{originPlace}/{destinationPlace}/{outboundPartialDate}/{inboundPartialDate}';

    /**
     * @param string $method
     *
     * @return array
     * @throws \Exception
     */
    public function getData($method = 'browsequotes')
    {
        if (!in_array($method, $this->methods)) {
            throw new Exception('Invalid Browse Cache method');
        }
        $this->makeRequest('GET', $this->getUrl($method));
        if ($this->getResponseStatus() === 200) {
            $data = $this->getResponseBody();
            $this->addCollection('carriers', $data->Carriers, $this->carrierVariables, 'CarrierId');
            $this->addCollection('places', $data->Places, $this->placeVariables, 'PlaceId');
            if (!empty($data->Quotes)) {
                $this->addQuotes($data->Quotes);
            }
            if ($method !== 'browsequotes') {
                $array = explode('browse', $method);
                $classMethod = 'add' . ucwords(end($array));
                $this->$classMethod($data);
            }
            $this->data['referral_url'] = $this->getReferralUrl($method);
            $this->resetKeys();
        } else {
            $this->printErrorMessage($this->getResponseMessage());
        }
        return $this->data;
    }

    /**
     * @param $method
     *
     * @return string
     */
    public function getUrl($method = 'browsequotes')
    {
        foreach ($this->getParameters() as $parameter => $value) {
            $search = '{' . $parameter . '}';
            if (strpos($this->url, $search) !== false) {
                $this->url = str_replace($search, $value, $this->url);
            }
        }
        return $this->url = str_replace('{method}', $method, $this->url);
    }

    /**
     * @param $key
     * @param $collection
     * @param $variables
     * @param $identifier
     */
    protected function addCollection($key, $collection, $variables, $identifier)
    {
        foreach ($collection as $object) {
            foreach ($variables as $new => $old) {
                if (isset($object->$old)) {
                    $this->data[$key][$object->$identifier][$new] = $object->$old;
                }
            }
        }
    }

    /**
     * Case I:
     *  Find me the cheapest price for each available route from London to France for the next year.
     *  This gives the cheapest known price for each airport in France.
     *
     * Case II:
     *  Find me the cheapest price for each available route from London to anywhere departing in January and returning
     *  in February. This gives the cheapest known price for each destination country that can be reached from London.
     *
     * Case III:
     *  Find me the cheapest prices from Edinburgh to London in the next year. This gives the cheapest known price
     *  for each month of the coming year.
     *
     * Case IV:
     *  Find me the cheapest prices from Edinburgh to London departing in January and returning in February.
     *  This gives the cheapest prices for each day in the months on the query.
     *
     * Case V:
     *  Find me the cheapest prices from Edinburgh to London departing on 5th January and returning on 6th February.
     *  This gives the cheapest prices for these days.
     *
     * @param $quotes
     *
     * @link http://business.skyscanner.net/portal/en-GB/Documentation/FlightsBrowseCacheQuotes
     */
    protected function addQuotes($quotes)
    {
        foreach ($quotes as $quote) {
            $id = $quote->QuoteId - 1;
            foreach ($this->flightVariables as $newVariable => $oldVariable) {
                if (isset($quote->$oldVariable)) {
                    $this->data['quotes'][$id][$newVariable] = $quote->$oldVariable;
                }
            }
            foreach (['outbound_leg' => 'OutboundLeg', 'inbound_leg' => 'InboundLeg'] as $new => $old) {
                if (isset($quote->$old)) {
                    foreach ($quote->$old->CarrierIds as $key => $carrierId) {
                        $this->data['quotes'][$id][$new]['carrier'][$key] = $this->data['carriers'][$carrierId];
                    }
                    if (count($quote->$old->CarrierIds) === 1 && $this->flattenSingleCarrier === true) {
                        $this->data['quotes'][$id][$new]['carrier'] = $this->data['quotes'][$id][$new]['carrier'][0];
                    }
                    foreach (['origin' => 'OriginId', 'destination' => 'DestinationId'] as $variable => $search) {
                        $this->data['quotes'][$id][$new][$variable] = $this->data['places'][$quote->$old->$search];
                    }
                    $this->data['quotes'][$id][$new]['departs_at'] = $quote->$old->DepartureDate;
                }
            }
        }
    }

    /**
     * Case I:
     *  Find me the cheapest price for each available route from London to France for the next year.
     *  This gives the cheapest known price for each airport in France.
     *
     * Case II:
     *  Find me the cheapest price for each available route from London to anywhere departing in January and
     *  returning in February. This gives the cheapest known price for each destination country that
     *  can be reached from London.
     *
     * @param $data
     *
     * @link http://business.skyscanner.net/portal/en-GB/Documentation/FlightsBrowseCacheRoutes
     */
    protected function addRoutes($data)
    {
        $variables = ['cheapest_price' => 'Price', 'updated_at' => 'QuoteDateTime'];
        foreach ($data->Routes as $key => $route) {
            foreach (['origin' => 'OriginId', 'destination' => 'DestinationId'] as $index => $identifier) {
                $this->data['routes'][$key][$index] = $this->data['places'][$route->$identifier];
            }
            $this->pushVariables($route, 'routes', $key, $variables);
            $this->pushQuotes($route, 'routes', $key);
        }
    }

    /**
     * Get the cheapest price from one place to another for each day of a given month
     * Get the cheapest price from one place to another for each month within the next year
     *
     * Case I:
     *  Find me the cheapest prices from Edinburgh to London in the next year.
     *  This gives the cheapest known price for each month of the coming year.
     *
     * Case II:
     *  Find me the cheapest prices from Edinburgh to London departing in January and returning in February.
     *  This gives the cheapest prices for each day in the months on the query.
     *
     * Case III:
     *  Find me the cheapest prices from Edinburgh to London departing on 5th January and returning on 6th February.
     *  This gives the cheapest prices for these days.
     *
     * @param $data
     *
     * @link  http://business.skyscanner.net/portal/en-GB/Documentation/FlightsBrowseCacheDates
     */
    protected function addDates($data)
    {
        $variables = ['date' => 'PartialDate', 'cheapest_price' => 'Price', 'updated_at' => 'QuoteDateTime'];
        foreach ($this->dateVariables as $new => $old) {
            if (isset($data->Dates->$old)) {
                foreach ($data->Dates->$old as $key => $date) {
                    $this->pushVariables($date, 'dates', $key, $variables);
                    $this->pushQuotes($date, 'dates', $key);
                }
            }
        }
    }

    /**
     * Case I:
     *  Find me the cheapest prices from Edinburgh to London for all days in January.
     *  This gives the cheapest price for each day in January.
     *
     * Case II:
     *  Find me the cheapest prices from Edinburgh to London departing in January and returning in February.
     *  This gives the cheapest prices for combination of all days in January with all days in February.
     *
     * @param $data
     *
     * @link  http://business.skyscanner.net/portal/en-GB/Documentation/FlightsBrowseCacheGrid
     */
    protected function addGrid($data)
    {
        $variables = ['minimum_price' => 'MinPrice', 'updated_at' => 'QuoteDateTime'];
        foreach ($data->Dates[1] as $key => $object) {
            if (isset($data->Dates[0][$key]->DateString)) {
                $this->pushVariables($object, 'grid', $data->Dates[0][$key]->DateString, $variables);
            }
        }
    }

    /**
     * @param $object
     * @param $collection
     * @param $key
     * @param $variables
     */
    protected function pushVariables($object, $collection, $key, $variables)
    {
        foreach ($variables as $newVariable => $oldVariable) {
            if (isset($object->$oldVariable)) {
                $this->data[$collection][$key][$newVariable] = $object->$oldVariable;
            }
        }
    }

    /**
     * @param        $object
     * @param        $collection
     * @param        $key
     * @param string $identifier
     */
    protected function pushQuotes($object, $collection, $key, $identifier = 'quotes')
    {
        if (!empty($object->QuoteIds)) {
            foreach ($object->QuoteIds as $id => $quote) {
                $this->data[$collection][$key][$identifier][$id] = $this->data[$identifier][$id];
            }
        }
    }

    /**
     * @param bool $isGet
     *
     * @return array
     */
    protected function getRequestParameters($isGet = true)
    {
        return [
            $this->getMethod($isGet) => ['apiKey' => $this->apiKey],
            'Accept' => 'application/json',
            'X-Forwarded-For' => $this->getXForwardedFor()
        ];
    }

    /**
     * @return array
     */
    protected function getOptionalPollingParameters()
    {
        return [
            'destinationPlace'        => $this->destinationPlace,
            'inboundPartialDate'      => $this->inboundPartialDate,
            'originPlace'             => $this->originPlace,
            'outboundPartialDate'     => !empty($this->outboundPartialDate) ? $this->outboundPartialDate
                                         : date('Y-m', strtotime('+1 month')),
        ];
    }

    /**
     * @return string
     */
    protected function getXForwardedFor()
    {
        return !empty($this->ip) ? $this->ip :
            getenv('HTTP_CLIENT_IP') ?: getenv('HTTP_X_FORWARDED_FOR') ?: getenv('HTTP_X_FORWARDED') ?:
                getenv('HTTP_FORWARDED_FOR') ?: getenv('HTTP_FORWARDED') ?: getenv('REMOTE_ADDR');
    }

    /**
     * @param $method
     *
     * @link http://business.skyscanner.net/portal/en-GB/Documentation/Referrals
     *
     * @return string
     */
    protected function getReferralUrl($method)
    {
        return str_replace($method, 'referral', $this->url) . '?apiKey=' . substr($this->apiKey, 0, 16);
    }

    /**
     * Ids are being used to find the objects from the collections easily for the quotes and routes,
     * However, there is no need to keep them as key after the identification.
     */
    private function resetKeys()
    {
        if (isset($this->data['places'])) {
            $this->data['places'] = array_values($this->data['places']);
        }
        if (isset($this->data['carriers'])) {
            $this->data['carriers'] = array_values($this->data['carriers']);
        }
    }
}
