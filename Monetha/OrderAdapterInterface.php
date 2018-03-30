<?php

namespace Monetha;

interface OrderAdapterInterface {
    public function getItems();

    public function getGrandTotalAmount();

    public function getCurrencyCode();

    public function getBaseUrl();
}
