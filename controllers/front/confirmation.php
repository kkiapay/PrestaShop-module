<?php
/**
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
 *  @author    Hennes Hervé <contact@h-hennes.fr>
 *  @copyright 2013-2016 Hennes Hervé
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  http://www.h-hennes.fr/blog/
 */

class kkiapayconfirmationModuleFrontController extends ModuleFrontController 
{
    

    /**
     * Retours de l'api de paiement
     */

	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{

		parent::initContent();

        //Vérification générales 
        $cart = $this->context->cart;

        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $customer =  new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $key = Configuration::get('KKIAPAY_PUBLIC');
        $color = Configuration::get('KKIAPAY_COLOR');
        $test = Configuration::get('KKIAPAY_CHECKBOX');
        $position= Configuration::get('KKIAPAY_POSITION');
        $private_key= Configuration::get('KKIAPAY_PRIVATE');
        $secret = Configuration::get('KKIAPAY_SECRET');
        $price= (float)$cart->getOrderTotal(true, Cart::BOTH);
        $firstname_customer=$customer->firstname;
        $lastname_customer=$customer->lastname;
        $email=$customer->email;
        $mylink = $this->context->link->getModuleLink('kkiapay','api');
        $mylink = $mylink.'?success=kkiapayojhsbbbes12345';

            $this->context->smarty->assign(array(
                  'back_url' => $this->context->link->getPageLink('order', true, NULL, "step=3"),
                  'confirm_url' => $this->context->link->getModuleLink('kkiapay', 'api', [], true),
                  'num_cart' => $cart->id,
                  'moyen_payment' => $this->module->displayName,
                  'date' => $cart->date_add

              ));

              $this->context->smarty->assign('firstname',$firstname_customer);
              $this->context->smarty->assign('lastname',$lastname_customer);
              $this->context->smarty->assign('api',$key);
              $this->context->smarty->assign('email',$email);
              $this->context->smarty->assign('price',$price);
              $this->context->smarty->assign('color',$color);
              $this->context->smarty->assign('position',$position);
              $this->context->smarty->assign('url',$mylink);
              $this->context->smarty->assign('secret',$secret);
              $this->context->smarty->assign('test',$test);
              $this->context->smarty->assign('private',$private_key);

              //var_dump($cart);

		$this->setTemplate('module:kkiapay/views/templates/admin/hook/front/api/payment_execution.tpl');
	}    
}
