<?php

namespace OzdemirBurak\SkyScanner\Tests\Traits;

use Carbon\Carbon;
use OzdemirBurak\SkyScanner\Traits\ReferralTrait;

class ReferralTraitTest extends \PHPUnit_Framework_TestCase
{
    use ReferralTrait;

    /**
     * @group referral-links
     */
    public function testReferralLinkByParameters()
    {
        $outboundDate = Carbon::now()->addMonth(1)->format('Y-m-d');
        $url = $this->getReferralLinkByParameters('TR', 'TRY', 'tr-TR', 'SAW', 'DLM', $outboundDate, null, API_KEY);
        $this->assertEquals('http://partners.api.skyscanner.net/apiservices/referral/v1.0/TR/TRY/tr-TR/SAW/DLM/' .
            $outboundDate . '?apiKey=' . substr(API_KEY, 0, 16), $url);
    }

    /**
     * @group referral-links
     */
    public function testReferralLinkByInvalidArrayOfParameters()
    {
        $this->assertEquals('', $this->getReferralLinkByArrayOfParameters('IamNotAnArray', API_KEY));
    }

    /**
     * @group referral-links
     */
    public function testReferralLinkByArrayOfParameters()
    {
        $outboundDate = Carbon::now()->addMonth(1)->format('Y-m-d');
        $inboundDate = Carbon::now()->addMonth(2)->format('Y-m-d');
        $url = $this->getReferralLinkByArrayOfParameters(['TR', 'TRY', 'tr-TR', 'SAW', 'DLM', $outboundDate, $inboundDate], API_KEY);
        $this->assertEquals('http://partners.api.skyscanner.net/apiservices/referral/v1.0/TR/TRY/tr-TR/SAW/DLM/' .
            $outboundDate . '/' . $inboundDate . '?apiKey=' . substr(API_KEY, 0, 16), $url);
    }
}
