<?php
/**
 * Created by PhpStorm.
 * User: hitrov
 * Date: 2019-03-21
 * Time: 14:51
 */

namespace Monetha\Response;


use Monetha\Response\Exception\IntegrationSecretNotFoundException;
use stdClass;

class ValidateApiKey extends AbstractResponse
{
    /**
     * @var string
     */
    private $integrationSecret;

    /**
     * ValidateApiKey constructor.
     * @param stdClass $responseJson
     * @throws IntegrationSecretNotFoundException
     */
    public function setResponseJson(stdClass $responseJson)
    {
        parent::setResponseJson($responseJson);

        if (empty($responseJson->integration_secret)) {
            throw new IntegrationSecretNotFoundException(
                'Integration secret not found, response: ' . json_encode($responseJson)
            );
        }

        $this->integrationSecret = $responseJson->integration_secret;
    }

    /**
     * @return string
     */
    public function getIntegrationSecret()
    {
        return $this->integrationSecret;
    }
}