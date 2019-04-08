<?php
/**
 * Created by PhpStorm.
 * User: hitrov
 * Date: 2019-03-21
 * Time: 14:51
 */

namespace Monetha\Response;

use stdClass;

abstract class AbstractResponse
{
    /**
     * @var stdClass
     */
    protected $responseJson;

    /**
     * @param stdClass $responseJson
     */
    public function setResponseJson(stdClass $responseJson)
    {
        $this->responseJson = $responseJson;
    }

    /**
     * @return stdClass
     */
    public function getResponseJson()
    {
        return $this->responseJson;
    }
}