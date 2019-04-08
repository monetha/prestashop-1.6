<?php

namespace Monetha;

trait ConfigAdapterTrait {
    /**
     * @var string
     */
    private $testMode;

    /**
     * @var string
     */
    private $merchantSecret;

    /**
     * @var string
     */
    private $monethaApiKey;

    /**
     * @return string
     */
    public function getMerchantSecret()
    {
        return $this->merchantSecret;
    }

    /**
     * @return string
     */
    public function getMthApiKey()
    {
        return $this->monethaApiKey;
    }

    /**
     * @return bool
     */
    public function getIsTestMode()
    {
        return (bool) $this->testMode;
    }
}
