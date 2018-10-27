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
    protected $locale = 'en-GB';

    /**
     * @param string $locale
     */
    public function setLocale($locale): void
    {
        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function getUri(): string
    {
        return 'reference/v1.0/countries/' . $this->locale;
    }
}
