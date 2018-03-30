<?php

namespace Monetha;

class InterceptorAdapter implements Interceptor {
    /**
     * @var array
     */
    private $item;

    public function __construct(array $item) {
        $this->item = $item;
    }

    public function getPrice() {
        return $this->item['price_wt'];
    }

    public function getName() {
        return $this->item['name'];
    }

    public function getQtyOrdered() {
        return $this->item['quantity'];
    }
}
