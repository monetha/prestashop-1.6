<?php
/**
 * Created by PhpStorm.
 * User: hitrov
 * Date: 2019-03-21
 * Time: 14:52
 */

namespace Monetha\Payload;


abstract class AbstractPayload
{
    /**
     * @var array
     */
    protected $payload;

    /**
     * @return string
     */
    public function __toString()
    {
        return !is_null($this->payload) ? json_encode($this->payload) : '';
    }

    /**
     * @param array $payload
     */
    protected function setPayload($payload)
    {
        $this->payload = $payload;
    }
}