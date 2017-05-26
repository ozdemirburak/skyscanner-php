<?php

namespace OzdemirBurak\SkyScanner\Tests\Localisation;

use OzdemirBurak\SkyScanner\Localisation\Market;

class MarketTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group market-tests
     */
    public function testWithoutApiKey()
    {
        $this->assertEmpty((new Market(null))->get());
    }

    /**
     * @group market-tests
     */
    public function testWithApiKey()
    {
        $this->assertNotEmpty((new Market(API_KEY))->get());
    }
}
