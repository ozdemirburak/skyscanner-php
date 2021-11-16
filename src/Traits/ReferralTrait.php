<?php

namespace OzdemirBurak\SkyScanner\Traits;

trait ReferralTrait
{
    /**
     * @var string
     */
    protected $referralUrl = 'https://partners.api.skyscanner.net/apiservices/referral/v1.0/';

    /**
     * @param $country
     * @param $currency
     * @param $locale
     * @param $originPlace
     * @param $destinationPlace
     * @param $outboundPartialDate
     * @param $inboundPartialDate
     * @param $apiKey
     *
     * @return string
     */
    public function getReferralLinkByParameters($country, $currency, $locale, $originPlace, $destinationPlace, $outboundPartialDate, $inboundPartialDate = null, $apiKey = null): string
    {
        return $this->referralUrl . implode('/', array_filter([
            $country, $currency, $locale, $originPlace, $destinationPlace, $outboundPartialDate, $inboundPartialDate
        ])) . $this->getApiKeyQuery($apiKey);
    }

    /**
     * @param $data
     * @param $apiKey
     *
     * @return string
     */
    public function getReferralLinkByArrayOfParameters($data, $apiKey = null): string
    {
        if (\is_array($data)) {
            return $this->referralUrl . implode('/', array_filter($data)) . $this->getApiKeyQuery($apiKey);
        }
        return '';
    }

    /**
     * @param $apiKey
     *
     * @return string
     */
    protected function getApiKeyQuery($apiKey): string
    {
        return !empty($apiKey) ? '?apiKey=' . substr($apiKey, 0, 16) : '';
    }
}
