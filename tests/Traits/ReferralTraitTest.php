<?php

namespace OzdemirBurak\SkyScanner\Tests\Traits;

use OzdemirBurak\SkyScanner\Traits\ReferralTrait;
use PHPUnit\Framework\TestCase;

class ReferralTraitTest extends TestCase
{
    use ReferralTrait;

    /**
     * @group referral-links
     */
    public function testReferralLinkByParameters()
    {
        $outboundDate = date('Y-m-d', strtotime('+1 month'));
        $url = $this->getReferralLinkByParameters('TR', 'TRY', 'tr-TR', 'SAW', 'DLM', $outboundDate, null, API_KEY_1);
        $this->assertEquals('https://partners.api.skyscanner.net/apiservices/referral/v1.0/TR/TRY/tr-TR/SAW/DLM/' .
            $outboundDate . '?apiKey=' . substr(API_KEY_1, 0, 16), $url);
    }

    /**
     * @group referral-links
     */
    public function testReferralLinkByInvalidArrayOfParameters()
    {
        $this->assertEquals('', $this->getReferralLinkByArrayOfParameters('IamNotAnArray', API_KEY_1));
    }

    /**
     * @group referral-links
     */
    public function testReferralLinkByArrayOfParameters()
    {
        $outboundDate = date('Y-m-d', strtotime('+1 month'));
        $inboundDate = date('Y-m-d', strtotime('+2 month'));
        $url = $this->getReferralLinkByArrayOfParameters(['TR', 'TRY', 'tr-TR', 'SAW', 'DLM', $outboundDate, $inboundDate], API_KEY_1);
        $this->assertEquals('https://partners.api.skyscanner.net/apiservices/referral/v1.0/TR/TRY/tr-TR/SAW/DLM/' .
            $outboundDate . '/' . $inboundDate . '?apiKey=' . substr(API_KEY_1, 0, 16), $url);
    }
}
