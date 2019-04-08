<?php

namespace Monetha\Adapter;

use Monetha\Response\Exception\ValidationException;
use Monetha\Services\GatewayService;
use Monetha\Constants\Resource;
use Monetha\Constants\EventType;

/**
 * Class WebHookAdapterAbstract
 * @package Monetha\Adapter
 */
abstract class WebHookAdapterAbstract implements WebHookAdapterInterface {
    /**
     * @param ConfigAdapterInterface $configAdapter
     * @param string $body
     * @param string $signature
     * @return bool
     * @throws ValidationException
     */
    final public function processWebHook(ConfigAdapterInterface $configAdapter, $body, $signature)
    {
        $data = json_decode($body);

        // Just simple ping event action
        if ($data->event == EventType::PING) {
            return true;
        }

        $gatewayService = new GatewayService($configAdapter);

        if (!$gatewayService->validateSignature($signature, $body)) {
            throw new ValidationException('Signature is not valid.');
        }

        switch ($data->resource) {
            case Resource::ORDER:
                switch ($data->event) {
                    case EventType::CANCELLED:
                        return $this->cancel($data->payload->note);

                    case EventType::FINALIZED:
                        return $this->finalize();

                    case EventType::MONEY_AUTHORIZED:
                        return $this->authorize();

                    default:
                        throw new ValidationException('Bad event type: ' . $data->event);
                }

            default:
                throw new ValidationException('Bad resource: ' . $data->resource);
        }
    }

    /**
     * @return bool
     */
    abstract public function cancel($note);

    /**
     * @return bool
     */
    abstract public function finalize();

    /**
     * @return bool
     */
    abstract public function authorize();
}
