<?php

namespace Monetha\Adapter;

interface InterceptorInterface {
    /**
     * @return float
     */
    public function getPrice();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return int
     */
    public function getQtyOrdered();
}
