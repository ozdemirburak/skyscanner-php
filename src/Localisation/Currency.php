<?php

namespace OzdemirBurak\SkyScanner\Localisation;

use OzdemirBurak\SkyScanner\LocalisationService;

class Currency extends LocalisationService
{
    /**
     * @var string
     */
    protected $property = 'Currencies';

    /**
     * @return string
     */
    public function getUri(): string
    {
        return 'reference/v1.0/currencies';
    }
}
