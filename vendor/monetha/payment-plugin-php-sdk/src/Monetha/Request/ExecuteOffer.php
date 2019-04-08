<?php
/**
 * Created by PhpStorm.
 * User: hitrov
 * Date: 2019-03-21
 * Time: 14:51
 */

namespace Monetha\Request;

use Monetha\Payload\AbstractPayload;
use Monetha\Response\ExecuteOffer as ExecuteOfferResponse;

class ExecuteOffer extends AbstractRequest
{
    protected $uri = 'v1/deals/execute';

    /**
     * ExecuteOffer constructor.
     * @param AbstractPayload $payload
     * @param $token
     * @param $apiUrlPrefix
     * @param null $uri
     */
    public function __construct(AbstractPayload $payload, $token, $apiUrlPrefix, $uri = null)
    {
        $this->response = new ExecuteOfferResponse();

        parent::__construct($payload, $token, $apiUrlPrefix, $uri);
    }
}