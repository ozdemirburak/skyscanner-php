<?php

namespace OzdemirBurak\SkyScanner\Tests\Travel\Flights;

use OzdemirBurak\SkyScanner\Travel\Flights\LivePricing;

class LivePricingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group flights-live-pricing-methods
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
     * @group flights-live-pricing-raw-data
     */
    public function testRawDataProperties()
    {
        $pricing = $this->getLivePricing();
        $data = $pricing->get();
        $status = $pricing->getResponseStatus();
        $this->assertContains($status, [200, 304]);
        $this->assertEquals('application/json', $pricing->getResponseHeader('Content-Type'));
        $this->assertNotEmpty($data);
        foreach (['Agents', 'Carriers', 'Currencies', 'Legs', 'Itineraries', 'Places', 'Segments', 'SessionKey', 'Status'] as $property) {
            $data = $pricing->get($property);
            $this->assertNotEmpty($data);
        }
        $this->assertEquals($pricing->get('Query')->Country, $pricing->getParameter('country'));
    }

    /**
     * @group flights-live-pricing-direct-flights
     */
    public function testOneWayDirect()
    {
        $pricing = $this->getLivePricing();
        $pricing->getFlights();
        $status = $pricing->getResponseStatus();
        $this->assertContains($status, [200, 304]);
    }

    /**
     * @group flights-live-pricing-one-stop-flights
     */
    public function testOneWayWithOneStop()
    {
        $pricing = $this->getLivePricing();
        $pricing->setParameters(['stops' => 1]);
        $pricing->getFlights();
        $status = $pricing->getResponseStatus();
        $this->assertContains($status, [200, 304]);
    }

    /**
     * @group flights-live-pricing-direct-flights
     */
    public function testRoundDirect()
    {
        $pricing = $this->getLivePricing();
        $pricing->setParameters(['inbounddate' => date('Y-m-d', strtotime('+2 week'))]);
        $pricing->getFlights();
        $status = $pricing->getResponseStatus();
        $this->assertContains($status, [200, 304]);
    }

    /**
     * @group flights-live-pricing-one-stop-flights
     */
    public function testRoundWithOneStop()
    {
        $pricing = $this->getLivePricing();
        $pricing->setParameters(['stops' => 1, 'inbounddate' => date('Y-m-d', strtotime('+2 week'))]);
        $pricing->getFlights();
        $status = $pricing->getResponseStatus();
        $this->assertContains($status, [200, 304]);
    }

    /**
     * @return \OzdemirBurak\SkyScanner\Travel\Flights\LivePricing
     */
    private function getLivePricing()
    {
        return new LivePricing(API_KEY);
    }
}
