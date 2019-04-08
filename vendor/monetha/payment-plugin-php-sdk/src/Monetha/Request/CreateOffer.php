<?php
/**
 * Created by PhpStorm.
 * User: hitrov
 * Date: 2019-03-21
 * Time: 14:51
 */

namespace Monetha\Request;

use Monetha\Payload\AbstractPayload;
use Monetha\Response\CreateOffer as CreateOfferResponse;

class CreateOffer extends AbstractRequest
{
    protected $uri = 'v1/merchants/offer_auth';

    /**
     * CreateOffer constructor.
     * @param AbstractPayload $payload
     * @param $token
     * @param $apiUrlPrefix
     * @param null $uri
     */
    public function __construct(AbstractPayload $payload, $token, $apiUrlPrefix, $uri = null)
    {
        $this->response = new CreateOfferResponse();

        parent::__construct($payload, $token, $apiUrlPrefix, $uri);
    }
}