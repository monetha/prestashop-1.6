<?php

require __DIR__ . '/../../vendor/autoload.php';

use Monetha\Response\Exception\ApiException;
use Monetha\PS16\Adapter\OrderAdapter;
use Monetha\PS16\Adapter\ClientAdapter;
use Monetha\Services\GatewayService;
use Monetha\PS16\Adapter\ConfigAdapter;

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

            return;
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

            return;
        }

        try {
            $orderAdapter = new OrderAdapter($cart, $this->context->currency->iso_code, _PS_BASE_URL_);

            $address = new Address($this->context->cart->id_address_delivery);
            $clientAdapter = new ClientAdapter($address, $this->context->customer);

            $configAdapter = new ConfigAdapter(false);

            $gatewayService = new GatewayService($configAdapter);

            $executeOfferResponse = $gatewayService->getExecuteOfferResponse($orderAdapter, $clientAdapter);

            $paymentUrl = $executeOfferResponse->getPaymentUrl();

            $currency = $this->context->currency;
            $total = (float)$cart->getOrderTotal(true, Cart::BOTH);
            $mailVars = array(
                '{bankwire_owner}' => Configuration::get('BANK_WIRE_OWNER'),
                '{bankwire_details}' => nl2br(Configuration::get('BANK_WIRE_DETAILS')),
                '{bankwire_address}' => nl2br(Configuration::get('BANK_WIRE_ADDRESS')),
                '{payment_url}' => $paymentUrl,
            );

            Db::getInstance()->insert("monetha_gateway", array(
                'monetha_id' => $executeOfferResponse->getOrderId(),
                'payment_url' => $paymentUrl,
                'cart_id' => $cart->id,
            ));

            $this->module->validateOrder($cart->id, Configuration::get(Monetha\Config::ORDER_STATUS), $total, $this->module->displayName, null, $mailVars, (int)$currency->id, false, $customer->secure_key);

        } catch (ApiException $e) {
            $message = sprintf(
                'Status code: %s, error: %s, message: %s',
                $e->getApiStatusCode(),
                $e->getApiErrorCode(),
                $e->getMessage()
            );
            error_log($message);

            $toolsError = Tools::displayError($e->getFriendlyMessage());
            $this->context->cookie->__set('redirect_errors',$toolsError);
            Tools::redirect('index.php?controller=order&step=3&monetha_error=1');

            return;

        } catch(\Exception $e) {
            $toolsError = Tools::displayError($e->getMessage());
            $this->context->cookie->__set('redirect_errors',$toolsError);
            Tools::redirect('index.php?controller=order&step=3&monetha_error=1');

            return;
        }

        Tools::redirectLink($paymentUrl);
    }
}
