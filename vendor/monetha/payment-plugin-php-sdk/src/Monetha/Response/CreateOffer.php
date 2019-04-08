<?php
/**
 * Created by PhpStorm.
 * User: hitrov
 * Date: 2019-03-21
 * Time: 14:51
 */

namespace Monetha\Response;


use Monetha\Response\Exception\TokenNotFoundException;
use stdClass;

class CreateOffer extends AbstractResponse
{
    /**
     * @var string
     */
    private $token;

    /**
     * CreateOffer constructor.
     * @param stdClass $responseJson
     * @throws TokenNotFoundException
     */
    public function setResponseJson(stdClass $responseJson)
    {
        parent::setResponseJson($responseJson);

        if (empty($responseJson->token)) {
            throw new TokenNotFoundException(
                'Token not found, response: ' . json_encode($responseJson)
            );
        }

        $this->token = $responseJson->token;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }
}