<?php

namespace OzdemirBurak\SkyScanner\Tests\Travel\Flights;

use Carbon\Carbon;
use Exception;
use OzdemirBurak\SkyScanner\Travel\Flights\BrowseCache;

class BrowseCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group browse-cache-methods
     */
    public function testUrl()
    {
        $date = Carbon::now()->addWeek(1)->format('Y-m-d');
        $parameters = ['originPlace' => 'LHR', 'destinationPlace' => 'JFK', 'outboundPartialDate' => $date];
        $url = 'http://partners.api.skyscanner.net/apiservices/browsequotes/v1.0/GB/GBP/en-GB/LHR/JFK/' . $date . '/';
        $cache = new BrowseCache(API_KEY);
        $cache->setParameters($parameters);
        $this->assertEquals($cache->getUrl(), $url);
    }

    /**
     * @group browse-cache-methods
     */
    public function testRequestWithoutApiKey()
    {
        $cache = new BrowseCache();
        $cache->makeRequest('GET', $cache->getUrl());
        $this->assertEquals(403, $cache->getResponseStatus());
    }

    /**
     * @group browse-cache-methods
     */
    public function testWithApiKey()
    {
        $cache = new BrowseCache(API_KEY);
        $cache->makeRequest('GET', $cache->getUrl());
        $this->assertEquals(200, $cache->getResponseStatus());
    }

    /**
     * @group browse-cache-methods
     */
    public function testInvalidMethod()
    {
        $cache = new BrowseCache(API_KEY);
        try {
            $cache->getData('Françoise Hardy');
        } catch (Exception $e) {
            return $this->assertEquals($e->getMessage(), 'Invalid Browse Cache method');
        }
        $this->fail('Exception has not been raised.');
    }

    /**
     * @group browse-cache-methods
     */
    public function testResponseContentType()
    {
        $cache = new BrowseCache(API_KEY);
        $cache->getData();
        $this->assertEquals('application/json', $cache->getResponseHeader('Content-Type'));
    }

    /**
     * @group browse-cache-flights-quotes
     */
    public function testQuotesOneWay()
    {
        $cache = new BrowseCache(API_KEY);
        $cache->setParameters(['outboundPartialDate' => Carbon::now()->addMonth(1)->format('Y-m')]);
        $this->assertNotEmpty($cache->getData('browsequotes')['quotes']);
    }

    /**
     * @group browse-cache-flights-quotes
     */
    public function testQuotesRound()
    {
        $cache = new BrowseCache(API_KEY);
        $cache->setParameters([
            'outboundPartialDate'  => Carbon::now()->addMonth(1)->format('Y-m-d'),
            'inboundPartialDate'   => Carbon::now()->addMonth(1)->format('Y-m-d')
        ]);
        $this->assertNotEmpty($cache->getData('browsequotes')['quotes']);
    }

    /**
     * @group browse-cache-flights-routes
     */
    public function testRoutesOneWay()
    {
        $cache = new BrowseCache(API_KEY);
        $cache->setParameters([
            'originPlace' => 'IST',
            'destinationPlace' => 'anywhere',
            'outboundPartialDate' => 'anytime'
        ]);
        $this->assertNotEmpty($cache->getData('browseroutes')['routes']);
    }

    /**
     * @group browse-cache-flights-routes
     */
    public function testRoutesRound()
    {
        $cache = new BrowseCache(API_KEY);
        $cache->setParameters([
            'originPlace' => 'IST',
            'destinationPlace' => 'anywhere',
            'outboundPartialDate' => 'anytime',
            'inboundPartialDate' => 'anytime'
        ]);
        $this->assertNotEmpty($cache->getData('browseroutes')['routes']);
    }

    /**
     * @group browse-cache-flights-dates
     */
    public function testDatesOneWay()
    {
        $cache = new BrowseCache(API_KEY);
        $cache->setParameters([
            'outboundPartialDate' => Carbon::now()->addMonth(1)->format('Y-m')
        ]);
        $this->assertNotEmpty($cache->getData('browsedates')['dates']);
    }

    /**
     * @group browse-cache-flights-dates
     */
    public function testDatesRound()
    {
        $cache = new BrowseCache(API_KEY);
        $cache->setParameters([
            'outboundPartialDate'  => Carbon::now()->addMonth(1)->format('Y-m'),
            'inboundPartialDate'   => Carbon::now()->addMonth(2)->format('Y-m')
        ]);
        $this->assertNotEmpty($cache->getData('browsedates')['dates']);
    }

    /**
     * @group browse-cache-flights-grid
     */
    public function testGridOneWay()
    {
        $cache = new BrowseCache(API_KEY);
        $cache->setParameters([
            'outboundPartialDate' => Carbon::now()->addMonth(1)->format('Y-m')
        ]);
        $this->assertNotEmpty($cache->getData('browsegrid')['grid']);
    }

    /**
     * @group browse-cache-flights-grid
     */
    public function testGridRound()
    {
        $cache = new BrowseCache(API_KEY);
        $cache->setParameters([
            'outboundPartialDate'  => Carbon::now()->addMonth(1)->format('Y-m'),
            'inboundPartialDate'   => Carbon::now()->addMonth(2)->format('Y-m')
        ]);
        $this->assertNotEmpty($cache->getData('browsegrid')['grid']);
    }
}
