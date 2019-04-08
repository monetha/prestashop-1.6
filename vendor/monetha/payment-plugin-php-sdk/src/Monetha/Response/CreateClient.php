<?php
/**
 * Created by PhpStorm.
 * User: hitrov
 * Date: 2019-03-21
 * Time: 14:51
 */

namespace Monetha\Response;


use Monetha\Response\Exception\ClientIdNotFoundException;
use stdClass;

class CreateClient extends AbstractResponse
{
    /**
     * @var int
     */
    private $clientId;

    /**
     * CreateClient constructor.
     * @param stdClass $responseJson
     * @throws ClientIdNotFoundException
     */
    public function setResponseJson(stdClass $responseJson)
    {
        parent::setResponseJson($responseJson);

        if (empty($responseJson->client_id)) {
            throw new ClientIdNotFoundException(
                'Client id not found, response: ' . json_encode($responseJson)
            );
        }

        $this->clientId = $responseJson->client_id;
    }

    /**
     * @return int
     */
    public function getClientId()
    {
        return $this->clientId;
    }
}