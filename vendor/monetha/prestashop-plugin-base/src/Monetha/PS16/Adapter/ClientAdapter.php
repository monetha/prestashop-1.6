<?php

namespace Monetha\PS16\Adapter;

use Monetha\Adapter\ClientAdapterInterface;
use Customer;
use Address;

class ClientAdapter implements ClientAdapterInterface {

    /**
     * @var Address
     */
    private $address;

    /**
     * @var string
     */
    private $email;

    public function __construct(Address $address, Customer $customer)
    {
        $this->address = $address;
        $this->email = $customer->email;
    }

    /**
     * @return string
     */
    public function getContactName() {
        return $this->address->firstname . ' ' . $this->address->lastname;
    }

    /**
     * @return string
     */
    public function getContactEmail() {
        return $this->email;
    }

    /**
     * @return string|null
     */
    public function getContactPhoneNumber() {
        $phoneNumber = $this->address->phone_mobile ? $this->address->phone_mobile : $this->address->phone;

        return $phoneNumber;
    }

    /**
     * @return string
     */
    public function getCountryIsoCode() {
        $iso_code = \Country::getIsoById($this->address->id_country);

        return $iso_code;
    }

    /**
     * @return string
     */
    public function getAddress() {
        return $this->address->address1;
    }

    /**
     * @return string
     */
    public function getCity() {
        return $this->address->city;
    }

    /**
     * @return string
     */
    public function getZipCode() {
        return $this->address->postcode;
    }
}
