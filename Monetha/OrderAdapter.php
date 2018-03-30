<?php

namespace Monetha;

class OrderAdapter implements OrderAdapterInterface {
    /**
     * @var \Cart
     */
    private $cart;

    /**
     * @var array
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
}
