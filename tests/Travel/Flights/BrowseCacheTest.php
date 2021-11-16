<?php

namespace OzdemirBurak\SkyScanner\Tests\Travel\Flights;

use OzdemirBurak\SkyScanner\Exceptions\InvalidMethodException;
use OzdemirBurak\SkyScanner\Travel\Flights\BrowseCache;
use PHPUnit\Framework\TestCase;

class BrowseCacheTest extends TestCase
{
    /**
     * @group browse-cache-methods
     */
    public function testUrl()
    {
        $cache = $this->getBrowseCache();
        [$url, $parameters] = $cache->getRequestUrlAndParameters($cache->getUrl(), true);
        $this->assertNotEmpty($parameters);
        $this->assertEquals('https://partners.api.skyscanner.net/apiservices/browsequotes/v1.0/GB/GBP/en-GB/LHR/IST/' . date('Y-m-d', strtotime('+1 week')) . '/?apiKey=' . $cache->getParameter('apiKey'), $url);
    }

    /**
     * @group browse-cache-methods
     */
    public function testInvalidMethod()
    {
        $cache = $this->getBrowseCache();
        $cache->setParameters(['method' => 'https://youtu.be/dQw4w9WgXcQ']);
        try {
            $cache->getPrices();
        } catch (InvalidMethodException $e) {
            $this->assertEquals($e->getMessage(), 'Invalid Browse Cache method');
            return;
        }
        $this->fail('Exception has not been raised.');
    }

    /**
     * @group browse-cache-raw-data
     */
    public function testRawDataProperties()
    {
        $cache = $this->getBrowseCache();
        $data = $cache->getPrices();
        $this->assertEquals(200, $cache->getResponseStatus());
        $this->assertEquals('application/json', $cache->getResponseHeader('Content-Type'));
        $this->assertNotEmpty($data);
        foreach (['Quotes', 'Carriers', 'Currencies', 'Places'] as $property) {
            $data = $cache->get($property);
            $this->assertNotEmpty($data);
        }
    }

    /**
     * @group browse-cache-flights-quotes
     */
    public function testQuotesOneWay()
    {
        $cache = $this->getBrowseCache();
        $cache->setParameters(['outboundPartialDate' => date('Y-m', strtotime('+1 month'))]);
        $this->assertNotEmpty($cache->getPrices()['Quotes']);
    }

    /**
     * @group browse-cache-flights-quotes
     */
    public function testQuotesRound()
    {
        $cache = $this->getBrowseCache();
        $cache->setParameters([
            'outboundPartialDate'  => date('Y-m', strtotime('+1 month')),
            'inboundPartialDate'   => date('Y-m', strtotime('+1 month'))
        ]);
        $this->assertNotEmpty($cache->getPrices()['Quotes']);
    }

    /**
     * @group browse-cache-flights-routes
     */
    public function testRoutesOneWay()
    {
        $cache = $this->getBrowseCache();
        $cache->setParameters([
            'originPlace' => 'IST',
            'destinationPlace' => 'anywhere',
            'outboundPartialDate' => 'anytime',
            'method' => 'browseroutes'
        ]);
        $this->assertNotEmpty($cache->getPrices()['Routes']);
    }

    /**
     * @group browse-cache-flights-routes
     */
    public function testRoutesRound()
    {
        $cache = $this->getBrowseCache();
        $cache->setParameters([
            'originPlace' => 'IST',
            'destinationPlace' => 'anywhere',
            'outboundPartialDate' => 'anytime',
            'inboundPartialDate' => 'anytime',
            'method' => 'browseroutes'
        ]);
        $this->assertNotEmpty($cache->getPrices()['Routes']);
    }

    /**
     * @group browse-cache-flights-dates
     */
    public function testDatesOneWay()
    {
        $cache = $this->getBrowseCache();
        $cache->setParameters([
            'outboundPartialDate' => date('Y-m', strtotime('+1 month')),
            'method'              => 'browsedates'
        ]);
        $this->assertNotEmpty($cache->getPrices()['Dates']);
    }

    /**
     * @group browse-cache-flights-dates
     */
    public function testDatesRound()
    {
        $cache = $this->getBrowseCache();
        $cache->setParameters([
            'outboundPartialDate'  => date('Y-m', strtotime('+1 month')),
            'inboundPartialDate'   => date('Y-m', strtotime('+2 month')),
            'method'               => 'browsedates'
        ]);
        $this->assertNotEmpty($cache->getPrices()['Dates']);
    }

    /**
     * @group browse-cache-flights-grid
     */
    public function testGridOneWay()
    {
        $cache = $this->getBrowseCache();
        $cache->setParameters([
            'outboundPartialDate' => date('Y-m', strtotime('+1 month')),
            'method'              => 'browsegrid'
        ]);
        $this->assertNotEmpty($cache->getPrices()['Grid']);
    }

    /**
     * @group browse-cache-flights-grid
     */
    public function testGridRound()
    {
        $cache = $this->getBrowseCache();
        $cache->setParameters([
            'outboundPartialDate'  => date('Y-m', strtotime('+1 month')),
            'inboundPartialDate'   => date('Y-m', strtotime('+2 month')),
            'method'               => 'browsegrid'
        ]);
        $this->assertNotEmpty($cache->getPrices()['Grid']);
    }

    /**
     * @param array $parameters
     *
     * @return \OzdemirBurak\SkyScanner\Travel\Flights\BrowseCache
     */
    private function getBrowseCache(array $parameters = [])
    {
        $cache = new BrowseCache(API_KEY_1);
        $cache->setParameters(!empty($parameters) ? $parameters : [
            'destinationPlace' => 'IST',
            'originPlace' => 'LHR',
            'outboundPartialDate' => date('Y-m-d', strtotime('+1 week'))
        ]);
        return $cache;
    }
}
