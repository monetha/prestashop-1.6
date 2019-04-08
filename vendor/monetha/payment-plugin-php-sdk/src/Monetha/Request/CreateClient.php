<?php
/**
 * Created by PhpStorm.
 * User: hitrov
 * Date: 2019-03-21
 * Time: 14:51
 */

namespace Monetha\Request;

use Monetha\Payload\AbstractPayload;
use Monetha\Response\CreateClient as CreateClientResponse;

class CreateClient extends AbstractRequest
{
    protected $uri = 'v1/clients';

    /**
     * CreateClient constructor.
     * @param AbstractPayload $payload
     * @param $token
     * @param $apiUrlPrefix
     * @param null $uri
     */
    public function __construct(AbstractPayload $payload, $token, $apiUrlPrefix, $uri = null)
    {
        $this->response = new CreateClientResponse();

        parent::__construct($payload, $token, $apiUrlPrefix, $uri);
    }
}