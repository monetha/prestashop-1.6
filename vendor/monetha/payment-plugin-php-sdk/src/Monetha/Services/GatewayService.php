<?php

namespace Monetha\Services;

use Monetha\Adapter\ClientAdapterInterface;
use Monetha\Adapter\ConfigAdapterInterface;
use Monetha\Adapter\OrderAdapterInterface;
use Monetha\Constants\ApiType;
use Monetha\Helpers\JWT;
use Monetha\Payload\CancelOrder as CancelOrderPayload;
use Monetha\Payload\CreateClient as CreateClientPayload;
use Monetha\Payload\CreateOffer as CreateOfferPayload;
use Monetha\Payload\ExecuteOffer as ExecuteOfferPayload;
use Monetha\Payload\ValidateApiKey as ValidateApiKeyPayload;
use Monetha\Request\CancelOrder;
use Monetha\Request\CreateClient;
use Monetha\Request\CreateOffer;
use Monetha\Request\ExecuteOffer;
use Monetha\Request\ValidateApiKey;
use Monetha\Response\CreateOffer as CreateOfferResponse;
use Monetha\Response\Exception\ApiException;
use Monetha\Response\Exception\ClientIdNotFoundException;
use Monetha\Response\Exception\IntegrationSecretNotFoundException;
use Monetha\Response\Exception\OrderIdNotFoundException;
use Monetha\Response\Exception\OrderNotFoundException;
use Monetha\Response\Exception\PaymentUrlNotFoundException;
use Monetha\Response\Exception\TokenNotFoundException;

class GatewayService
{
    /**
     * @var string
     */
    private $merchantSecret;

    /**
     * @var string
     */
    private $mthApiKey;

    /**
     * @var string
     */
    private $testMode;

    /**
     * GatewayService constructor.
     * @param ConfigAdapterInterface $configAdapter
     */
    public function __construct(ConfigAdapterInterface $configAdapter)
    {
        $this->merchantSecret = $configAdapter->getMerchantSecret();
        $this->mthApiKey = $configAdapter->getMthApiKey();
        $this->testMode = $configAdapter->getIsTestMode();
    }

    /**
     * @param OrderAdapterInterface $orderAdapter
     * @param ClientAdapterInterface $clientAdapter
     * @return \Monetha\Response\ExecuteOffer
     * @throws ApiException
     */
    public function getExecuteOfferResponse(OrderAdapterInterface $orderAdapter, ClientAdapterInterface $clientAdapter)
    {
        $createOfferResponse = $this->createOffer($orderAdapter, $clientAdapter);
        $token = $createOfferResponse->getToken();

        $executeOfferResponse = $this->executeOffer($token);

        /** @var \Monetha\Response\ExecuteOffer $executeOfferResponse */
        return $executeOfferResponse;
    }

    /**
     * @return bool
     * @throws ApiException
     */
    public function validateApiKey()
    {
        $apiUrl = $this->getApiUrl();
        $merchantId = $this->getMerchantId();

        if ($merchantId == null) {
            return false;
        }

        $uri = 'v1/merchants/' . $merchantId .'/secret';

        $payload = new ValidateApiKeyPayload();
        $request = new ValidateApiKey($payload, $this->mthApiKey, $apiUrl, $uri);

        $validateResponse = $request->send();

        /** @var \Monetha\Response\ValidateApiKey $validateResponse */
        $integrationSecret = $validateResponse->getIntegrationSecret();

        return $integrationSecret == $this->merchantSecret;
    }

    /**
     * @param string $signature
     * @param string $data
     * @return bool
     */
    public function validateSignature($signature, $data)
    {
        return $signature == base64_encode(hash_hmac('sha256', $data, $this->merchantSecret, true));
    }

    private function getMerchantId()
    {
        $tks = explode('.', $this->mthApiKey);
        if (count($tks) != 3) {
            return null;
        }
        list($headb64, $bodyb64, $cryptob64) = $tks;
        if($this->isJson(JWT::urlsafeB64Decode($bodyb64)))
        {
            $payload = JWT::jsonDecode(JWT::urlsafeB64Decode($bodyb64));
        }
        else
        {
            return null;
        }

        if (isset($payload->mid)) {
            return $payload->mid;
        }

        return null;
    }

    /**
     * @param string $str
     * @return bool
     */
    private function isJson($str) {
        $json = json_decode($str);
        return $json && $str != $json;
    }

    /**
     * @return string
     */
    private function getApiUrl()
    {
        $apiUrl = ApiType::PROD;

        if ((bool)$this->testMode) {
            $apiUrl = ApiType::TEST;
        }

        return $apiUrl;
    }

    /**
     * @param $orderId
     * @return \Monetha\Response\CancelOrder
     * @throws ApiException
     */
    public function cancelExternalOrder($orderId)
    {
        $apiUrl = $this->getApiUrl();
        $uri = 'v1/orders/' . $orderId .'/cancel';

        $payload = new CancelOrderPayload();
        $request = new CancelOrder($payload, $this->mthApiKey, $apiUrl, $uri);

        /** @var \Monetha\Response\CancelOrder $response */
        $response = $request->send();

        return $response;
    }

    /**
     * @param ClientAdapterInterface $clientAdapter
     * @return \Monetha\Response\CreateClient
     * @throws ApiException
     */
    private function createClient(ClientAdapterInterface $clientAdapter)
    {
        $apiUrl = $this->getApiUrl();

        $payload = new CreateClientPayload($clientAdapter);
        $request = new CreateClient($payload, $this->mthApiKey, $apiUrl);

        /** @var \Monetha\Response\CreateClient $response */
        $response = $request->send();

        return $response;
    }

    /**
     * @param OrderAdapterInterface $orderAdapter
     * @param ClientAdapterInterface $clientAdapter
     * @return CreateOfferResponse
     * @throws ApiException
     */
    public function createOffer(OrderAdapterInterface $orderAdapter, ClientAdapterInterface $clientAdapter)
    {
        $clientResponse =  $this->createClient($clientAdapter);

        /** @var \Monetha\Response\CreateClient $clientId */
        $clientId = $clientResponse->getClientId();

        $apiUrl = $this->getApiUrl();

        $payload = new CreateOfferPayload($orderAdapter, $clientId);
        $request = new CreateOffer($payload, $this->mthApiKey, $apiUrl);

        /** @var \Monetha\Response\CreateOffer $response */
        $response = $request->send();

        return $response;
    }

    /**
     * @param $token
     * @return \Monetha\Response\ExecuteOffer
     * @throws ApiException
     */
    public function executeOffer($token)
    {
        /** @var \Monetha\Response\CreateOffer $createOfferResponse */
        $payload = new ExecuteOfferPayload($token);

        $apiUrl = $this->getApiUrl();
        $request = new ExecuteOffer($payload, $this->mthApiKey, $apiUrl);

        /** @var \Monetha\Response\ExecuteOffer $response */
        $response = $request->send();

        return $response;
    }
}
