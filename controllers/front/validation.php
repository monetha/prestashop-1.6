<?php

require_once(__DIR__ . './../../Services/GatewayService.php');

use Monetha\Config;
use Monetha\Services\GatewayService;

class MonethaGatewayValidationModuleFrontController extends ModuleFrontController
{
    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        $conf = Config::get_configuration();

        $testMode = $conf[Config::PARAM_TEST_MODE];
        $merchantSecret = $conf[Config::PARAM_MERCHANT_SECRET];
        $monethaApiKey = $conf[Config::PARAM_MONETHA_API_KEY];
        $gatewayService = new GatewayService($merchantSecret, $monethaApiKey, $testMode);

        $cart = $this->context->cart;
        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        // Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'monethagateway') {
                $authorized = true;
                break;
            }
        }
        if (!$authorized) {
            die($this->module->l('This payment method is not available.', 'validation'));
        }

        $orderAdapter = new Monetha\OrderAdapter($cart, $this->context->currency->iso_code, _PS_BASE_URL_);

        $authorizationRequest = new Monetha\AuthorizationRequest();
        $offerBody = $gatewayService->prepareOfferBody($orderAdapter, $cart->id);
        $paymentUrl = $authorizationRequest->getPaymentUrl($offerBody);

        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $currency = $this->context->currency;
        $total = (float)$cart->getOrderTotal(true, Cart::BOTH);
        $mailVars = array(
            '{bankwire_owner}' => Configuration::get('BANK_WIRE_OWNER'),
            '{bankwire_details}' => nl2br(Configuration::get('BANK_WIRE_DETAILS')),
            '{bankwire_address}' => nl2br(Configuration::get('BANK_WIRE_ADDRESS')),
            '{payment_url}' => $paymentUrl,
        );

        $this->module->validateOrder($cart->id, Configuration::get(Monetha\Config::ORDER_STATUS), $total, $this->module->displayName, null, $mailVars, (int)$currency->id, false, $customer->secure_key);
        //Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart->id.'&id_module='.$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key);

        Tools::redirectLink($paymentUrl);
    }
}
