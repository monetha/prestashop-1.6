<?php

namespace Monetha;


class Config {
    const PARAM_ENABLED = 'enabled';
    const PARAM_TEST_MODE = 'testMode';
    const PARAM_MERCHANT_KEY = 'merchantKey';
    const PARAM_MERCHANT_SECRET = 'merchantSecret';

    private static $configuration = [
        self::PARAM_ENABLED => '1',
        self::PARAM_TEST_MODE => '1',
        self::PARAM_MERCHANT_KEY => 'MONETHA_SANDBOX_KEY',
        self::PARAM_MERCHANT_SECRET => 'MONETHA_SANDBOX_SECRET',
    ];

    const ORDER_STATUS = 'PS_OS_MONETHA';

    public static function get_predefined_configuration() {
        return self::$configuration;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public static function get_configuration() {
        $confJson = \Configuration::get('monethagateway');
        $conf = json_decode($confJson, true);

        self::validate($conf);

        return $conf;
    }
    
    private static $labels = [
        self::PARAM_ENABLED => 'Enabled',
        self::PARAM_TEST_MODE => 'Test Mode',
        self::PARAM_MERCHANT_KEY => 'Monetha Key',
        self::PARAM_MERCHANT_SECRET => 'Monetha Secret',
    ];

    public static function get_labels() {
        return self::$labels;
    }
    
    /**
     * @param $form_values
     *
     * @throws \Exception
     */
    public static function validate($form_values) {
        $enabled = $form_values[self::PARAM_ENABLED];
        $test_mode = $form_values[self::PARAM_TEST_MODE];
        $merchant_key = $form_values[self::PARAM_MERCHANT_KEY];
        $merchant_secret = $form_values[self::PARAM_MERCHANT_SECRET];

        if (
            $enabled === false ||
            $test_mode === false ||
            $merchant_key === false ||
            $merchant_secret === false
        ) {
            throw new \Exception(implode(', ', self::$labels) . ' required.');
        }

        if ($enabled !== '1' && $enabled !== '0') {
            throw new \Exception('Invalid ' . self::$labels[self::PARAM_ENABLED] . ' parameter');
        }

        if ($test_mode !== '1' && $test_mode !== '0') {
            throw new \Exception('Invalid ' . self::$labels[self::PARAM_TEST_MODE] . ' parameter');
        }

        if (empty($merchant_key)) {
            throw new \Exception('Invalid ' . self::$labels[self::PARAM_MERCHANT_KEY] . ' parameter');
        }

        if (empty($merchant_secret)) {
            throw new \Exception('Invalid ' . self::$labels[self::PARAM_MERCHANT_SECRET] . ' parameter');
        }
    }
}