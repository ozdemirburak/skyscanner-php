# Unofficial SkyScanner PHP API

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Total Downloads][ico-downloads]][link-downloads]

This is an unofficial PHP API to support Flights, Car Hire and Hotels Services provided by 
[SkyScanner](http://business.skyscanner.net/portal/en-GB/Documentation/ApiOverview).

Currently, only the [Live Pricing Service](http://business.skyscanner.net/portal/en-GB/Documentation/FlightsLivePricingList) 
from the Flights Service is implemented. The others will be implemented in the near future.

## Install

Via Composer

``` bash
$ composer require ozdemirburak/skyscanner-php
```

## Usage

With the minimal setup, the Live Pricing Service can be used like below where it will return the flights 
from London Heathrow Airport to Istanbul Atatürk Airport which will take place in 1 week later from today
where it will also exclude the flights which are not direct with the prices for one adult.

``` php
$pricing = new LivePricing($apiKey = 'your-api-key', $country = 'GB', $currency = 'GBP', $locale = 'en-GB');
$pricing->setParameters([
    'adults' => 1,
    'destinationplace' => 'IST',
    'originplace' => 'LHR',
    'outbounddate' => Carbon::now()->addWeek(1)->format('Y-m-d'),
    'stops' => 0
]);
$flights = $pricing->parseFlights($onlyCheapestAgentPerItinerary = true);
```

After calling the `parseFlights()` method, some part of the data that is returned will look like below where it will
only return the agent with the lowest price.

``` php
0 => array:2 [
  "outbound_leg" => array:8 [
    "id" => "13554-1607191020-BA-0-12585-1607191620"
    "origin" => 13554
    "destination" => 12585
    "departs_at" => "2016-07-19T10:20:00"
    "arrives_at" => "2016-07-19T16:20:00"
    "duration" => 240
    "direction" => "Outbound"
    "flight_numbers" => array:1 [
      676 => array:1 [
        "carrier" => array:5 [
          "id" => 881
          "code" => "BA"
          "name" => "British Airways"
          "display_code" => "BA"
          "image_path" => "http://s1.apideeplink.com/images/airlines/BA.png"
        ]
      ]
    ]
  ]
  "agent" => array:9 [
    "price" => 245.45
    "url" => "http://partners.api.skyscanner.net/apiservices/deeplink/v2?_cje=jzj5DawL5zJyT%2bnfeP9GJWfImnVvZd7vh0AJSObmdOp8YP07VbGmhzc%2bVTc80nUp&url=http%3a%2f%2fwww.apideeplink.com%2ftransport_deeplink%2f4.0%2fUK%2fen-gb%2fGBP%2fomeg%2f1%2f13554.12585.2016-07-19%2fair%2ftrava%2fflights%3fitinerary%3dflight%7c-32480%7c676%7c13554%7c2016-07-19T10%3a20%7c12585%7c2016-07-19T16%3a20%26carriers%3d-32480%26passengers%3d1%2c0%2c0%26channel%3ddataapi%26cabin_class%3deconomy%26facilitated%3dfalse%26ticket_price%3d245.45%26is_npt%3dfalse%26is_multipart%3dfalse%26client_id%3dskyscanner_b2b%26request_id%3d2ed24021-23ba-4f55-9a1e-2b491e346cf2%26deeplink_ids%3deu-central-1.prod_2189ee66fcc0ebd8f20f8fdc4d05ebea%26commercial_filters%3dfalse%26q_datetime_utc%3d2016-07-12T13%3a14%3a01"
    "age" => 314
    "id" => 3496199
    "name" => "omegaflightstore.com"
    "status" => "UpdatesPending"
    "optimised_for_mobile" => false
    "type" => "TravelAgent"
    "image_path" => "http://s1.apideeplink.com/images/websites/omeg.png"
  ]
]
```

If you pass the `$onlyCheapestAgentPerItinerary` as `false` to the flight parser as 
`parseFlights($onlyCheapestAgentPerItinerary = false)`, then it will return all the agents with all the properties
that the `agent` property has. 

Furthermore, if you also indicate the `inbounddate` variable, then it will also return the `inbound_leg` just as the
same as the outbound leg.

``` php
0 => array:3 [
  "agents" => array:20 [
    0 => array:9 [ …9]
    1 => array:9 [ …9]
    2 => array:9 [ …9]
    3 => array:9 [ …9]
    4 => array:9 [ …9]
    5 => array:9 [ …9]
    6 => array:9 [ …9]
    7 => array:9 [ …9]
    8 => array:9 [ …9]
    9 => array:9 [ …9]
    10 => array:9 [ …9]
    11 => array:9 [ …9]
    12 => array:9 [ …9]
    13 => array:9 [ …9]
    14 => array:9 [ …9]
    15 => array:9 [ …9]
    16 => array:9 [ …9]
    17 => array:9 [ …9]
    18 => array:9 [ …9]
    19 => array:9 [ …9]
  ]
  "outbound_leg" => array:8 [
    "id" => "13554-1607191745-BA-0-12585-1607192340"
    "origin" => 13554
    "destination" => 12585
    "departs_at" => "2016-07-19T17:45:00"
    "arrives_at" => "2016-07-19T23:40:00"
    "duration" => 235
    "direction" => "Outbound"
    "flight_numbers" => array:1 [ …1]
  ]
  "inbound_leg" => array:8 [
    "id" => "12585-1607261350-BA-0-13554-1607261615"
    "origin" => 12585
    "destination" => 13554
    "departs_at" => "2016-07-26T13:50:00"
    "arrives_at" => "2016-07-26T16:15:00"
    "duration" => 265
    "direction" => "Inbound"
    "flight_numbers" => array:1 [ …1]
  ]
]
```

All the variable names are the same as indicated within the [SkyScanner API documentation]
(http://business.skyscanner.net/portal/en-GB/Documentation/FlightsLivePricingList). 

The initial parameters are the ones that are needed in the all API calls.

``` php
/**
 * ISO country code, or specified location schema
 *
 * @link https://en.wikipedia.org/wiki/ISO_3166-1#Current_codes
 *
 * @var string
 */
protected $country = 'GB';

/**
 * ISO currency code
 *
 * @link https://en.wikipedia.org/wiki/ISO_4217#Active_codes
 *
 * @var string
 */
protected $currency = 'GBP';

/**
 * ISO locale code (language and country)
 *
 * @link https://msdn.microsoft.com/en-us/library/ee825488(v=cs.20).aspx
 *
 * @var string
 */
protected $locale = 'en-GB';
```

The parameters specified below are specific to Live Pricing Service.

``` php

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
 * The number of children
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
 * The number of infants
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
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email mail@burakozdemir.co.uk instead of using the issue tracker.

## Credits

- [Burak Özdemir][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/ozdemirburak/skyscanner-php.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/ozdemirburak/skyscanner-php/master.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/ozdemirburak/skyscanner-php.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/ozdemirburak/skyscanner-php
[link-travis]: https://travis-ci.org/ozdemirburak/skyscanner-php
[link-downloads]: https://packagist.org/packages/ozdemirburak/skyscanner-php
[link-author]: https://github.com/ozdemirburak
[link-contributors]: ../../contributors
