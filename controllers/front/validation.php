<?php

require __DIR__ . '/../../vendor/autoload.php';

class MonethaGatewayValidationModuleFrontController extends ModuleFrontController
{
    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
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
        );

        $this->module->validateOrder($cart->id, Configuration::get(Monetha\Config::ORDER_STATUS), $total, $this->module->displayName, null, $mailVars, (int)$currency->id, false, $customer->secure_key);

        $data = Db::getInstance()->executeS("SELECT `payment_url` FROM `"._DB_PREFIX_."monetha_gateway` WHERE `cart_id` = '{$cart->id}' LIMIT 1");
        $row = reset($data);
        Tools::redirectLink($row['payment_url']);
    }
}
