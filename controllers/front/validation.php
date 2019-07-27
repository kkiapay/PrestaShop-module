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

/**
 * @since 1.5.0
 */
class KkiapayValidationModuleFrontController extends ModuleFrontController
{
	/**
	 * @see FrontController::postProcess()
	 */
	public function postProcess()
	{
		//var_dump($this->context);
		$cart = $this->context->cart;
		if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active)
			Tools::redirect('index.php?controller=order&step=1');

		// Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
		$authorized = false;
		foreach (Module::getPaymentModules() as $module)
			if ($module['name'] == 'kkiapay')
			{
				$authorized = true;
				break;
			}
		if (!$authorized)
			die($this->module->l('Cette méthode de paiement n\'est pas authorisée.', 'validation'));

		$customer = new Customer($cart->id_customer);
		if (!Validate::isLoadedObject($customer))
			Tools::redirect('index.php?controller=order&step=1');

		$currency = $this->context->currency;
		$total = (float)$cart->getOrderTotal(true, Cart::BOTH);
		$mailVars = array(
			'{private_key}' => Configuration::get('KKIAPAY_PUBLIC'),
			'{public_key}' => Configuration::get('KKIAPAY_PRIVATE'),
			'{secret}' => Configuration::get('KKIAPAY_SECRET'),
			'{color}' => Configuration::get('KKIAPAY_COLOR'),
			'{checkbox}' => Configuration::get('KKIAPAY_CHECKBOX'),
			'{position}' => Configuration::get('KKIAPAY_POSITION')
		);
		
		        //Traitement de la réponse OK
         if ( Tools::getValue('transaction_id') and Tools::getValue('success') and Tools::getValue('success')=='kkiapayojhsbbbes12345' ) {
              $this->module->validateOrder((int)$cart->id, Configuration::get('PS_OS_PAYMENT'), $total, $this->module->displayName, null, mailVars, (int)$currency->id, false, $customer->secure_key);
              Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart->id.'&id_module='.$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key);
         } else {
             //Erreur
              $this->module->validateOrder((int)$cart->id, Configuration::get('PS_OS_ERROR'),0, $this->module->displayName, null, array(), (int)$currency->id, false, $customer->secure_key);
              Tools::redirect('index.php?controller=order&step=1');
         }

	}
}
