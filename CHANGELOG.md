# Changelog

All Notable changes to `skyscanner-php` will be documented in this file

## 2018-10-27
- Remove country, currency and locale initialization from constructor.
- Remove `dd` helper.

## 2018-10-21
- Added experimental Hotels\LivePricing
- Remove support for PHP 7.0 and below.
- Renamed parameter names in Flight: Live Pricing, using camelCase parameter names now instead of all lowered.
- Changed the logic in `getUrl` implementations.

## 2017-12-14
- Updated PHPUnit

## 2017-05-26
- Major refactoring, not renaming the original object properties returned by the API itself
- Renamed Flights\LivePricing method name `parseFlights` to `getFlights`
- Renamed Flights\BrowseCache method name `getData` to `getPrices`
- Added raw `get()` method for reading data without any modifications 
- Added CarHire\LivePricing
- Added Localisation\Currency
- Added Localisation\Locale
- Added Localisation\Market

## 2016-09-22
- Removed Carbon dependency and PHP5.5 support

## 2016-09-11
- Added Flights\BrowseCache

## 2016-07-12
- First release with Flights\LivePrices component of the Travel API
