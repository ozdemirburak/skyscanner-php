<?php

namespace OzdemirBurak\SkyScanner\Localisation;

use OzdemirBurak\SkyScanner\LocalisationService;

class Locale extends LocalisationService
{
    /**
     * @var string
     */
    protected $property = 'Locales';

    /**
     * @return string
     */
    public function getUri()
    {
        return 'reference/v1.0/locales';
    }
}
