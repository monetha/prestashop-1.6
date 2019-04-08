<?php

namespace Monetha\Adapter;

interface ClientAdapterInterface {
    /**
     * @return string
     */
    public function getContactName();

    /**
     * @return string
     */
    public function getContactEmail();

    /**
     * @return string
     */
    public function getContactPhoneNumber();

    /**
     * @return string
     */
    public function getCountryIsoCode();

    /**
     * @return string
     */
    public function getAddress();

    /**
     * @return string
     */
    public function getCity();

    /**
     * @return string
     */
    public function getZipCode();
}
