<?php

namespace OzdemirBurak\SkyScanner\Tests\Localisation;

use OzdemirBurak\SkyScanner\Localisation\Market;
use PHPUnit\Framework\TestCase;

class MarketTest extends TestCase
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
        $this->assertNotEmpty((new Market(API_KEY_1))->get());
    }
}
