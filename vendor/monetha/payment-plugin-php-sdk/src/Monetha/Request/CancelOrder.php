<?php
/**
 * Created by PhpStorm.
 * User: hitrov
 * Date: 2019-03-21
 * Time: 14:51
 */

namespace Monetha\Request;

use Monetha\Response\CancelOrder as CancelOrderResponse;
use Monetha\Payload\AbstractPayload;

class CancelOrder extends AbstractRequest
{
    /**
     * CancelOrder constructor.
     * @param AbstractPayload $payload
     * @param $token
     * @param $apiUrlPrefix
     * @param null $uri
     */
    public function __construct(AbstractPayload $payload, $token, $apiUrlPrefix, $uri = null)
    {
        $this->response = new CancelOrderResponse();

        parent::__construct($payload, $token, $apiUrlPrefix, $uri);
    }
}