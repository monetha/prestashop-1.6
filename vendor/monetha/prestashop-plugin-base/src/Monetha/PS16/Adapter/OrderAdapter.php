<?php

namespace Monetha\PS16\Adapter;

use Monetha\Adapter\CallbackUrlInterface;
use Monetha\Adapter\InterceptorInterface;
use Monetha\Adapter\OrderAdapterInterface;

class OrderAdapter implements OrderAdapterInterface, CallbackUrlInterface {
    /**
     * @var \Cart
     */
    private $cart;

    /**
     * @var InterceptorInterface[]
     */
    private $items = [];

    /**
     * @var string
     */
    private $currencyCode;

    /**
     * @var string
     */
    private $baseUrl;

    public function __construct(\Cart $cart, $currencyCode, $baseUrl) {
        $this->cart = $cart;
        $this->currencyCode = $currencyCode;
        $this->baseUrl = $baseUrl;

        $items = $this->cart->getProducts();
        foreach ($items as $item) {
            $this->items[] = new InterceptorAdapter($item);
        }
    }

    /**
     * @return InterceptorInterface[]
     */
    public function getItems() {
        return $this->items;
    }

    public function getGrandTotalAmount() {
        return $this->cart->getOrderTotal();
    }

    public function getCurrencyCode() {
        return $this->currencyCode;
    }

    public function getBaseUrl() {
        return $this->baseUrl;
    }

    /**
     * @return mixed
     */
    public function getCartId()
    {
        return $this->cart->id;
    }

    public function getCallbackUrl()
    {
        return $this->getBaseUrl() . '/modules/monethagateway/webservices/actions.php';
    }
}
