<?php

namespace OzdemirBurak\SkyScanner;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
use OzdemirBurak\SkyScanner\Traits\ConsoleTrait;
use OzdemirBurak\SkyScanner\Traits\RequestTrait;

abstract class LocalisationService
{
    use ConsoleTrait;

    /**
     * The API Key to identify the customer
     *
     * @link http://portal.business.skyscanner.net/en-gb/accounts/profile/
     *
     * @var string
     */
    protected $apiKey;

    /**
     * Data Property
     *
     * @var string
     */
    protected $property;

    /**
     * Request Uri
     *
     * @return string
     */
    abstract public function getUri();

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
     * @return mixed
     */
    public function get()
    {
        $data = $this->request('http://partners.api.skyscanner.net/apiservices/' . $this->getUri());
        return isset($data->{$this->property}) ? $data->{$this->property} : $data;
    }

    /**
     * @param        $url
     * @param string $requestMethod
     * @param string $method
     *
     * @return mixed
     */
    public function request($url, $requestMethod = 'GET', $method = 'query')
    {
        try {
            $response = $this->getClient()->request($requestMethod, $url, [
                $method  => $this->getParams(),
                'Accept' => 'application/json'
            ]);
            return json_decode($response->getBody());
        } catch (RequestException $e) {
            $this->printErrorMessage(Psr7\str($e->getResponse()), false);
        }
        return [];
    }

    /**
     * @return \GuzzleHttp\Client
     */
    public function getClient()
    {
        return new Client();
    }

    /**
     * @return array
     */
    protected function getParams()
    {
        return ['apiKey' => $this->apiKey];
    }
}
