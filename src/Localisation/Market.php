<?php

namespace OzdemirBurak\SkyScanner\Localisation;

use OzdemirBurak\SkyScanner\LocalisationService;

class Market extends LocalisationService
{
    /**
     * @var string
     */
    protected $property = 'Countries';

    /**
     * @var string
     */
    protected $locale;

    /**
     * Market constructor.
     *
     * @param string $apiKey
     * @param string $locale
     */
    public function __construct($apiKey, $locale = 'en-GB')
    {
        $this->locale = $locale;
        parent::__construct($apiKey);
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return 'reference/v1.0/countries/' . $this->locale;
    }
}
