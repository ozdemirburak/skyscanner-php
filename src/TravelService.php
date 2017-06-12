<?php

namespace OzdemirBurak\SkyScanner;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
use OzdemirBurak\SkyScanner\Traits\ConsoleTrait;

abstract class TravelService
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
     * Data returned by SkyScanner API Request
     *
     * @var mixed
     */
    protected $data;

    /**
     * ISO locale code (language and country)
     *
     * @link https://msdn.microsoft.com/en-us/library/ee825488(v=cs.20).aspx
     *
     * @var string
     */
    protected $locale = 'en-GB';

    /**
     * Keep or remove IDs after being used to find the given relations such as Carrier Information
     *
     * @var bool
     */
    protected $removeIds = false;

    /**
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $response;

    /**
     * SkyScanner Request Provider
     *
     * @var string
     */
    protected $url = 'http://partners.api.skyscanner.net/apiservices/';

    /**
     * URL to make the query
     *
     * @return string
     */
    abstract public function getUrl();

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
    public function setParameters(array $parameters)
    {
        foreach ($parameters as $property => $value) {
            if (property_exists($this, $property)) {
                $this->$property = $value;
            } else {
                $this->printErrorMessage('Invalid property name: ' . $property);
            }
        }
    }

    /**
     * @param string $method
     * @param string $url
     * @param array  $parameters
     *
     * @return bool|mixed|\Psr\Http\Message\ResponseInterface
     */
    public function makeRequest($method = 'GET', $url = '', array $parameters = [])
    {
        try {
            $parameters = !empty($parameters) ? $parameters : $this->getRequestParameters(strtolower($method) === 'get');
            return $this->response = $this->client->request($method, $url, $parameters);
        } catch (RequestException $e) {
            $this->printErrorMessage(Psr7\str($e->getRequest()), false);
            if ($e->hasResponse()) {
                $this->response = $e->getResponse();
                $this->printErrorMessage(Psr7\str($e->getResponse()), false);
            }
        }
        return false;
    }

    /**
     * The parameters used for the requests to the Skyscanner API
     *
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
     * Just return data property without doing any modifications to the original one
     *
     * @param string $property
     * @param bool   $reset
     *
     * @return mixed
     */
    public function get($property = null, $reset = false)
    {
        if (empty($this->data) || $reset === true) {
            $this->makeRequest('GET', $this->getUrl());
            $this->data = $this->getResponseBody();
        }
        return !empty($property) ? $this->data->{$property} : $this->data;
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
     * @return integer
     */
    public function getResponseStatus()
    {
        return $this->response->getStatusCode();
    }

    /**
     * @param bool $withStatusCode
     *
     * @return mixed|string
     */
    public function getResponseMessage($withStatusCode = true)
    {
        $message = array_key_exists($status = $this->getResponseStatus(), $messages = $this->getResponseMessages()) ?
            $messages[$status] : 'Unknown response';
        return $withStatusCode ? implode(' - ', [$status, $message]) : $message;
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
     * Response body, in JSON format if the Accept header is set as 'application/json'
     *
     * @param bool $decode
     *
     * @return mixed
     */
    public function getResponseBody($decode = true)
    {
        $data = $this->response->getBody();
        return $decode === true ? json_decode($data) : $data;
    }

    /**
     * Returns specific response header defined by key
     *
     * @param      $key
     * @param bool $first
     *
     * @return string
     */
    public function getResponseHeader($key, $first = true)
    {
        $header = $this->response->getHeader($key);
        $headerFirst = isset($header[0]) ? $header[0] : '';
        return $first ? $headerFirst : $header;
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
     * If you make too many requests, then you will probably have issues on rate limiting.
     * So, until a non-empty location is received, it will make a request to get a session key.
     *
     * In other words, until the rate limit is reset, it will make a request to obtain a session key
     *
     * @param string $url
     * @param string $method
     * @param string $location
     *
     * Message: Rate limit has been exceeded: 100 PerMinute for PricingSession
     *
     * @return string
     */
    protected function getSessionKey($url, $method = 'POST', $location = '')
    {
        while (empty($location)) {
            $this->makeRequest($method, $url);
            $location = $this->getResponseHeader('Location');
        }
        $locationParameters = explode('/', $location);
        return end($locationParameters);
    }

    /**
     * @param bool $isGet
     *
     * @return array
     */
    protected function getRequestParameters($isGet = true)
    {
        return [
            $this->getMethod($isGet) => $this->getParameters($isGet),
            'Accept' => 'application/json'
        ];
    }

    /**
     * @param bool $isGet
     *
     * @return string
     */
    protected function getMethod($isGet = true)
    {
        return $isGet === true ? 'query' : 'form_params';
    }

    /**
     * Replace parameters within the given string with their values.
     * For instance replace the string {currency} with the string USD
     *
     * @param string $string
     *
     * @return mixed
     */
    protected function replaceParameters($string)
    {
        foreach ($this->getParameters() as $parameter => $value) {
            $search = '{' . $parameter . '}';
            if (strpos($string, $search) !== false) {
                $string = str_replace($search, $value, $string);
            }
        }
        return $string;
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
     * Helper method for array search to locate the property with the given property
     *
     * @param $needle
     * @param $haystack
     * @param $property
     *
     * @return mixed
     */
    protected function arraySearch($needle, $haystack, $property)
    {
        return array_search($needle, array_map(function ($value) use ($property) {
            return is_object($value) ? $value->$property : $value[$property];
        }, $haystack), true);
    }

    /**
     * Get the X-Forwarded-For
     *
     * @return string
     */
    protected function getXForwardedFor()
    {
        return !empty($this->ip) ? $this->ip :
            getenv('HTTP_CLIENT_IP') ?: getenv('HTTP_X_FORWARDED_FOR') ?: getenv('HTTP_X_FORWARDED') ?:
                getenv('HTTP_FORWARDED_FOR') ?: getenv('HTTP_FORWARDED') ?: getenv('REMOTE_ADDR') ?: '127.0.0.1';
    }

    /**
     * Messages returned by the Skyscanner API
     *
     * @return array
     */
    protected function getResponseMessages()
    {
        return [
            200 => 'Success',
            201 => 'Created – The session has been created.',
            204 => 'No Content – The session is still being created (wait and try again).',
            304 => 'Not Modified – The results have not been modified since the last poll.',
            400 => 'Bad Request – Input validation failed.',
            403 => 'Forbidden – The API Key was not supplied, or it was invalid, or it is not authorized to access.',
            410 => 'Gone – The session has expired. A new session must be created.',
            429 => 'Too Many Requests – There have been too many requests in the last minute.',
            500 => 'Server Error – An internal server error has occurred which has been logged.'
        ];
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
