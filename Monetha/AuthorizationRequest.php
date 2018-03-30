<?php

namespace Monetha;

class AuthorizationRequest
{
    /**
     * @var string
     */
    private $merchantKey = '';

    /**
     * @var string
     */
    private $merchantSecret = '';

    /**
     * @var bool
     */
    private $testMode = false;

    public function __construct() {
        $conf = Config::get_configuration();

        $this->testMode = $conf[Config::PARAM_TEST_MODE];
        $this->merchantKey = $conf[Config::PARAM_MERCHANT_KEY];
        $this->merchantSecret = $conf[Config::PARAM_MERCHANT_SECRET];
    }

    /**
     * @param string $uri
     * @param string $method
     * @param array|null $body
     *
     * @return mixed
     * @throws \Exception
     */
    private function callApi($uri, $method = 'GET', array $body = null) {
        $mthApi = "https://api.monetha.io/";
        if ($this->testMode) {
            $mthApi = "https://api-sandbox.monetha.io/";
        }

        $chSign = curl_init();

        $options = [
            CURLOPT_URL => $mthApi . $uri,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER =>  array(
                "Cache-Control: no-cache",
                "Content-Type: application/json",
                "MTH-Deal-Signature: " . $this->merchantKey . ":" . $this->merchantSecret
            ),
        ];

        if ($method !== 'GET' && $body) {
            $options[CURLOPT_POSTFIELDS] = json_encode($body, JSON_NUMERIC_CHECK);
            $options[CURLOPT_CUSTOMREQUEST] = $method;
        }

        curl_setopt_array($chSign, $options);

        $res = curl_exec($chSign);
        $error = curl_error($chSign);

        if ($error) {
            //TODO: log
            throw new \Exception($error);
        }

        $resStatus = curl_getinfo($chSign, CURLINFO_HTTP_CODE);
        if ($resStatus < 200 || $resStatus >= 300) {
            //TODO: log
            throw new \Exception($res);
        }

        $resJson = json_decode($res);

        curl_close($chSign);

        return $resJson;
    }

    /**
     * @param OrderAdapterInterface $order
     * @param string $orderId
     *
     * @return array
     */
    public function createDeal(OrderAdapterInterface $order, $orderId) {
        $items = [];
        $cartItems = $order->getItems();

        $itemsPrice = 0;
        foreach($cartItems as $item) {
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
            'callback_url' => 'https://www.monetha.io/callback',
            'cancel_url' => 'https://www.monetha.io/cancel',
            'external_order_id' => $orderId . " ",
        );

        return $deal;
    }

    /**
     * @param array $deal
     *
     * @return string
     * @throws \Exception
     */
    public function getPaymentUrl(array $deal) {
        $resJson = $this->callApi("v1/merchants/offer", 'POST', $deal);
        $paymentUrl = '';
        if ($resJson && $resJson->token) {
            $resJson = $this->callApi('v1/deals/execute?token=' . $resJson->token);
            $paymentUrl = $resJson->order->payment_url;
        }

        return $paymentUrl;
    }
}
