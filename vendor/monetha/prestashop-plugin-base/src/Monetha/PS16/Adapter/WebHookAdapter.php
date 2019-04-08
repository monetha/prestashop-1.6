<?php

namespace Monetha\PS16\Adapter;

use Order;
use Monetha\Adapter\WebHookAdapterAbstract;

class WebHookAdapter extends WebHookAdapterAbstract {
    /**
     * @var Order
     */
    private $order;

    public function __construct(Order $order = null)
    {
        $this->order = $order;
    }

    public function cancel($note) {
        $history = new \OrderHistory();
        $history->id_order = $this->getOrderId();
        $history->changeIdOrderState(6, $this->getOrderId(), true);
        return $history->save();
    }

    public function finalize() {
        $history = new \OrderHistory();
        $history->id_order = $this->getOrderId();
        $history->changeIdOrderState(2, $this->getOrderId(), true);
        return $history->save();
    }

    public function authorize() {
        $history = new \OrderHistory();
        $history->id_order = $this->getOrderId();
        $history->changeIdOrderState(2, $this->getOrderId(), true);
        return $history->save();
    }
}
