<?php
/**
 * Created by PhpStorm.
 * User: hitrov
 * Date: 2019-03-21
 * Time: 14:52
 */

namespace Monetha\Payload;


use Monetha\Adapter\ClientAdapterInterface;

class CreateClient extends AbstractPayload
{
    /**
     * CreateClient constructor.
     * @param ClientAdapterInterface $clientAdapter
     */
    public function __construct(ClientAdapterInterface $clientAdapter)
    {
        $payload = [
            'contact_name' => $clientAdapter->getContactName(),
            'contact_email' => $clientAdapter->getContactEmail(),
            'contact_phone_number' => preg_replace('/\D/', '', $clientAdapter->getContactPhoneNumber()),
            'country_code_iso' => $clientAdapter->getCountryIsoCode(),
            'address' => $clientAdapter->getAddress(),
            'city' => $clientAdapter->getCity(),
            'zipcode' => $clientAdapter->getZipCode(),
        ];

        $this->setPayload($payload);
    }
}