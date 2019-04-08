<?php
/**
 * Created by PhpStorm.
 * User: hitrov
 * Date: 2019-03-21
 * Time: 14:51
 */

namespace Monetha\Request;

use Monetha\Payload\AbstractPayload;
use Monetha\Response\ValidateApiKey as ValidateApiKeyResponse;

class ValidateApiKey extends AbstractRequest
{
    protected $method = 'GET';

    /**
     * ValidateApiKey constructor.
     * @param AbstractPayload $payload
     * @param $token
     * @param $apiUrlPrefix
     * @param null $uri
     */
    public function __construct(AbstractPayload $payload, $token, $apiUrlPrefix, $uri = null)
    {
        $this->response = new ValidateApiKeyResponse();

        parent::__construct($payload, $token, $apiUrlPrefix, $uri);
    }
}