<?php
/**
 * Created by PhpStorm.
 * User: hitrov
 * Date: 2019-03-21
 * Time: 14:52
 */

namespace Monetha\Payload;


class CancelOrder extends AbstractPayload
{
    /**
     * CancelOrder constructor.
     */
    public function __construct()
    {
        $this->setPayload(['cancel_reason'=> 'Order cancelled from shop']);
    }
}