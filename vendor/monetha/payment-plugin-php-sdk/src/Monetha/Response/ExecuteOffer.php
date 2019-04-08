<?php
/**
 * Created by PhpStorm.
 * User: hitrov
 * Date: 2019-03-21
 * Time: 14:51
 */

namespace Monetha\Response;


use Monetha\Response\Exception\OrderIdNotFoundException;
use Monetha\Response\Exception\OrderNotFoundException;
use Monetha\Response\Exception\PaymentUrlNotFoundException;
use stdClass;

class ExecuteOffer extends AbstractResponse
{
    /**
     * @var stdClass
     */
    private $order;

    /**
     * ExecuteOffer constructor.
     * @param stdClass $responseJson
     * @throws OrderNotFoundException
     */
    public function setResponseJson(stdClass $responseJson)
    {
        parent::setResponseJson($responseJson);

        if (empty($responseJson->order)) {
            throw new OrderNotFoundException(
                'Order not found, response: ' . json_encode($responseJson)
            );
        }

        $this->order = $responseJson->order;
    }

    /**
     * @return string
     * @throws PaymentUrlNotFoundException
     */
    public function getPaymentUrl()
    {
        if (empty($this->order->payment_url)) {
            throw new PaymentUrlNotFoundException('Payment url not found, order: ' . json_encode($this->order));
        }

        return $this->order->payment_url;
    }

    /**
     * @return string
     * @throws OrderIdNotFoundException
     */
    public function getOrderId()
    {
        if (empty($this->order->id)) {
            throw new OrderIdNotFoundException('Order id not found, order: ' . json_encode($this->order));
        }

        return $this->order->id;
    }

    /**
     * @return stdClass
     */
    public function getOrder()
    {
        return $this->order;
    }
}