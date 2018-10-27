<?php

namespace OzdemirBurak\SkyScanner;

use OzdemirBurak\SkyScanner\Traits\RequestTrait;

abstract class LocalisationService
{
    use RequestTrait;

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
    abstract public function getUri(): string;

    /**
     * LocalisationService constructor.
     *
     * @param $apiKey
     */
    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get()
    {
        $data = $this->request(implode('/', [$this->url, $this->getUri()]));
        return $data->{$this->property} ?? $data;
    }
}
