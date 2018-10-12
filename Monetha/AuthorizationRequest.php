<?php

namespace Monetha;

require_once(__DIR__ . './../Services/GatewayService.php');

use Monetha\Services\GatewayService;

class AuthorizationRequest
{
    private $merchantSecret = '';
    private $monethaApiKey = '';
    private $testMode = false;

    public function __construct()
    {
        $conf = Config::get_configuration();
        $this->testMode = $conf[Config::PARAM_TEST_MODE];
        $this->merchantSecret = $conf[Config::PARAM_MERCHANT_SECRET];
        $this->monethaApiKey = $conf[Config::PARAM_MONETHA_API_KEY];
    }

    public function getPaymentUrl(array $offer)
    {
        $gatewayService = new GatewayService($this->merchantSecret, $this->monethaApiKey, $this->testMode);
        $offerResponse = $gatewayService->createOffer($offer);
        $executeOfferResponse = $gatewayService->executeOffer($offerResponse->token);
        return $executeOfferResponse->order->payment_url;
    }
}
