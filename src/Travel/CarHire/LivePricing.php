<?php

namespace OzdemirBurak\SkyScanner\Travel\CarHire;

use OzdemirBurak\SkyScanner\TravelService;
use OzdemirBurak\SkyScanner\Traits\ImageTrait;

class LivePricing extends TravelService
{
    use ImageTrait;

    /**
     * API Endpoint
     *
     * @var string
     */
    protected $endpoint = 'carhire/liveprices/v2/';

    /**
     * API Uri
     *
     * @var string
     */
    protected $uri = '{country}/{currency}/{locale}/{pickupplace}/{dropoffplace}/{pickupdatetime}/{dropoffdatetime}/{driverage}?apiKey={apiKey}&userip={userip}';

    /**
     * @var string
     */
    protected $uriSession = '?apiKey={apiKey}&deltaExcludeWebsites={deltaExcludeWebsites}';

    /**
     * Main data property that contains pricing information
     *
     * @var string
     */
    protected $property = 'cars';

    /**
     * A list of website IDs whose results you want to discard, or an empty string
     * Must be CSV or semicolon-separated values
     *
     * @var array|string
     */
    protected $deltaExcludeWebsites;

    /**
     * The age of the driver that must be between 21 and 75
     *
     * @var integer
     */
    protected $driverage = 21;

    /**
     * Date and time for dropoff
     * Formatted as ISO Date and Time format (YYYY-MM-DDThh:mm)
     *
     * @var string
     */
    protected $dropoffdatetime;

    /**
     * The dropoff location
     *
     * IATA code or autosuggest place ID or a latitude,longitude pair formatted like 55.95,-3.37-latlong
     *
     * @link http://business.skyscanner.net/portal/en-GB/Documentation/HotelsAutoSuggest
     * @var string
     */
    protected $dropoffplace = 'MAN';

    /**
     * Date and time for pickup
     * Formatted as ISO Date and Time format (YYYY-MM-DDThh:mm)
     *
     * @var string
     */
    protected $pickupdatetime;

    /**
     * The pickup location
     * IATA code or autosuggest place ID or a latitude,longitude pair formatted like 55.95,-3.37-latlong
     *
     * @link http://business.skyscanner.net/portal/en-GB/Documentation/HotelsAutoSuggest
     *
     * @var string
     */
    protected $pickupplace = 'LHR';

    /**
     * Save remote car images to local where urls are returned from the request
     *
     * @var bool
     */
    protected $saveCarImages = false;

    /**
     * Save remote website images to local where urls are returned from the request
     *
     * @var bool
     */
    protected $saveWebsiteImages = false;

    /**
     * Assign the return value of function getIpAddress() to userip
     *
     * @var array
     */
    protected $extraParameters = ['userip' => 'getIpAddress'];

    /**
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUrl(): string
    {
        [$id, $this->uri] = [$this->getSessionId($this->url . $this->endpoint, 'GET'), $this->uriSession];
        return $this->url . $this->endpoint . $id;
    }

    /**
     * @return array|bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCars()
    {
        if ($this->init()) {
            $properties = [
                'car_class_id' => ['car_class', 'car_classes'],
                'image_id'     => ['car_image', 'images'],
                'website_id'   => ['website', 'websites']
            ];
            foreach ($this->data->cars as &$car) {
                // assign car class, image and website
                foreach ($properties as $key => $values) {
                    if (isset($this->data->{$values[1]})) {
                        $id = $this->arraySearch($car->$key, $this->data->{$values[1]}, 'id');
                        $car->{$values[0]} = $this->data->{$values[1]}[$id];
                    }
                }
                // remove IDs
                if ($this->removeIds === true) {
                    unset($car->car_class_id, $car->image_id, $car->website_id);
                }
                // save images
                $imageProperties = [
                    'saveWebsiteImages' => ['website', 'image_url'],
                    'saveCarImages'     => ['car_image', 'url']
                ];
                foreach ($imageProperties as $boolean => $property) {
                    if ($this->$boolean === true) {
                        $car->{$property[0]}->{$property[1]} = $this->saveImage(
                            $car->{$property[0]}->{$property[1]},
                            $this->savePath
                        );
                    }
                }
            }
            return $this->data->cars;
        }
        $this->printErrorMessage($this->getResponseMessage());
        return [];
    }

    /**
     * @return array
     */
    protected function getDefaultParameters(): array
    {
        return array_merge(parent::getDefaultParameters(), [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ]);
    }
}
