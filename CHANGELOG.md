# Changelog

All Notable changes to `skyscanner-php` will be documented in this file

## 2017-05-26
- Major refactoring, not renaming the original object properties returned by the API itself
- Renamed Flights\LivePrices method name `parseFlights` to `getFlights`
- Renamed Flights\BrowseCache method name `getData` to `getPrices`
- Added raw `get()` method for reading data without any modifications 
- Added CarHire\LivePrices
- Added Localisation\Currency
- Added Localisation\Locale
- Added Localisation\Market

## 2016-09-22
- Removed Carbon dependency and PHP5.5 support

## 2016-09-11
- Added Flights\BrowseCache

## 2016-07-12
- First release with Flights\LivePrices component of the Travel API
