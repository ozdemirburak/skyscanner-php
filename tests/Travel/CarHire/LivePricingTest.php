<?php

namespace OzdemirBurak\SkyScanner\Tests\Travel\CarHire;

use OzdemirBurak\SkyScanner\Travel\CarHire\LivePricing;
use PHPUnit\Framework\TestCase;

class LivePricingTest extends TestCase
{
    /**
     * @group cars-live-pricing-methods
     */
    public function testParameters()
    {
        $pricing = new LivePricing('something');
        $pricing->setParameters(['dropoffplace' => 'IST']);
        $this->assertEquals('IST', $pricing->getParameter('dropoffplace'));
        $this->assertEquals('something', $pricing->getParameter('apiKey'));
        $this->assertNull($pricing->getParameter('dummy'));
    }

    /**
     * @group cars-live-pricing-url
     */
    public function testUrl()
    {
        $uri = $this->getUri($pricing = $this->getLivePricing());
        $this->assertEquals($uri, $pricing->getUri());
    }

    /**
     * Get Properties: 'submitted_query', 'cars', 'websites', 'images', 'car_classes', 'debug_items'
     * There may be cases like there are not cars, so just check websites and debug items
     *
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
            foreach (['websites', 'debug_items'] as $property) {
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
     * @param \OzdemirBurak\SkyScanner\Travel\CarHire\LivePricing $pricing
     * @param array                                               $output
     *
     * @return string
     */
    private function getUri(LivePricing $pricing, array $output = [])
    {
        foreach ($this->getParameterNames() as $p) {
            $output[] = $pricing->getParameter($p);
        }
        return implode('/', $output) . '?apiKey=' . $pricing->getParameter('apiKey') .
            '&userip=' . $pricing->getIpAddress();
    }

    /**
     * @return array
     */
    private function getParameterNames()
    {
        return [
            'country', 'currency', 'locale', 'pickupplace', 'dropoffplace',
            'pickupdatetime', 'dropoffdatetime', 'driverage'
        ];
    }

    /**
     * @return \OzdemirBurak\SkyScanner\Travel\CarHire\LivePricing
     */
    private function getLivePricing()
    {
        $pricing = new LivePricing(API_KEY_1);
        $pricing->setParameters([
            'dropoffplace' => 'ADB',
            'dropoffdatetime' => date('Y-m-d\TH:i', strtotime('+2 week')),
            'pickupplace' => 'IST',
            'pickupdatetime' => date('Y-m-d\TH:i', strtotime('+1 week')),
            'driverage' => 21
        ]);
        return $pricing;
    }
}
