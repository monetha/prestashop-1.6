<?php

namespace Monetha\Services;

require_once(__DIR__ . '/HttpService.php');
require_once(__DIR__ . './../Consts/ApiType.php');
require_once(__DIR__ . './../Consts/EventType.php');
require_once(__DIR__ . './../Consts/Resource.php');
require_once(__DIR__ . './../Helpers/JWT.php');

use Monetha\Consts\ApiType;
use Monetha\Consts\Resource;
use Monetha\Consts\EventType;
use Monetha\Helpers\JWT;
use Monetha\Services\HttpService;

class GatewayService
{
    public $merchantSecret;
    public $mthApiKey;
    public $testMode;

    public function __construct($merchantSecret, $mthApiKey, $testMode)
    {
        $this->merchantSecret = $merchantSecret;
        $this->mthApiKey = $mthApiKey;
        $this->testMode = $testMode;
    }

    public function prepareOfferBody($order, $orderId)
    {
        $items = [];
        $cartItems = $order->getItems();

        $itemsPrice = 0;
        foreach ($cartItems as $item) {
            /**
             * @var $item Interceptor
             */
            $price = round($item->getPrice(), 2);
            $quantity = $item->getQtyOrdered();
            $li = [
                'name' => $item->getName(),
                'quantity' => $quantity,
                'amount_fiat' => $price,
            ];
            $itemsPrice += $price * $quantity;
            $items[] = $li;
        }

        $itemsPrice = round($itemsPrice, 2);

        $grandTotal = round($order->getGrandTotalAmount(), 2);

        // Add shipping and taxes
        $shipping = [
            'name' => 'Shipping and taxes',
            'quantity' => 1,
            'amount_fiat' => round($grandTotal - $itemsPrice, 2),
        ];
        $items[] = $shipping;

        $deal = array(
            'deal' => array(
                'amount_fiat' => round($grandTotal, 2),
                'currency_fiat' => $order->getCurrencyCode(),
                'line_items' => $items
            ),
            'return_url' => $order->getBaseUrl(),
            'callback_url' => $order->getBaseUrl() . '/modules/monethagateway/webservices/actions.php',
            'external_order_id' => $orderId . " ",
        );

        return $deal;
    }

    public function validateApiKey()
    {
        $apiUrl = $this->getApiUrl();
        $merchantId = $this->getMerchantId();

        if ($merchantId == null) {
            return false;
        }

        $apiUrl = $apiUrl . 'v1/merchants/' . $merchantId .'/secret';

        $response = HttpService::callApi($apiUrl, 'GET', null, ["Authorization: Bearer " . $this->mthApiKey]);
        return ($response && $response->integration_secret && $response->integration_secret == $this->merchantSecret);
    }

    public function configurationIsValid()
    {
        return (
            !empty($this->merchantSecret) &&
            !empty($this->mthApiKey) &&
            !empty($this->testMode)
        );
    }

    public function validateSignature($signature, $data)
    {
        return $signature == base64_encode(hash_hmac('sha256', $data, $this->merchantSecret, true));
    }

    public function getMerchantId()
    {
        $tks = explode('.', $this->mthApiKey);
        if (count($tks) != 3) {
            return null;
        }
        list($headb64, $bodyb64, $cryptob64) = $tks;
        $payload = JWT::jsonDecode(JWT::urlsafeB64Decode($bodyb64));

        if (isset($payload->mid)) {
            return $payload->mid;
        }

        return null;
    }

    public function getApiUrl()
    {
        $apiUrl = ApiType::PROD;

        if ((bool)$this->testMode) {
            $apiUrl = ApiType::TEST;
        }

        return $apiUrl;
    }

    public function cancelExternalOrder($orderId)
    {
        $apiUrl = $this->getApiUrl();
        $apiUrl = $apiUrl . 'v1/orders/' . $orderId .'/cancel';
        $body = ['cancel_reason'=> 'Order cancelled from shop'];
        return HttpService::callApi($apiUrl, 'POST', $body, ["Authorization: Bearer " . $this->mthApiKey]);
    }

    public function createOffer($offerBody)
    {
        $apiUrl = $this->getApiUrl();
        $apiUrl = $apiUrl . 'v1/merchants/offer_auth';

        return HttpService::callApi($apiUrl, 'POST', $offerBody, ["Authorization: Bearer " . $this->mthApiKey]);
    }

    public function executeOffer($token)
    {
        $apiUrl = $this->getApiUrl();
        $apiUrl = $apiUrl . 'v1/deals/execute';

        return HttpService::callApi($apiUrl, 'POST', ["token" => $token], []);
    }

    public function processAction($order, $data)
    {
        switch ($data->resource) {
            case Resource::ORDER:
                switch ($data->event) {
                    case EventType::CANCELLED:
                        $this->cancelOrder($order, $data->payload->note);
                        break;
                    case EventType::FINALIZED:
                        $this::finalizeOrder($order);
                        break;
                    default:
                        throw new \Exception('Bad action type');
                        break;
                }
                break;

            default:
            throw new \Exception('Bad resource');
            break;
        }
    }

    public function cancelOrder($order, $note)
    {
        $history = new \OrderHistory();
        $history->id_order = (int)$order->id;
        $history->changeIdOrderState(6, (int)($order->id), true);
        $history->save();
    }

    public function finalizeOrder($order)
    {
        $history = new \OrderHistory();
        $history->id_order = (int)$order->id;
        $history->changeIdOrderState(2, (int)($order->id), true);
        $history->save();
    }
}
