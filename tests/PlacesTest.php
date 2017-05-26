<?php

namespace OzdemirBurak\SkyScanner\Tests;

use OzdemirBurak\SkyScanner\Exceptions\RestrictedMethodException;
use OzdemirBurak\SkyScanner\PlacesService;

class PlacesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group places-tests
     */
    public function testGet()
    {
        try {
            $this->getPlaces()->get();
        } catch (RestrictedMethodException $e) {
            return $this->assertContains('permission', $e->getMessage());
        }
        $this->fail('Exception has not been raised.');
    }

    /**
     * @group places-tests
     */
    public function testList()
    {
        $this->assertNotEmpty($this->getPlaces()->getList('UK', 'GBP', 'en-GB', 'istanbul'));
    }

    /**
     * @group places-tests
     */
    public function testInformation()
    {
        $this->assertNotEmpty($this->getPlaces()->getInformation('UK', 'GBP', 'en-GB', 'CDG-sky'));
    }

    /**
     * @group places-tests
     */
    public function testHotels()
    {
        $this->assertNotEmpty($this->getPlaces()->getHotels('UK', 'EUR', 'en-GB', 'paris'));
    }

    /**
     * @return \OzdemirBurak\SkyScanner\PlacesService
     */
    private function getPlaces()
    {
        return new PlacesService(API_KEY);
    }
}
