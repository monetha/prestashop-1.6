<?php

namespace Monetha\Adapter;

interface ConfigAdapterInterface {
    /**
     * @return string
     */
    public function getMerchantSecret();

    /**
     * @return string
     */
    public function getMthApiKey();

    /**
     * @return bool
     */
    public function getIsTestMode();
}
