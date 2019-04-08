<?php

namespace Monetha\Adapter;

interface WebHookAdapterInterface {
    /**
     * @return bool
     */
    public function cancel($note);

    /**
     * @return bool
     */
    public function finalize();

    /**
     * @return bool
     */
    public function authorize();
}
