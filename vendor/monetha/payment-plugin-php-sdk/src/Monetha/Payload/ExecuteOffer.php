<?php
/**
 * Created by PhpStorm.
 * User: hitrov
 * Date: 2019-03-21
 * Time: 14:52
 */

namespace Monetha\Payload;

class ExecuteOffer extends AbstractPayload
{
    /**
     * ExecuteOffer constructor.
     * @param string $token
     */
    public function __construct($token)
    {
        $payload = ['token' => $token];

        $this->setPayload($payload);
    }
}