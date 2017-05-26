<?php

namespace OzdemirBurak\SkyScanner\Travel\Flights;

use OzdemirBurak\SkyScanner\TravelService;
use OzdemirBurak\SkyScanner\Exceptions\InvalidMethodException;

class BrowseCache extends TravelService
{
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
     * Needed for X-Forwarded-For
     *
     * @var string
     */
    protected $ip;

    /**
     * @var string
     */
    protected $method = 'browsequotes';

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
     * @var array
     */
    protected $prices = [];

    /**
     * @return string
     */
    public function getUrl()
    {
        $uri = '{country}/{currency}/{locale}/{originPlace}/{destinationPlace}/{outboundPartialDate}/{inboundPartialDate}';
        $this->url = str_replace('{method}', $this->method, $this->url . '{method}/v1.0/') . $this->replaceParameters($uri);
        return $this->url;
    }

    /**
     * @return array
     * @throws \OzdemirBurak\SkyScanner\Exceptions\InvalidMethodException
     */
    public function getPrices()
    {
        if ($this->init()) {
            // If method is grid or routes, then there may not be any quotes
            if (!empty($this->data->Quotes)) {
                $this->prices['Quotes'] = $this->addQuotes();
            }
            if ($this->method !== 'browsequotes') {
                $method = ucwords(str_replace('browse', '', $this->method));
                $this->prices[$method] = $this->{'add' . $method}();
            }
            $this->prices['ReferralUrl'] = $this->data->ReferralUrl = $this->getReferralUrl();
        } else {
            $this->printErrorMessage($this->getResponseMessage());
        }
        return $this->prices;
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
     * @link http://business.skyscanner.net/portal/en-GB/Documentation/FlightsBrowseCacheQuotes
     */
    protected function addQuotes()
    {
        foreach ($this->data->Quotes as &$quote) {
            foreach (['OutboundLeg', 'InboundLeg'] as $leg) {
                if (isset($quote->$leg)) {
                    foreach ($quote->$leg->CarrierIds as $key => $carrierId) {
                        $carrier = $this->arraySearch($carrierId, $this->data->Carriers, 'CarrierId');
                        $quote->Carriers[] = $this->data->Carriers[$carrier];
                    }
                    if ($this->flattenSingleCarrier === true && count($quote->$leg->CarrierIds) === 1) {
                        $quote->Carrier = $quote->Carriers[0];
                        unset($quote->Carriers);
                    }
                    foreach (['Origin' => 'OriginId', 'Destination' => 'DestinationId'] as $variable => $search) {
                        $place = $this->arraySearch($quote->$leg->$search, $this->data->Places, 'PlaceId');
                        $quote->$variable = $this->data->Places[$place];
                    }
                }
                if ($this->removeIds === true) {
                    unset($quote->$leg->CarrierIds, $quote->$leg->OriginId, $quote->$leg->DestinationId);
                }
            }
        }
        return $this->data->Quotes;
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
     * @link http://business.skyscanner.net/portal/en-GB/Documentation/FlightsBrowseCacheRoutes
     *
     * @param array $routes
     *
     * @return array
     */
    protected function addRoutes(array $routes = [])
    {
        foreach ($this->data->Routes as $key => $route) {
            foreach (['Origin' => 'OriginId', 'Destination' => 'DestinationId'] as $index => $identifier) {
                $place = $this->arraySearch($route->$identifier, $this->data->Places, 'PlaceId');
                $routes[$key][$index] = $this->data->Places[$place];
            }
            $routes[$key] = $this->syncQuotes($route, $routes[$key], 'QuoteIds');
        }
        return $routes;
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
     * @param array $dates
     *
     * @return mixed
     *
     * @link     http://business.skyscanner.net/portal/en-GB/Documentation/FlightsBrowseCacheDates
     */
    protected function addDates(array $dates = [])
    {
        foreach (['OutboundDates', 'InboundDates'] as $date) {
            if (!empty($this->data->Dates->$date)) {
                foreach ($this->data->Dates->$date as $key => $datum) {
                    $dates[$key] = $this->syncQuotes($datum, [], 'QuoteIds');
                }
            }
        }
        return $dates;
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
     * @param array $grid
     *
     * @return array
     *
     * @link     http://business.skyscanner.net/portal/en-GB/Documentation/FlightsBrowseCacheGrid
     */
    protected function addGrid(array $grid = [])
    {
        if (isset($this->data->Dates[1])) {
            foreach ($this->data->Dates[1] as $key => $object) {
                if (!empty($object) && !empty($this->data->Dates[0][$key]->DateString)) {
                    $grid[$key] = $object;
                    $grid[$key]->DateString = $this->data->Dates[0][$key]->DateString;
                }
            }
        }
        return $grid;
    }

    /**
     * @param        $object
     * @param        $data
     * @param string $property
     *
     * @return mixed
     */
    protected function syncQuotes($object, $data, $property = 'QuoteIds')
    {
        if (!empty($object->$property)) {
            foreach ($object->$property as $quoteId) {
                $quote = $this->arraySearch($quoteId, $this->data->Quotes, 'QuoteId');
                $data['Quotes'][] = $this->data->Quotes[$quote];
            }
        }
        return $data;
    }

    /**
     * @link http://business.skyscanner.net/portal/en-GB/Documentation/Referrals
     *
     * @return string
     */
    protected function getReferralUrl()
    {
        return str_replace($this->method, 'referral', $this->url) . '?apiKey=' . substr($this->apiKey, 0, 16);
    }

    /**
     * @return bool
     * @throws \OzdemirBurak\SkyScanner\Exceptions\InvalidMethodException
     */
    private function init()
    {
        if (!in_array($this->method, $this->methods, true)) {
            throw new InvalidMethodException('Invalid Browse Cache method');
        }
        $this->prices = [];
        $this->get();
        return $this->getResponseStatus() === 200;
    }

    /**
     * @param bool $isGet
     *
     * @return array
     */
    protected function getRequestParameters($isGet = true)
    {
        return array_merge(parent::getRequestParameters($isGet), [
            'X-Forwarded-For' => $this->getXForwardedFor()
        ]);
    }

    /**
     * @return array
     */
    protected function getOptionalPollingParameters()
    {
        return [
            'destinationPlace'    => $this->destinationPlace,
            'inboundPartialDate'  => $this->inboundPartialDate,
            'originPlace'         => $this->originPlace,
            'outboundPartialDate' => !empty($this->outboundPartialDate) ? $this->outboundPartialDate :
                                     date('Y-m', strtotime('+1 month'))
        ];
    }
}
