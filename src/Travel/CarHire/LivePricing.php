<?php

namespace OzdemirBurak\SkyScanner\Travel\CarHire;

use OzdemirBurak\SkyScanner\TravelService;
use OzdemirBurak\SkyScanner\Traits\ImageTrait;

class LivePricing extends TravelService
{
    use ImageTrait;

    /**
     * A list of website IDs whose results you want to discard, or an empty string
     *
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
     *
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
     *
     * @var string
     */
    protected $dropoffplace = 'ADB';

    /**
     * Date and time for pickup
     *
     * Formatted as ISO Date and Time format (YYYY-MM-DDThh:mm)
     *
     * @var string
     */
    protected $pickupdatetime;

    /**
     * The pickup location
     *
     * IATA code or autosuggest place ID or a latitude,longitude pair formatted like 55.95,-3.37-latlong
     *
     * @link http://business.skyscanner.net/portal/en-GB/Documentation/HotelsAutoSuggest
     *
     * @var string
     */
    protected $pickupplace = 'IST';

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
     * @return string
     */
    public function getUrl()
    {
        $this->url .= 'carhire/liveprices/v2/';
        $uri = '{country}/{currency}/{locale}/{pickupplace}/{dropoffplace}/{pickupdatetime}/{dropoffdatetime}/{driverage}';
        $url = $this->url . $this->replaceParameters($uri);
        $url = $this->url . $this->getSessionKey($this->getPollingQueryUrl($url), 'GET');
        return $url;
    }

    /**
     * @return array
     */
    public function getCars()
    {
        if ($this->init()) {
            $properties = ['car_class_id' => ['car_class', 'car_classes'], 'image_id' => ['car_image', 'images'], 'website_id' => ['website', 'websites']];
            foreach ($this->data->cars as &$car) {
                // assign car class, image and website
                foreach ($properties as $key => $values) {
                    $id = $this->arraySearch($car->$key, $this->data->{$values[1]}, 'id');
                    $car->{$values[0]} = $this->data->{$values[1]}[$id];
                }
                // remove IDs
                if ($this->removeIds === true) {
                    unset($car->car_class_id, $car->image_id, $car->website_id);
                }
                // save images
                $imageProperties = ['saveWebsiteImages' => ['website', 'image_url'], 'saveCarImages' => ['car_image', 'url']];
                foreach ($imageProperties as $boolean => $property) {
                    if ($this->$boolean === true) {
                        $car->{$property[0]}->{$property[1]} = $this->saveImage($car->{$property[0]}->{$property[1]}, $this->savePath);
                    }
                }
            }
        } else {
            $this->printErrorMessage($this->getResponseMessage());
        }
        return $this->data->cars;
    }

    /**
     * Initialize and store data
     *
     * @return bool
     */
    private function init()
    {
        $this->get();
        return !empty($this->data->cars);
    }

    /**
     * @return array
     */
    protected function getSpecificSessionParameters()
    {
        return $this->filterArray([
            'driverage'         => $this->driverage,
            'dropoffplace'      => $this->dropoffplace,
            'pickupplace'       => $this->pickupplace,
            'pickupdatetime'    => !empty($this->pickupdatetime) ? $this->pickupdatetime :
                                   date('Y-m-d\TH:i', strtotime('+1 week')),
            'dropoffdatetime'   => !empty($this->dropoffdatetime) ? $this->dropoffdatetime :
                                   date('Y-m-d\TH:i', strtotime('+2 week'))
        ]);
    }

    /**
     * @return array
     */
    protected function getOptionalPollingParameters()
    {
        return $this->filterArray([
            'userip'               => $this->getXForwardedFor(),
            'deltaExcludeWebsites' => is_array($this->deltaExcludeWebsites) ?
                implode(',', $this->deltaExcludeWebsites) :
                $this->deltaExcludeWebsites
        ]);
    }
}
