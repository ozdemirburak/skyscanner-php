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
where it will also exclude the flights which are not direct with the prices for one person.

``` php
$pricing = new LivePricing($apiKey = 'your-api-key', $country = 'GB', $currency = 'GBP', $locale = 'en-GB');
$pricing->setParameters([
    'adults' => 1,
    'destinationplace' => 'IST',
    'originplace' => 'LHR',
    'outbounddate' => Carbon::now()->addWeek(1)->format('Y-m-d'),
    'stops' => 0
]);
$flights = $pricing->parseFlights();
```

All the variable names are the same as indicated within the SkyScanner API documentation. 

The initial parameters are the ones needed in the all API calls.

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
