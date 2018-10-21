<?php

namespace OzdemirBurak\SkyScanner\Tests\Travel\Hotels;

use OzdemirBurak\SkyScanner\Travel\Hotels\LivePricing;
use PHPUnit\Framework\TestCase;

class LivePricingTest extends TestCase
{
    /**
     * @group hotels-live-pricing-methods
     */
    public function testParameters()
    {
        $pricing = new LivePricing('something');
        $pricing->setParameters(['entity_id' => 27544008]);
        $this->assertEquals(27544008, $pricing->getParameter('entity_id'));
        $this->assertEquals('something', $pricing->getParameter('apiKey'));
        $this->assertNull($pricing->getParameter('dummy'));
    }

    /**
     * @group hotels-live-pricing-url
     */
    public function testUrl()
    {
        $uri = $this->getUri($pricing = $this->getLivePricing());
        $this->assertContains($uri, $pricing->getUri());
    }

    /**
     * @param \OzdemirBurak\SkyScanner\Travel\Hotels\LivePricing $pricing
     * @param array                                               $output
     *
     * @return string
     */
    private function getUri(LivePricing $pricing, array $output = [])
    {
        foreach ($this->getParameterNames() as $key => $p) {
            $output[] = ($key === 0 ? '?' : '&') . $p . '=' . $pricing->getParameter($p === 'apikey' ? 'apiKey' : $p);
        }
        return str_replace('country', 'market', implode('', $output));
    }

    /**
     * @return array
     */
    private function getParameterNames()
    {
        return ['apikey', 'country', 'currency', 'locale', 'checkin_date', 'checkout_date'];
    }

    /**
     * @return \OzdemirBurak\SkyScanner\Travel\Hotels\LivePricing
     */
    private function getLivePricing()
    {
        $pricing = new LivePricing(API_KEY_1);
        $pricing->setParameters([
            'entity_id'     => 27544008,
            'checkin_date'  => date('Y-m-d', strtotime('+1 week')),
            'checkout_date' => date('Y-m-d', strtotime('+2 week')),
        ]);
        return $pricing;
    }
}
