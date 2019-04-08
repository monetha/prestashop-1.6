<?php

namespace Monetha\PS16\Adapter;

use Monetha\Adapter\ConfigAdapterInterface;
use Monetha\Config;
use Monetha\ConfigAdapterTrait;

class ConfigAdapter implements ConfigAdapterInterface {
    use ConfigAdapterTrait;

    public function __construct($validateConfig = true)
    {
        $conf = Config::get_configuration($validateConfig);
        $this->testMode = $conf[Config::PARAM_TEST_MODE];
        $this->merchantSecret = $conf[Config::PARAM_MERCHANT_SECRET];
        $this->monethaApiKey = $conf[Config::PARAM_MONETHA_API_KEY];
    }
}
