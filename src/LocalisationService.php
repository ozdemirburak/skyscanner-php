<?php

namespace OzdemirBurak\SkyScanner;

use OzdemirBurak\SkyScanner\Traits\RequestTrait;

abstract class LocalisationService
{
    use RequestTrait;

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
    public function fetch()
    {
        $data = $this->get('http://partners.api.skyscanner.net/apiservices/' . $this->getUri());
        return isset($data->{$this->property}) ? $data->{$this->property} : $data;
    }

    /**
     * @return array
     */
    protected function getParams()
    {
        return ['apiKey' => $this->apiKey];
    }
}
