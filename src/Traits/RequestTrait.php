<?php

namespace OzdemirBurak\SkyScanner\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;

trait RequestTrait
{
    use ConsoleTrait;

    /**
     * @var string
     */
    protected $url = 'http://partners.api.skyscanner.net/apiservices';

    /**
     * @var string
     */
    protected $apiKey = '';

    /**
     * @param        $url
     * @param array  $parameters
     * @param string $requestMethod
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request($url, array $parameters = [], $requestMethod = 'GET')
    {
        try {
            $method = strtolower($requestMethod) === 'get' ? 'query' : 'form_params';
            $response = $this->getClient()->request($requestMethod, $url, [
                $method  => array_merge($this->getParams(), $parameters),
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
    public function getClient(): Client
    {
        return new Client();
    }

    /**
     * @return array
     */
    protected function getParams(): array
    {
        return ['apiKey' => $this->apiKey];
    }
}
