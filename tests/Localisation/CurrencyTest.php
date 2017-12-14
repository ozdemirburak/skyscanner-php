<?php

namespace OzdemirBurak\SkyScanner\Tests\Localisation;

use OzdemirBurak\SkyScanner\Localisation\Currency;
use PHPUnit\Framework\TestCase;

class CurrencyTest extends TestCase
{
    /**
     * @group currency-tests
     */
    public function testWithoutApiKey()
    {
        $this->assertEmpty((new Currency(null))->get());
    }

    /**
     * @group currency-tests
     */
    public function testWithApiKey()
    {
        $this->assertNotEmpty((new Currency(API_KEY))->get());
    }
}
