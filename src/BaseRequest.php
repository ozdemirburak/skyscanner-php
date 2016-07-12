<?php

namespace OzdemirBurak\SkyScanner;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
use OzdemirBurak\SkyScanner\Traits\ConsoleTrait;

abstract class BaseRequest
{
    use ConsoleTrait;

    /**
     * The API Key to identify the customer
     *
     * @link http://portal.business.skyscanner.net/en-gb/accounts/profile/
     *
     * @var string
     */
    protected $apiKey = '';

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

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

    /**
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $response;
    
    /**
     * SkyScanner Request Provider
     *
     * @var string
     */
    protected $url = '';

    /**
     * Auth constructor.
     *
     * @param string $apiKey
     * @param string $country
     * @param string $currency
     * @param string $locale
     */
    public function __construct($apiKey = '', $country = '', $currency = '', $locale = '')
    {
        foreach (['apiKey', 'country', 'currency', 'locale'] as $variable) {
            $this->assignVariableOrDefault($variable, $$variable);
        }
        $this->client = $this->getClient();
    }

    /**
     * @param $variableName
     * @param $variableValue
     */
    public function assignVariableOrDefault($variableName, $variableValue)
    {
        $this->$variableName = !empty($variableValue) ? $variableValue : $this->$variableName;
    }

    /**
     * @param $parameters
     */
    public function setParameters($parameters)
    {
        if (is_array($parameters)) {
            foreach ($parameters as $property => $value) {
                if (property_exists($this, $property)) {
                    $this->$property = $value;
                } else {
                    $this->printErrorMessage('Invalid property name: ' . $property);
                }
            }
        } else {
            $this->printErrorMessage('Parameters that are passed must be an array with keys and values.');
        }
    }

    /**
     * @param string $method
     * @param string $url
     * @param array  $parameters
     *
     * @return bool|mixed|\Psr\Http\Message\ResponseInterface
     */
    public function makeRequest($method = 'GET', $url = '', $parameters = [])
    {
        try {
            $isGet = strtolower($method) === 'get' ? true : false;
            $parameters = !empty($parameters) ? $parameters : $this->getRequestsParameters($isGet);
            $this->response = $this->client->request($method, !empty($url) ? $url : $this->url, $parameters);
            return $this->response;
        } catch (RequestException $e) {
            $this->printErrorMessage(Psr7\str($e->getRequest()));
            if ($e->hasResponse()) {
                $this->response = $e->getResponse();
                $this->printErrorMessage(Psr7\str($e->getResponse()));
            }
        }
        return false;
    }

    /**
     * @param bool $isGet
     *
     * @return array
     */
    protected function getRequestsParameters($isGet = true)
    {
        $method = $isGet === true ? 'query' : 'form_params';
        return [
            $method => $this->getParameters($isGet),
            'Accept' => 'application/json'
        ];
    }

    /**
     * @param bool $isGet
     *
     * @return array
     */
    public function getParameters($isGet = true)
    {
        return array_merge([
            'apiKey'   => $this->apiKey,
            'country'  => $this->country,
            'currency' => $this->currency,
            'locale'   => $this->locale
        ], $this->getSpecificSessionParameters(), $isGet ? $this->getOptionalPollingParameters() : []);
    }

    /**
     * @param array $array
     *
     * @return array
     */
    protected function filterArray(array $array)
    {
        return array_filter($array, function ($value) {
            return ($value !== null && $value !== false && $value !== '');
        });
    }

    /**
     * @param $key
     *
     * @return mixed|null
     */
    public function getParameter($key)
    {
        $parameters = $this->getParameters();
        return isset($parameters[$key]) ? $parameters[$key] : null;
    }

    /**
     * Get Response Status
     *
     * 200 => Success
     * 201 => Created – The session has been created.
     * 204 => No Content – The session is still being created (wait and try again).
     * 304 => Not Modified – The results have not been modified since the last poll.
     * 400 => Bad Request – Input validation failed.
     * 403 => Forbidden – The API Key was not supplied, or it was invalid, or it is not authorized to access.
     * 410 => Gone – The session has expired. A new session must be created.
     * 429 => Too Many Requests – There have been too many requests in the last minute.
     * 500 => Server Error – An internal server error has occurred which has been logged.
     *
     * @return integer
     */
    public function getResponseStatus()
    {
        return $this->response->getStatusCode();
    }

    /**
     * Response headers
     *
     * @return mixed
     */
    public function getResponseHeaders()
    {
        return $this->response->getHeaders();
    }

    /**
     * Returns specific response header defined by key
     *
     * @param string $key
     * @param bool   $first
     *
     * @return null
     */
    public function getResponseHeader($key, $first = true)
    {
        $header = $this->response->getHeader($key);
        return $first ? $header[0] : $header;
    }

    /**
     * Create a client
     *
     * @return \GuzzleHttp\Client
     */
    public function getClient()
    {
        return empty($this->client) ? new Client() : $this->client;
    }

    /**
     * Class specific post parameters for creating the session
     *
     * @return array
     */
    protected function getSpecificSessionParameters()
    {
        return [];
    }

    /**
     * Class specific get parameters for polling the results
     *
     * @return array
     */
    protected function getOptionalPollingParameters()
    {
        return [];
    }
}
