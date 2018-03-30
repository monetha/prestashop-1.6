<?php

namespace Monetha;

interface Interceptor {
    public function getPrice();

    public function getName();

    public function getQtyOrdered();
}
