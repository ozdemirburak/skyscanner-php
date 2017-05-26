<?php

namespace OzdemirBurak\SkyScanner\Tests\Localisation;

use OzdemirBurak\SkyScanner\Localisation\Currency;

class CurrencyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group currency-tests
     */
    public function testWithoutApiKey()
    {
        $this->assertEmpty((new Currency(null))->fetch());
    }

    /**
     * @group currency-tests
     */
    public function testWithApiKey()
    {
        $this->assertNotEmpty((new Currency(API_KEY))->fetch());
    }
}
