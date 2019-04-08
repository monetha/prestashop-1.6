<?php

namespace Monetha;

use Monetha\Adapter\ConfigAdapterInterface;
use Monetha\ConfigAdapterTrait;
use Monetha\Services\GatewayService;

class Config implements ConfigAdapterInterface
{
    use ConfigAdapterTrait;

    const PARAM_ENABLED = 'enabled';
    const PARAM_TEST_MODE = 'testMode';
    const PARAM_MERCHANT_SECRET = 'merchantSecret';
    const PARAM_MONETHA_API_KEY = 'monethaApiKey';

    const ORDER_STATUS = 'PS_OS_MONETHA';

    private static $configuration = [
        self::PARAM_ENABLED => '0',
        self::PARAM_TEST_MODE => '1',
        self::PARAM_MERCHANT_SECRET => 'MONETHA_SANDBOX_KEY',
        self::PARAM_MONETHA_API_KEY => 'MONETHA_API_KEY',
    ];

    public static function get_predefined_configuration()
    {
        return self::$configuration;
    }

    /**
     * @param bool $validateConfig
     * @return mixed
     * @throws \Exception
     */
    public static function get_configuration($validateConfig = true)
    {
        $confJson = \Configuration::get('monethagateway');
        $conf = json_decode($confJson, true);

        if ($validateConfig) {
            self::validate($conf, $validateConfig);
        }

        return $conf;
    }

    private static $labels = [
        self::PARAM_ENABLED => 'Enabled',
        self::PARAM_TEST_MODE => 'Test Mode',
        self::PARAM_MERCHANT_SECRET => 'Merchant secret',
        self::PARAM_MONETHA_API_KEY => 'Monetha Api Key',
    ];

    public static function get_labels()
    {
        return self::$labels;
    }

    /**
     * @param $form_values
     * @param bool $validateConfig
     * @throws \Exception
     */
    public static function validate($form_values, $validateConfig = true)
    {
        $enabled = $form_values[self::PARAM_ENABLED];
        $testMode = $form_values[self::PARAM_TEST_MODE];
        $merchantSecret = $form_values[self::PARAM_MERCHANT_SECRET];
        $monethaApiKey = $form_values[self::PARAM_MONETHA_API_KEY];

        if (
            $enabled === false ||
            $testMode === false ||
            $merchantSecret === false ||
            $monethaApiKey === false
        ) {
            throw new \Exception(implode(', ', self::$labels) . ' required.');
        }

        if ($enabled !== '1' && $enabled !== '0') {
            throw new \Exception('Invalid ' . self::$labels[self::PARAM_ENABLED] . ' parameter');
        }

        if ($testMode !== '1' && $testMode !== '0') {
            throw new \Exception('Invalid ' . self::$labels[self::PARAM_TEST_MODE] . ' parameter');
        }

        if (empty($merchantSecret)) {
            throw new \Exception('Invalid ' . self::$labels[self::PARAM_MERCHANT_SECRET] . ' parameter');
        }

        if (empty($monethaApiKey)) {
            throw new \Exception('Invalid ' . self::$labels[self::PARAM_MONETHA_API_KEY] . ' parameter');
        }

        if ($validateConfig) {
            $configAdapter = new self();
            $configAdapter->testMode = $testMode;
            $configAdapter->merchantSecret = $merchantSecret;
            $configAdapter->monethaApiKey = $monethaApiKey;

            // Validate monetha api key with backend
            $gatewayService = new GatewayService($configAdapter);
            if (!$gatewayService->validateApiKey()) {
                throw new \Exception('Merchant secret or Monetha Api Key is not valid!');
            }
        }
    }
}
