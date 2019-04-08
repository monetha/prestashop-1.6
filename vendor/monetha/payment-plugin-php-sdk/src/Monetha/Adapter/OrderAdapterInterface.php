<?php

namespace Monetha\Adapter;

interface OrderAdapterInterface {
    /**
     * Returns array of items
     * which are of the class InterceptorInterface
     *
     * @return InterceptorInterface[]
     */
    public function getItems();

    /**
     * @return float
     */
    public function getGrandTotalAmount();

    /**
     * @return string
     */
    public function getCurrencyCode();

    /**
     * @return string
     */
    public function getBaseUrl();

    /**
     * @return string
     */
    public function getCartId();
}
