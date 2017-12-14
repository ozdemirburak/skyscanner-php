<?php

namespace OzdemirBurak\SkyScanner\Tests\Localisation;

use OzdemirBurak\SkyScanner\Localisation\Locale;
use PHPUnit\Framework\TestCase;

class LocaleTest extends TestCase
{
    /**
     * @group locale-tests
     */
    public function testWithoutApiKey()
    {
        $this->assertEmpty((new Locale(null))->get());
    }

    /**
     * @group locale-tests
     */
    public function testWithApiKey()
    {
        $this->assertNotEmpty((new Locale(API_KEY))->get());
    }
}
