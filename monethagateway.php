<?php

require_once dirname(__FILE__).'/../../modules/monethagateway/vendor/autoload.php';

if (!defined('_PS_VERSION_'))
    exit;

class MonethaGateway extends PaymentModule
{
	protected $_html = '';
	protected $_postErrors = array();

	public $details;
	public $owner;
	public $address;
	public $extra_mail_vars;

    const MODULE_NAME = 'monethagateway';
    const DISPLAY_NAME = 'Monetha Gateway';

    const COLOR = '#00e882';

	public function __construct()
	{
		$this->name = self::MODULE_NAME;
		$this->tab = 'payments_gateways';
		$this->version = '1.1.2';
		$this->author = 'Monetha';
		$this->controllers = array('payment', 'validation');
		$this->is_eu_compatible = 1;

		$this->currencies = true;
		$this->currencies_mode = 'checkbox';

		$this->bootstrap = true;
		parent::__construct();

		$this->displayName = $this->l(self::DISPLAY_NAME);
		$this->description = $this->l('Accept payments for your products via Monetha Gateway transfer.');
		$this->confirmUninstall = $this->l('Are you sure about removing these details?');
		$this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6.99.99');

		if (!count(Currency::checkPaymentCurrencies($this->id)))
			$this->warning = $this->l('No currency has been set for this module.');

		$this->extra_mail_vars = array(
										'{bankwire_owner}' => Configuration::get('BANK_WIRE_OWNER'),
										'{bankwire_details}' => nl2br(Configuration::get('BANK_WIRE_DETAILS')),
										'{bankwire_address}' => nl2br(Configuration::get('BANK_WIRE_ADDRESS'))
										);
	}

	public function install()
	{
		if (
		    !parent::install() || 
            !$this->registerHook('payment') || 
            !$this->registerHook('displayPaymentEU') || 
            !$this->registerHook('paymentReturn')
        )
			return false;

        $configuration = Monetha\Config::get_predefined_configuration();

        Configuration::updateValue(self::MODULE_NAME, json_encode($configuration));

        $this->create_order_state();
        $this->copy_email_templates();

		return true;
	}

	public function uninstall()
	{
	    $this->delete_order_state();
	    $this->delete_email_templates();

        if (
		    !Configuration::deleteByName(self::MODULE_NAME) ||
            !Configuration::deleteByName(Monetha\Config::ORDER_STATUS) ||
            !parent::uninstall()
        )
			return false;

		return true;
	}

    /**
     * @throws PrestaShopDatabaseException
     */
    private function create_order_state() {
        $db = Db::getInstance();

        $db->insert('order_state', array(
            'send_email' => 1,
            'module_name' => self::MODULE_NAME,
            'color' => self::COLOR,
            'logable' => 1,
        ));

        $order_state_id = $db->Insert_ID();
        Configuration::updateValue(Monetha\Config::ORDER_STATUS, $order_state_id);

        $db->insert('order_state_lang', array(
            'id_order_state' => $order_state_id,
            'id_lang' => 1,
            'name' => 'Awaiting Monetha payment',
            'template' => self::MODULE_NAME,
        ));
    }

    private function delete_order_state() {
        $db = Db::getInstance();
        $order_state_id = Configuration::get(Monetha\Config::ORDER_STATUS);

        $db->delete('order_state_lang', "id_order_state = $order_state_id", 1);
        $db->delete('order_state', "id_order_state = $order_state_id", 1);
    }

    private function copy_email_templates() {
        $source = _PS_MODULE_DIR_ . self::MODULE_NAME . '/mails/en/' . self::MODULE_NAME;
        $destination = _PS_MAIL_DIR_ . 'en/' . self::MODULE_NAME;

        $txt_template_source_path = $source . '.txt';
        $txt_template_destination_path = $destination . '.txt';
        if (file_exists($txt_template_source_path)) {
            copy($txt_template_source_path, $txt_template_destination_path);
        }

        $html_template_source_path = $source . '.html';
        $txt_template_destination_path = $destination . '.html';
        if (file_exists($html_template_source_path)) {
            copy($html_template_source_path, $txt_template_destination_path);
        }
    }

    private function delete_email_templates() {
        $path = _PS_MAIL_DIR_ . 'en/' . self::MODULE_NAME;

        $txt_template_path = $path . '.txt';
        if (file_exists($txt_template_path)) {
            unlink($txt_template_path);
        }

        $html_template_path = $path . '.html';
        if (file_exists($html_template_path)) {
            unlink($html_template_path);
        }
    }

    public function displayForm()
    {
        $output = null;
        try {
            $conf = Monetha\Config::get_configuration();
        } catch(\Exception $e) {
            $output .= $this->displayError('Current configuration error: ' . $this->l($e->getMessage()));
        }

        // Get default language
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        // Language
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit'.$this->name;
        $helper->toolbar_btn = array(
            'save' =>
                array(
                    'desc' => $this->l('Save'),
                    'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                        '&token='.Tools::getAdminTokenLite('AdminModules'),
                ),
            'back' => array(
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            )
        );

        $yes_no_options = [
            'query' => [
                [
                    'id_option' => '1',
                    'name' => 'Yes',
                ],
                [
                    'id_option' => '0',
                    'name' => 'No',
                ],
            ],
            'id' => 'id_option',
            'name' => 'name',
        ];

        $labels = Monetha\Config::get_labels();

        $fields_form[0]['form'] = [
            'legend' => [
                'title' => $this->l( self::DISPLAY_NAME .' Settings'),
            ],
            'input' => [
                [
                    'type' => 'select',
                    'name' => Monetha\Config::PARAM_ENABLED,
                    'label' => $this->l($labels[Monetha\Config::PARAM_ENABLED]),
                    'options' => $yes_no_options,
                    'required' => true,
                ],
                [
                    'type' => 'select',
                    'name' => Monetha\Config::PARAM_TEST_MODE,
                    'label' => $this->l($labels[Monetha\Config::PARAM_TEST_MODE]),
                    'options' => $yes_no_options,
                    'required' => true,
                ],
                [
                    'type' => 'text',
                    'name' => Monetha\Config::PARAM_MERCHANT_KEY,
                    'label' => $this->l($labels[Monetha\Config::PARAM_MERCHANT_KEY]),
                    'required' => true,
                ],
                [
                    'type' => 'text',
                    'name' => Monetha\Config::PARAM_MERCHANT_SECRET,
                    'label' => $this->l($labels[Monetha\Config::PARAM_MERCHANT_SECRET]),
                    'required' => true,
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            ],
        ];

        $helper->fields_value = $conf;

        return $output . $helper->generateForm($fields_form);
    }

    private function get_form_values() {
        $enabled = Tools::getValue(Monetha\Config::PARAM_ENABLED);
        $test_mode = Tools::getValue(Monetha\Config::PARAM_TEST_MODE);
        $merchant_key = Tools::getValue(Monetha\Config::PARAM_MERCHANT_KEY);
        $merchant_secret = Tools::getValue(Monetha\Config::PARAM_MERCHANT_SECRET);

        return [
            Monetha\Config::PARAM_ENABLED => $enabled,
            Monetha\Config::PARAM_TEST_MODE => $test_mode,
            Monetha\Config::PARAM_MERCHANT_KEY => $merchant_key,
            Monetha\Config::PARAM_MERCHANT_SECRET => $merchant_secret,
        ];
    }

	public function getContent()
	{
        $output = null;

        if (Tools::isSubmit('submit'.$this->name)) {
            $form_values = $this->get_form_values();

            try {
                Monetha\Config::validate($form_values);
                Configuration::updateValue(self::MODULE_NAME, json_encode($form_values));
                $output .= $this->displayConfirmation($this->l('Settings updated'));

            } catch(\Exception $e) {
                $output .= $this->displayError($this->l($e->getMessage()));
            }
		}

        return $output.$this->displayForm();
	}

	public function hookPayment($params)
	{
		if (!$this->active)
			return;
		if (!$this->checkCurrency($params['cart']))
			return;

		$this->smarty->assign(array(
			'this_path' => $this->_path,
			'this_path_bw' => $this->_path,
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
		));
		return $this->display(__FILE__, 'payment.tpl');
	}

	public function hookDisplayPaymentEU($params)
	{
		if (!$this->active)
			return;

		if (!$this->checkCurrency($params['cart']))
			return;

		$payment_options = array(
			'cta_text' => $this->l('Pay by Monetha Gateway'),
			'logo' => Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/bankwire.jpg'),
			'action' => $this->context->link->getModuleLink($this->name, 'validation', array(), true)
		);

		return $payment_options;
	}

	public function hookPaymentReturn($params)
	{
		if (!$this->active)
			return;

		$state = $params['objOrder']->getCurrentState();
		if (in_array($state, array(Configuration::get('PS_OS_BANKWIRE'), Configuration::get('PS_OS_OUTOFSTOCK'), Configuration::get('PS_OS_OUTOFSTOCK_UNPAID'))))
		{
			$this->smarty->assign(array(
				'total_to_pay' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
				'bankwireDetails' => Tools::nl2br($this->details),
				'bankwireAddress' => Tools::nl2br($this->address),
				'bankwireOwner' => $this->owner,
				'status' => 'ok',
				'id_order' => $params['objOrder']->id
			));
			if (isset($params['objOrder']->reference) && !empty($params['objOrder']->reference))
				$this->smarty->assign('reference', $params['objOrder']->reference);
		}
		else
			$this->smarty->assign('status', 'failed');
		return $this->display(__FILE__, 'payment_return.tpl');
	}

	public function checkCurrency($cart)
	{
		$currency_order = new Currency($cart->id_currency);
		$currencies_module = $this->getCurrency($cart->id_currency);

		if (is_array($currencies_module))
			foreach ($currencies_module as $currency_module)
				if ($currency_order->id == $currency_module['id_currency'])
					return true;
		return false;
	}
}
