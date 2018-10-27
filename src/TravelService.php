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
    protected $apiKey;

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
     * SkyScanner Service Endpoint
     *
     * @var string
     */
    protected $endpoint;

    /**
     * Instead of declaring some parameters, one can use this variable to assign parameter value by function.
     * Example: ['ip' => getIpAddress] => assigns the value of getIpAddress to ip in getParameters(), getUri() methods.
     *
     * @var array
     */
    protected $extraParameters = [];

    /**
     * Client IP, needed for X-Forwarded-For value
     *
     * @var string
     */
    protected $ip;

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
     * SkyScanner Service URI
     *
     * @var string
     */
    protected $uri;

    /**
     * Main data properties that contains information about Travel Services like "cars", "Itineraries" etc.
     *
     * @var string
     */
    protected $property;

    /**
     * URL to make the query
     *
     * @return string
     */
    abstract public function getUrl(): string;

    /**
     * Auth constructor.
     *
     * @param string $apiKey
     */
    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
        $this->client = $this->getClient();
    }

    /**
     * @param $parameters
     */
    public function setParameters(array $parameters): void
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
     * @param string $url
     * @param string $method
     *
     * @return bool|mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function makeRequest($url, $method = 'GET')
    {
        try {
            [$url, $parameters] = $this->getRequestUrlAndParameters($url, $method === 'GET');
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
     * Just return data property without doing any modifications to the original one
     *
     * @param string $property
     * @param bool   $reset
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get($property = null, $reset = false)
    {
        if (empty($this->data) || $reset === true) {
            $this->makeRequest($this->getUrl(), 'GET');
            $this->data = $this->getResponseBody();
        }
        if ($property !== null) {
            return $this->data->{$property} ?? [];
        }
        return $this->data;
    }

    /**
     * Initialize and store data
     *
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function init(): bool
    {
        $this->get();
        return !empty($this->data->{$this->property});
    }

    /**
     * @param $key
     *
     * @return mixed|null
     */
    public function getParameter($key)
    {
        return $this->$key ?? null;
    }

    /**
     * Get Response Status
     *
     * @return integer
     */
    public function getResponseStatus(): int
    {
        return $this->response->getStatusCode();
    }

    /**
     * @param bool $withStatusCode
     *
     * @return string
     */
    public function getResponseMessage($withStatusCode = true): string
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
    public function getResponseHeader($key, $first = true): string
    {
        $header = $this->response->getHeader($key);
        $headerFirst = $header[0] ?? '';
        return $first ? $headerFirst : $header;
    }

    /**
     * Create a client
     *
     * @return \GuzzleHttp\Client
     */
    public function getClient(): Client
    {
        return $this->client ?? new Client();
    }

    /**
     * If you make too many requests, then you will probably have issues on rate limiting.
     * So, until a non-empty location is received, it will make a request to get a session key.
     *
     * In other words, until the rate limit is reset, it will make a request to obtain a session key
     *
     * @param string $url
     * @param string $method
     *
     * Message: Rate limit has been exceeded: 100 PerMinute for PricingSession
     *
     * @return bool|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getSessionId($url, $method = 'POST')
    {
        if ($this->makeRequest($url, $method) === false) {
            $this->printErrorMessage('Could not fetch a valid session id.');
            return false;
        }
        $location = $this->getResponseHeader('Location');
        $locationParameters = explode('/', $location);
        $id = end($locationParameters);
        if (strpos($id, '?') !== false) {
            // cars live pricing return apikey within the session for some reason.
            [$id, $others] = explode('?', $id, 2);
        }
        return $id;
    }

    /**
     * @param string $url
     * @param bool  $isGet
     *
     * @return array
     */
    public function getRequestUrlAndParameters($url, $isGet): array
    {
        if ($isGet === true) {
            return [$url . $this->getUri(), $this->getDefaultParameters()];
        }
        return [$url, array_merge($this->getDefaultParameters(), ['form_params' => $this->getParameters()])];
    }

    /**
     * Default parameters for requests
     *
     * @return array
     */
    protected function getDefaultParameters(): array
    {
        return ['Accept' => 'application/json'];
    }

    /**
     * Create direct query link via replacing uri
     *
     * @return string
     */
    public function getUri(): string
    {
        foreach ($this->getParameterArray() as $parameter) {
            if (property_exists($this, $parameter)) {
                if (isset($this->$parameter)) {
                    $this->uri = str_replace('{' . $parameter . '}', $this->$parameter, $this->uri);
                } else {
                    $this->uri = str_replace('&'. $parameter . '={' . $parameter . '}', '', $this->uri);
                }
            }
        }
        foreach ($this->extraParameters as $parameter => $function) {
            $this->uri = str_replace('{' . $parameter . '}', $this->$function(), $this->uri);
        }
        return $this->uri;
    }

    /**
     * Get parameter array to create query string from parameters
     *
     * @param array $parameters
     *
     * @return array
     */
    public function getParameters(array $parameters = []): array
    {
        foreach (array_merge($parameters, $this->getParameterArray()) as $parameter) {
            if (isset($this->$parameter)) {
                $parameters[$parameter] = $this->$parameter;
            }
        }
        foreach ($this->extraParameters as $parameter => $function) {
            $parameters[$parameter] = $this->$function();
        }
        return $this->filterArray($parameters);
    }

    /**
     * Get everything between {curly braces} in uri string
     *
     * @return array
     */
    protected function getParameterArray(): array
    {
        preg_match_all('/{(.*?)}/', $this->uri, $parameters);
        return $parameters[1];
    }

    /**
     * Filter array and return not empty ones (allows 0 and false)
     *
     * @param array $array
     *
     * @return array
     */
    protected function filterArray(array $array): array
    {
        return array_filter($array, function ($value) {
            return $value !== null && $value !== '';
        });
    }

    /**
     * Helper method for array search to locate the property with the given property
     *
     * @param $needle
     * @param $haystack
     * @param $property
     *
     * @return false|int|string
     */
    protected function arraySearch($needle, $haystack, $property)
    {
        return array_search($needle, array_map(function ($value) use ($property) {
            return \is_object($value) ? $value->$property : $value[$property];
        }, $haystack), true);
    }

    /**
     * Get IP Address
     *
     * @link https://gist.github.com/cballou/2201933
     * @return string
     */
    public function getIpAddress(): string
    {
        if ($this->ip !== null) {
            return $this->ip;
        }
        $keys = [
            'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'
        ];
        foreach ($keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    if ($this->validateIp($ip = trim($ip))) {
                        return $ip;
                    }
                }
            }
        }
        return '127.0.0.1';
    }

    /**
     * @param $ip
     *
     * @return mixed
     */
    protected function validateIp($ip)
    {
        $filter = FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
        return filter_var($ip, FILTER_VALIDATE_IP, $filter);
    }

    /**
     * Messages returned by the Skyscanner API
     *
     * @return array
     */
    protected function getResponseMessages(): array
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
}
