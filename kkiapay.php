<?php
/*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

class kkiapay extends PaymentModule
{
	protected $_html = '';
	protected $_postErrors = array();

	public $details;
	public $owner;
	public $address;
	public $extra_mail_vars;
	public function __construct()
	{
		$this->name = 'kkiapay';
		$this->tab = 'payments_gateways';
		$this->version = '1.0.0';
		$this->author = 'kkiapay';
		$this->controllers = array('payment', 'validation');
		$this->is_eu_compatible = 1;

		$this->currencies = true;
		$this->currencies_mode = 'checkbox';

		$config = Configuration::getMultiple(array('KKIAPAY_PUBLIC', 'KKIAPAY_PRIVATE', 'KKIAPAY_SECRET', 'KKIAPAY_COLOR', 'KKIAPAY_CHECKBOX', 'KKIAPAY_POSITION'));
		if (!empty($config['KKIAPAY_PUBLIC']))
			$this->public = $config['KKIAPAY_PUBLIC'];
		if (!empty($config['KKIAPAY_PRIVATE']))
			$this->private = $config['KKIAPAY_PRIVATE'];
		if (!empty($config['KKIAPAY_SECRET']))
			$this->secret = $config['KKIAPAY_SECRET'];
		if (!empty($config['KKIAPAY_COLOR']))
			$this->color = $config['KKIAPAY_COLOR'];
		if (!empty($config['KKIAPAY_CHECKBOX']))
			$this->checkbox = $config['KKIAPAY_CHECKBOX'];
		if (!empty($config['KKIAPAY_POSITION']))
			$this->position = $config['KKIAPAY_POSITION'];

		$this->bootstrap = true;
		parent::__construct();

		$this->displayName = $this->l('kkiapay');
		$this->description = $this->l('kkiaPay permet aux entreprises de recevoir des paiements en toute sécurité via de l\'argent mobile, une carte de crédit ou un compte bancaire.');
		$this->confirmUninstall = $this->l('Voulez-vous désinstaller kkiapay');
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.6.99.99');
		$this->limited_currencies = array('XOF');

		if (!isset($this->public))
			$this->warning = $this->l('La clé publique doit être configurée pour utiliser ce module');
		if (!count(Currency::checkPaymentCurrencies($this->id)))
			$this->warning = $this->l('La monnaie XOF (FCFA) doit être configurée pour utiliser ce module');

		$this->extra_mail_vars = array(
										'{private_key}' => Configuration::get('KKIAPAY_PUBLIC'),
										'{public_key}' => Configuration::get('KKIAPAY_PRIVATE'),
										'{secret}' => Configuration::get('KKIAPAY_SECRET'),
										'{color}' => Configuration::get('KKIAPAY_COLOR'),
										'{checkbox}' => Configuration::get('KKIAPAY_CHECKBOX'),
										'{position}' => Configuration::get('KKIAPAY_POSITION')
										);
	}

	public function install()
	{
		if (!parent::install() || !$this->registerHook('payment') || ! $this->registerHook('displayPaymentEU') || !$this->registerHook('paymentReturn'))
			return false;
		return true;
	}

	public function uninstall()
	{
		if (!Configuration::deleteByName('KKIAPAY_PUBLIC')
				|| !Configuration::deleteByName('KKIAPAY_PRIVATE')
				|| !Configuration::deleteByName('KKIAPAY_SECRET')
				|| !Configuration::deleteByName('KKIAPAY_COLOR')
				|| !Configuration::deleteByName('KKIAPAY_CHECKBOX')
				|| !Configuration::deleteByName('KKIAPAY_POSITION')
				|| !parent::uninstall())
			return false;
		return true;
	}

	protected function _postValidation()
	{
		if (Tools::isSubmit('btnSubmit'))
		{
			if (!Tools::getValue('KKIAPAY_PUBLIC'))
				$this->_postErrors[] = $this->l('La clée publique est recquise.');
		}
	}

	protected function _postProcess()
	{
		if (Tools::isSubmit('btnSubmit'))
		{
			Configuration::updateValue('KKIAPAY_PUBLIC', Tools::getValue('KKIAPAY_PUBLIC'));
			Configuration::updateValue('KKIAPAY_PRIVATE', Tools::getValue('KKIAPAY_PRIVATE'));
			Configuration::updateValue('KKIAPAY_SECRET', Tools::getValue('KKIAPAY_SECRET'));
			Configuration::updateValue('KKIAPAY_COLOR', Tools::getValue('KKIAPAY_COLOR'));
			Configuration::updateValue('KKIAPAY_CHECKBOX', Tools::getValue('KKIAPAY_CHECKBOX'));
			Configuration::updateValue('KKIAPAY_POSITION', Tools::getValue('KKIAPAY_POSITION'));
		}
		$this->_html .= $this->displayConfirmation($this->l('Paramètres mis à jour'));
	}

	protected function _displaykkiapay()
	{
		return $this->display(__FILE__, 'infos.tpl');
	}

	public function getContent()
	{
		if (Tools::isSubmit('btnSubmit'))
		{
			$this->_postValidation();
			if (!count($this->_postErrors))
				$this->_postProcess();
			else
				foreach ($this->_postErrors as $err)
					$this->_html .= $this->displayError($err);
		}
		else
			$this->_html .= '<br />';

		$this->_html .= $this->_displaykkiapay();
		$this->_html .= $this->renderForm();

		return $this->_html;
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
			'cta_text' => $this->l('Payez avec kkiapay'),
			'action' => $this->context->link->getModuleLink($this->name, 'validation', array(), true)
		);

		return $payment_options;
	}

	public function hookPaymentReturn($params)
	{
		if (!$this->active)
			return;

		$state = $params['objOrder']->getCurrentState();
		if (in_array($state, array(Configuration::get('PS_OS_PAYMENT'))))
		{
			$this->smarty->assign(array(
				'total_to_pay' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
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

	public function renderForm()
	{

		$options = array(
            array(
              'id_option' => 'right', 
              'name' => $this->l('A droite de la page') 
            ),
            array(
              'id_option' => 'center',
              'name' => $this->l('Au Centre de la page')
            ),
            array(
                'id_option'=>'left',
                'name'=>$this->l('A Gauche de la page')
            )
        );

		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Paramètres'),
				),
				'input' => array(
					array(
						'type' => 'switch',
						'label' => $this->l('Activez le mode test'),
						'desc'=> $this->l('Configurez sur oui pour mettre en environnement sandbox'),
						'name' => 'KKIAPAY_CHECKBOX',
						'is_bool' => true,
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => "1",
							),
							array(
								'id' => 'active_off',
								'value' => "0",
							)
						),
					),
					array(
						'col' => '6',
						'type' => 'text',
						'label' => $this->l('Clé publique'),
						'name' => 'KKIAPAY_PUBLIC',
						'class'=>'form-control',
						'desc'=> $this->l('Obtenez vos clés d\'API sur le dashboard kkiapay dans la section Développeurs'),
						'required' => true
					),
					array(
						'col' => '6',
						'type' => 'text',
						'label' => $this->l('Clé privé'),
						'name' => 'KKIAPAY_PRIVATE',
						'class'=>'form-control',
						'desc'=> $this->l('Obtenez vos clés d\'API sur le dashboard kkiapay dans la section Développeurs'),
					),
					array(
						'col' => '6',
						'type' => 'text',
						'label' => $this->l('Secret'),
						'name' => 'KKIAPAY_SECRET',
						'class'=>'form-control',
						'desc'=> $this->l('Obtenez vos clés d\'API sur le dashboard kkiapay dans la section Développeurs'),
					),
					array(
						'col' => '6',
						'type' => 'text',
						'label' => $this->l('Couleur du widget kkiapay'),
						'name' => 'KKIAPAY_COLOR',
						'class'=>'form-control',
						'desc'=> $this->l('Paramétrez la couleur de la fenêtre kkiapay. Pour une meilleure harmonisation, utilisez la couleur dominante de votre site ou laissez vide.'),
					),
					array(
						'type' => 'select',
						'lang' => true,
						'label' => $this->l('Disposition du widget kkiapay'),
						'name' => 'KKIAPAY_POSITION',
						'class'=>'form-control',
						'desc'=> $this->l('Utilisez cette option pour contrôler l\'endroit où la fenêtre kkiapay devrait s\'afficher sur votre site'),
						'options' => array(
						  'query' => $options,
						  'id' => 'id_option', 
						  'name' => 'name'
						),
					),
				),
				'submit' => array(
					'title' => $this->l('Sauvegarder'),
				)
			),
		);

		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$helper->module = $this->module;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
		
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = array();

		


		$helper->id = (int)Tools::getValue('id_carrier');
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'btnSubmit';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

		$myModulePosition=Configuration::get('KKIAPAY_POSITION');

        $helper->fields_value['KKIAPAY_PUBLIC'] = Configuration::get('KKIAPAY_PUBLIC');
        $helper->fields_value['KKIAPAY_SECRET'] = Configuration::get('KKIAPAY_SECRET');
        $helper->fields_value['KKIAPAY_PRIVATE'] = Configuration::get('KKIAPAY_PRIVATE');
        $helper->fields_value['KKIAPAY_COLOR'] = Configuration::get('KKIAPAY_COLOR');
        $helper->fields_value['KKIAPAY_CHECKBOX'] = Configuration::get('KKIAPAY_CHECKBOX');
        $helper->fields_value['KKIAPAY_POSITION'] = Configuration::get('KKIAPAY_POSITION');

		return $helper->generateForm(array($fields_form));
	}

	public function getConfigFieldsValues()
	{
		return array(
			'KKIAPAY_PUBLIC' => Tools::getValue('KKIAPAY_PUBLIC', Configuration::get('KKIAPAY_PUBLIC')),
			'KKIAPAY_SECRET' => Tools::getValue('KKIAPAY_SECRET', Configuration::get('KKIAPAY_SECRET')),
			'KKIAPAY_PRIVATE' => Tools::getValue('KKIAPAY_PRIVATE', Configuration::get('KKIAPAY_PRIVATE')),
			'KKIAPAY_COLOR' => Tools::getValue('KKIAPAY_COLOR', Configuration::get('KKIAPAY_COLOR')),
			'KKIAPAY_CHECKBOX' => Tools::getValue('KKIAPAY_CHECKBOX', Configuration::get('KKIAPAY_CHECKBOX')),
			'KKIAPAY_POSITION' => Tools::getValue('KKIAPAY_POSITION', Configuration::get('KKIAPAY_POSITION')),
		);
	}
}
