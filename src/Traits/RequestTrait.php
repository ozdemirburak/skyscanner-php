<?php

namespace OzdemirBurak\SkyScanner\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;

trait RequestTrait
{
    use ConsoleTrait;

    /**
     * @param $url
     *
     * @return mixed
     */
    public function get($url)
    {
        return $this->request($url);
    }

    /**
     * @param $url
     *
     * @return mixed
     */
    public function post($url)
    {
        return $this->request($url, 'POST', 'form_params');
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
     * Create a client
     *
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
        return [];
    }
}
