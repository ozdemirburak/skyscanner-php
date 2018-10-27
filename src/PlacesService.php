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
     * @param string $apiKey
     */
    public function __construct($apiKey)
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
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get()
    {
        $data = $this->request(implode('/', [$this->url, 'geo', 'v1.0']));
        if (empty($data)) {
            throw new RestrictedMethodException(implode(' ', [
                'You don\'t have the permission to call this method.',
                'For more information, contact with Skyscanner here: https://partners.skyscanner.net/contact/'
            ]));
        }
        return $data;
    }

    /**
     * Get a list of places that match a query string.
     *
     * @param string $country
     * @param string $currency
     * @param string $locale
     * @param string $query
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getList($country, $currency, $locale, $query)
    {
        $url = implode('/', [$this->url, 'autosuggest', 'v1.0', $country, $currency, $locale]);
        $data = $this->request($url, compact('query'));
        return $data->Places ?? [];
    }

    /**
     * Get information about a country, city or airport using its ID.
     *
     * @param string $market
     * @param string $currency
     * @param string $locale
     * @param string $id
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getInformation($market, $currency, $locale, $id)
    {
        $url = implode('/', [$this->url, 'autosuggest', 'v1.0', $market, $currency, $locale]);
        $data = $this->request($url, compact('id'));
        return $data->Places ?? [];
    }

    /**
     * Retrieve a list of hotels and/or geographical locations which can then be used with the hotels and car hire APIs.
     * In the case of car hire, use this if you want downtown (non-airport) searches.
     *
     * @param string $country
     * @param string $currency
     * @param string $locale
     * @param string $query
     * @param bool   $removeIds
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getHotels($country, $currency, $locale, $query, $removeIds = true)
    {
        $url = implode('/', [$this->url, 'hotels', 'autosuggest', 'v2', $country, $currency, $locale, $query]);
        $data = $this->request($url);
        if (!empty($data->results)) {
            foreach ($data->results as &$result) {
                $id = array_search($result->parent_place_id, array_column($data->places, 'place_id'), true);
                $result->parent_place = $data->places[$id];
                if ($removeIds === true) {
                    unset($result->parent_place_id);
                }
            }
        }
        return $data;
    }
}
