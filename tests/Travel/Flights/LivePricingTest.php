<?php

namespace OzdemirBurak\SkyScanner\Tests\Travel\Flights;

use OzdemirBurak\SkyScanner\Travel\Flights\LivePricing;

class LivePricingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group live-pricing-methods
     */
    public function testDefaultParameters()
    {
        $pricing = new LivePricing();
        $this->assertEquals('GB', $pricing->getParameter('country'));
    }

    /**
     * @group live-pricing-methods
     */
    public function testInvalidParameter()
    {
        $pricing = new LivePricing();
        $this->assertNull($pricing->getParameter('dummy'));
    }

    /**
     * @group live-pricing-methods
     */
    public function testParameterAssignmentByConstructor()
    {
        $pricing = new LivePricing('something');
        $this->assertEquals('something', $pricing->getParameter('apiKey'));
    }

    /**
     * @group live-pricing-methods
     */
    public function testParameterAssignmentByFunction()
    {
        $pricing = new LivePricing();
        $pricing->setParameters(['apiKey' => 'something']);
        $this->assertEquals('something', $pricing->getParameter('apiKey'));
    }

    /**
     * @group live-pricing-methods
     */
    public function testRequestWithoutApiKey()
    {
        $pricing = new LivePricing();
        $pricing->makeRequest('POST');
        $this->assertEquals(403, $pricing->getResponseStatus());
    }

    /**
     * @group live-pricing-methods
     */
    public function testWithApiKey()
    {
        $pricing = new LivePricing(API_KEY);
        $pricing->makeRequest('POST');
        $this->assertEquals(201, $pricing->getResponseStatus());
    }

    /**
     * @group live-pricing-methods
     */
    public function testUrl()
    {
        $pricing = new LivePricing(API_KEY);
        $this->assertNotEmpty($pricing->getUrl());
    }

    /**
     * @group live-pricing-methods
     */
    public function testResponseContentType()
    {
        $pricing = new LivePricing(API_KEY);
        $pricing->makeRequest('POST');
        $this->assertEquals('application/json', $pricing->getResponseHeader('Content-Type'));
    }

    /**
     * @group live-pricing-flights
     */
    public function testOneWayWithNonStop()
    {
        $pricing = new LivePricing(API_KEY);
        $this->assertNotEmpty($pricing->parseFlights());
    }

    /**
     * @group live-pricing-flights
     */
    public function testOneWayWithMultipleStops()
    {
        $pricing = new LivePricing(API_KEY);
        $pricing->setParameters(['stops' => 2]);
        $this->assertNotEmpty($pricing->parseFlights());
    }
    
    /**
     * @group live-pricing-flights
     */
    public function testRoundWithNonStop()
    {
        $pricing = new LivePricing(API_KEY);
        $pricing->setParameters(['inbounddate' => date('Y-m-d', strtotime('+2 week'))]);
        $this->assertNotEmpty($pricing->parseFlights());
    }
    
    /**
     * @group live-pricing-flights
     */
    public function testRoundWithMultipleStops()
    {
        $pricing = new LivePricing(API_KEY);
        $pricing->setParameters(['stops' => 2, 'inbounddate' => date('Y-m-d', strtotime('+2 week'))]);
        $this->assertNotEmpty($pricing->parseFlights());
    }

    /**
     * @group live-pricing-flights
     */
    public function testOneWayWithMultipleStopsWithoutCheapestFlights()
    {
        $pricing = new LivePricing(API_KEY);
        $pricing->setParameters(['stops' => 2]);
        $this->assertNotEmpty($pricing->parseFlights(false));
    }

    /**
     * @group live-pricing-flights
     */
    public function testRoundWithNonStopWithoutCheapestFlights()
    {
        $pricing = new LivePricing(API_KEY);
        $pricing->setParameters(['inbounddate' => date('Y-m-d', strtotime('+2 week'))]);
        $this->assertNotEmpty($pricing->parseFlights(false));
    }
}
