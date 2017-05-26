<?php

namespace OzdemirBurak\SkyScanner\Tests\Travel\CarHire;

use OzdemirBurak\SkyScanner\Travel\CarHire\LivePricing;

class LivePricingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group cars-live-pricing-methods
     */
    public function testParameters()
    {
        $pricing = new LivePricing('something');
        $pricing->setParameters(['currency' => 'TRY']);
        $this->assertEquals('GB', $pricing->getParameter('country'));
        $this->assertEquals('something', $pricing->getParameter('apiKey'));
        $this->assertNull($pricing->getParameter('dummy'));
        $this->assertEquals('TRY', $pricing->getParameter('currency'));
    }

    /**
     * @group cars-live-pricing-raw-data
     */
    public function testRawDataProperties()
    {
        $pricing = $this->getLivePricing();
        $data = $pricing->get();
        $status = $pricing->getResponseStatus();
        $this->assertContains($status, [200, 304]);
        if ($status !== 304) {
            $this->assertNotEmpty($data);
            foreach (['submitted_query', 'cars', 'websites', 'images', 'car_classes', 'debug_items'] as $property) {
                $data = $pricing->get($property);
                $this->assertNotEmpty($data);
            }
        }
    }

    /**
     * @group cars-live-pricing-cars
     */
    public function testWithApiKey()
    {
        $pricing = $this->getLivePricing();
        $cars = $pricing->getCars();
        $status = $pricing->getResponseStatus();
        $this->assertContains($status, [200, 304]);
        if ($status !== 304) {
            $this->assertNotEmpty($cars);
        }
    }

    /**
     * @return \OzdemirBurak\SkyScanner\Travel\CarHire\LivePricing
     */
    private function getLivePricing()
    {
        return new LivePricing(API_KEY);
    }
}
