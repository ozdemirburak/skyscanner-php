<?php

namespace OzdemirBurak\SkyScanner;

use OzdemirBurak\SkyScanner\Exceptions\RestrictedMethodException;
use OzdemirBurak\SkyScanner\Traits\RequestTrait;

class PlacesService
{
    use RequestTrait;

    /**
     * LocalisationService constructor.
     *
     * @param $apiKey
     */
    public function __construct($apiKey = '')
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Get the full list of all the places that SkyScanner supports.
     * You need to contact and grant permission from SkyScanner to use this method
     *
     * https://partners.skyscanner.net/contact/
     *
     * @return mixed
     * @throws \OzdemirBurak\SkyScanner\Exceptions\RestrictedMethodException
     */
    public function get()
    {
        $data = $this->request(implode('/', [$this->url, 'geo', 'v1.0']));
        if (empty($data)) {
            throw new RestrictedMethodException('You don\'t have the permission to call this method, contact SkyScanner via https://partners.skyscanner.net/contact/ to request permission.');
        }
        return $data;
    }

    /**
     * Get a list of places that match a query string.
     *
     * @param $country
     * @param $currency
     * @param $locale
     * @param $query
     *
     * @return mixed
     */
    public function getList($country, $currency, $locale, $query)
    {
        $url = implode('/', [$this->url, 'autosuggest', 'v1.0', $country, $currency, $locale]);
        $data = $this->request($url, compact('query'));
        return isset($data->Places) ? $data->Places : [];
    }

    /**
     * Get information about a country, city or airport using its ID.
     *
     * @param $market
     * @param $currency
     * @param $locale
     * @param $id
     *
     * @return mixed
     */
    public function getInformation($market, $currency, $locale, $id)
    {
        $url = implode('/', [$this->url, 'autosuggest', 'v1.0', $market, $currency, $locale]);
        $data = $this->request($url, compact('id'));
        return isset($data->Places) ? $data->Places : [];
    }

    /**
     * Retrieve a list of hotels and/or geographical locations which can then be used with the hotels and car hire APIs.
     * In the case of car hire, use this if you want downtown (non-airport) searches.
     *
     * @param $country
     * @param $currency
     * @param $locale
     * @param $query
     *
     * @return mixed
     */
    public function getHotels($country, $currency, $locale, $query)
    {
        $url = implode('/', [$this->url, 'hotels', 'autosuggest', 'v2', $country, $currency, $locale, $query]);
        return $this->request($url);
    }
}
