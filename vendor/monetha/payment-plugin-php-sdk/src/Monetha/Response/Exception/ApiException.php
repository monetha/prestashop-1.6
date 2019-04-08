<?php
/**
 * Created by PhpStorm.
 * User: hitrov
 * Date: 2019-03-21
 * Time: 16:38
 */

namespace Monetha\Response\Exception;


use Throwable;

class ApiException extends \Exception
{
    const UNKNOWN_ERROR_MESSAGE = 'Unknown error has occurred, please contact merchant.';

    const EXCEPTION_MESSAGE_MAPPING = [
        'INVALID_PHONE_NUMBER' => 'Invalid phone number',
        'AUTH_TOKEN_INVALID' => 'Monetha plugin setup is invalid, please contact merchant.',
        'INVALID_PHONE_COUNTRY_CODE' => 'This country code is invalid, please input correct country code.',
        'AMOUNT_TOO_BIG' => 'The value of your cart exceeds the maximum amount. Please remove some of the items from the cart.',
        'AMOUNT_TOO_SMALL' => 'Amount_fiat in body should be greater than or equal to 0.01',
        'PROCESSOR_MISSING' => 'Can\'t process order, please contact merchant.',
        'UNSUPPORTED_CURRENCY' => 'Selected currency is not supported by Monetha.',
    ];

    /**
     * @var string
     */
    private $apiErrorCode = '';

    private $apiStatusCode;

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->apiErrorCode = $code;
    }

    /**
     * @param string $apiErrorCode
     */
    public function setApiErrorCode($apiErrorCode)
    {
        $this->apiErrorCode = $apiErrorCode;
    }

    /**
     * @return string
     */
    public function getApiErrorCode()
    {
        return $this->apiErrorCode;
    }

    /**
     * @return string
     */
    public function getFriendlyMessage() {
        return !empty(self::EXCEPTION_MESSAGE_MAPPING[$this->apiErrorCode]) ?
            self::EXCEPTION_MESSAGE_MAPPING[$this->apiErrorCode] :
            self::UNKNOWN_ERROR_MESSAGE;
    }

    /**
     * @return mixed
     */
    public function getApiStatusCode()
    {
        return $this->apiStatusCode;
    }

    /**
     * @param mixed $apiStatusCode
     */
    public function setApiStatusCode($apiStatusCode)
    {
        $this->apiStatusCode = $apiStatusCode;
    }
}