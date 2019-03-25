<?php

namespace Monetha;

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

    public function getPaymentUrl(array $offer, array $client)
    {

        $gatewayService = new GatewayService($this->merchantSecret, $this->monethaApiKey, $this->testMode);
        $clientId = $gatewayService->createClient($client);
        if($clientId == 'INVALID_PHONE_NUMBER') {
            return array('error' => 'INVALID_PHONE_NUMBER', 'message' => 'Invalid phone number');
        } elseif($clientId == 'AUTH_TOKEN_INVALID') {
            return array('error' => 'AUTH_TOKEN_INVALID', 'message' => 'Monetha plugin setup is invalid, please contact merchant.');
        } elseif($clientId == 'INTERNAL_ERROR') {
            return array('error' => 'INTERNAL_ERROR', 'message' => 'There\'s some internal server error, please contact merchant.');
        } elseif($clientId == 'INVALID_PHONE_COUNTRY_CODE') {
            return array('error' => 'INVALID_PHONE_COUNTRY_CODE', 'message' => 'This country code is invalid, please input correct country code.');
        } else {
            $offer['deal']['client_id'] = $clientId;
        }

        $offerResponse = $gatewayService->createOffer($offer);

        if($offerResponse == 'AMOUNT_TOO_BIG') {
            return array('error' => 'AMOUNT_TOO_BIG', 'message' => 'The value of your cart exceeds the maximum amount. Please remove some of the items from the cart.');
        } elseif($offerResponse == 'AUTH_TOKEN_INVALID') {
            return array('error' => 'AUTH_TOKEN_INVALID', 'message' => 'Monetha plugin setup is invalid, please contact merchant.');
        } elseif($offerResponse == 'AMOUNT_TOO_SMALL') {
            return array('error' => 'AMOUNT_TOO_SMALL', 'message' => 'amount_fiat in body should be greater than or equal to 0.01');
        } elseif($offerResponse == 'INTERNAL_ERROR') {
            return array('error' => 'INTERNAL_ERROR', 'message' => 'There\'s some internal server error, please contact merchant.');
        } elseif($offerResponse == 'PROCESSOR_MISSING') {
            return array('error' => 'PROCESSOR_MISSING', 'message' => 'Can\'t process order, please contact merchant.');
        } elseif($offerResponse == 'UNSUPPORTED_CURRENCY') {
            return array('error' => 'UNSUPPORTED_CURRENCY', 'message' => 'Selected currency is not supported by monetha.');
        }

        $executeOfferResponse = $gatewayService->executeOffer($offerResponse->token);
        
        if($executeOfferResponse == 'AMOUNT_TOO_BIG') {
            return array('error' => 'AMOUNT_TOO_BIG', 'message' => 'The value of your cart exceeds the maximum amount. Please remove some of the items from the cart.');
        } elseif($executeOfferResponse == 'AUTH_TOKEN_INVALID') {
            return array('error' => 'AUTH_TOKEN_INVALID', 'message' => 'Monetha plugin setup is invalid, please contact merchant.');
        } elseif($executeOfferResponse == 'AMOUNT_TOO_SMALL') {
            return array('error' => 'AMOUNT_TOO_SMALL', 'message' => 'amount_fiat in body should be greater than or equal to 0.01');
        } elseif($executeOfferResponse == 'INTERNAL_ERROR') {
            return array('error' => 'INTERNAL_ERROR', 'message' => 'There\'s some internal server error, please contact merchant.');
        } elseif($executeOfferResponse == 'PROCESSOR_MISSING') {
            return array('error' => 'PROCESSOR_MISSING', 'message' => 'Can\'t process order, please contact merchant.');
        } elseif($executeOfferResponse == 'UNSUPPORTED_CURRENCY') {
            return array('error' => 'UNSUPPORTED_CURRENCY', 'message' => 'Selected currency is not supported by monetha.');
        }

        return array('payment_url' => $executeOfferResponse->order->payment_url, 'monetha_id' => $executeOfferResponse->order->id);
        //return $executeOfferResponse->order->payment_url;
    
    }
}
