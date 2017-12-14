<?php

namespace OzdemirBurak\SkyScanner\Traits;

trait ReferralTrait
{
    /**
     * @var string
     */
    protected $referralUrl = 'http://partners.api.skyscanner.net/apiservices/referral/v1.0/';

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
    public function getReferralLinkByParameters($country, $currency, $locale, $originPlace, $destinationPlace, $outboundPartialDate, $inboundPartialDate = null, $apiKey = null)
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
    public function getReferralLinkByArrayOfParameters($data, $apiKey = null)
    {
        if (is_array($data)) {
            return $this->referralUrl . implode('/', array_filter($data)) . $this->getApiKeyQuery($apiKey);
        }
        return '';
    }

    /**
     * @param $apiKey
     *
     * @return string
     */
    protected function getApiKeyQuery($apiKey)
    {
        return !empty($apiKey) ? '?apiKey=' . substr($apiKey, 0, 16) : '';
    }
}
