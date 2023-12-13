<?php
/**
* 2007-2019 PrestaShop
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Kkiapay extends PaymentModule
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'kkiapay';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'kkiapay';
        $this->need_instance = 0;
	$this->module_key = 'eea9595b905b3279e130609bea3c0c9e';

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('kkiapay');
        $this->description = $this->l('kkiaPay permet aux entreprises de recevoir des paiements en toute sécurité via de l\'argent mobile, une carte de crédit ou un compte bancaire.');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->limited_currencies = array('XOF');
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {

        Configuration::updateValue('KKIAPAY_LIVE_MODE', false);

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('paymentOptions');
    }

    public function uninstall()
    {
        Configuration::deleteByName('KKIAPAY_LIVE_MODE');

        return parent::uninstall();
    }

    public function hookPaymentOptions($params)
    {
        
        $currency_id = $params['cart']->id_currency;
        $currency = new Currency((int)$currency_id);

        if (in_array($currency->iso_code, $this->limited_currencies ) == false ){
            return false;
    }
        
        $key = Configuration::get('KKIAPAY_PUBLIC');
        $color = Configuration::get('KKIAPAY_COLOR');
        $test = Configuration::get('KKIAPAY_CHECKBOX');
        $position= Configuration::get('KKIAPAY_POSITION');
        $private_key= Configuration::get('KKIAPAY_PRIVATE');
        $secret = Configuration::get('KKIAPAY_SECRET');
        $firstname_customer=($params['cookie']->customer_firstname);
        $lastname_customer=($params['cookie']->customer_lastname);
        $price= $params['cart']->getOrderTotal();
        //var_dump($this->module->currentOrder);

        $mylink = $this->context->link->getModuleLink('kkiapay', 'api');
        $mylink = $mylink.'?success=kkiapayojhsbbbes12345';
        //var_dump($mylink);
        
        $this->context->smarty->assign('firstname', $firstname_customer);
        $this->context->smarty->assign('lastname', $lastname_customer);
        $this->context->smarty->assign('api', $key);
        $this->context->smarty->assign('price', $price);
        $this->context->smarty->assign('color', $color);
        $this->context->smarty->assign('position', $position);
        $this->context->smarty->assign('url', $mylink);
        $this->context->smarty->assign('secret', $secret);
        $this->context->smarty->assign('test', $test);
        $this->context->smarty->assign('private', $private_key);

        $paymentForm = $this->fetch('module:kkiapay/views/templates/admin/hook/front/api/payment.tpl');

        $cardPaymentOption = new PaymentOption();

        $options= $cardPaymentOption->setCallToActionText('Payez par Mobile Money et par Carte Bancaire (Kkiapay)')
                          ->setAdditionalInformation($paymentForm)
                          ->setAction($this->context->link->getModuleLink($this->name, 'confirmation', array(), true))
                          ->setLogo("https://firebasestorage.googleapis.com/v0/b/love-kkiapay.appspot.com/o/kkiapay.svg?alt=media&token=236aa08e-e679-4df4-85a7-ecc354c72d86");
        
        return [$options];

    }


    public function getOptions()

    {
            $options = array (
   
            array (
   
                  'id_checkbox_options' => 1,
   
                  'checkbox_options_name' => '',
                  
                  'checked' => 'checked' )
               );
   
         return $options;
   
    }

    public function hookPaymentReturn($params) 
    {   

        // die();

        if (!$this->active) {
            return;
        }
        if (!isset($params['order']) || ($params['order']->module != $this->name)) {
            return false;
        }
        if (isset($params['order']) && Validate::isLoadedObject($params['order']) && isset($params['order']->valid)) {
            $this->smarty->assign(array(
                'id_order' => $params['order']->id,
                'valid' => $params['order']->valid,
                ));
        }
        if (isset($params['order']->reference) && !empty($params['order']->reference)) {
            $this->smarty->assign('reference', $params['order']->reference);
        }
        $this->smarty->assign(array(
            'shop_name' => $this->context->shop->name,
            'reference' => $params['order']->reference,
            'contact_url' => $this->context->link->getPageLink('contact', true)
            ));
        return $this->fetch('kkiapay/views/templates/admin/hook/front/api/payment_return.tpl'); 
    }

    public function hookActionValidateOrder($params){
        
        var_dump($params);
        die('');

    }


    public function getContent()
    {
        $output = null;
        // $myModuleName = strval(Tools::getValue('password'));
        // die($myModuleName);
        if (Tools::isSubmit('submit'.$this->name)) {
            $myModuleName = (string)Tools::getValue('KKIAPAY_PUBLIC');
            $myModulePrivate = (string)Tools::getValue('KKIAPAY_PRIVATE');
            $myModuleSecret = (string)Tools::getValue('KKIAPAY_SECRET');
            $myModuleColor = (string)Tools::getValue('KKIAPAY_COLOR');
            $myModuleCheckbox = (string)Tools::getValue('KKIAPAY_CHECKBOX');
            $myModulePosition = (string)Tools::getValue('KKIAPAY_POSITION');
            

            $v = (string)Tools::getValue('api');
            // var_dump($myModuleCheckbox);die();

            if (
                !$myModuleName ||
                empty($myModuleName) ||
                !Validate::isGenericName($myModuleName)
            ) {
                
                $output .= $this->displayError($this->l('Valeurs de configurations invalides'));
            } else {
                Configuration::updateValue('KKIAPAY_PUBLIC', $myModuleName);
                Configuration::updateValue('KKIAPAY_PRIVATE', $myModulePrivate);
                Configuration::updateValue('KKIAPAY_SECRET', $myModuleSecret);
                Configuration::updateValue('KKIAPAY_COLOR', $myModuleColor);
                Configuration::updateValue('KKIAPAY_CHECKBOX',$myModuleCheckbox );
                Configuration::updateValue('KKIAPAY_POSITION',$myModulePosition );
                $output .= $this->displayConfirmation($this->l('Paramètres mis à jour'));
            }
        }

        return $output.$this->displayForm();
    }


        public function displayForm()
    {
        $password = (string)Tools::getValue('password');
        // var_dump($password);die();
        // if($password) {
        // // only save if a value was entered
        // Configuration::updateValue('passwordKey', $password);
        // }
        $options_test = array(
            array(
              'id_option' => '1', 
              'name' => $this->l('Oui') 
            ),
            array(
              'id_option' => '0',
              'name' => $this->l('Non')
            )
        );

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

        // Get default language
        $this->hookDisplayHeader();
        $defaultLang = (int)Configuration::get('PS_LANG_DEFAULT');

        $fieldsForm=array();
        // Init Fields form array
        $fieldsForm[0]['form'] = [
            'legend' => [
                'title' => $this->l('Paramètres'),
            ],
            'input' => [                
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
                            'label' => $this->trans('Enabled', array(), 'Admin.Global'),
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => "0",
                            'label' => $this->trans('Disabled', array(), 'Admin.Global'),
                        )
                    ),
                )

                ,
                
                [
                    'col' => '6',
                    'type' => 'text',
                    'label' => $this->l('Clé publique'),
                    'name' => 'KKIAPAY_PUBLIC',
                    'class'=>'form-control',
                    'desc'=> $this->l('Obtenez vos clés d\'API sur le dashboard kkiapay dans la section Développeurs'),
                    'required' => true
                ],
                [
                    'col' => '6',
                    'type' => 'text',
                    'label' => $this->l('Clé privé'),
                    'name' => 'KKIAPAY_PRIVATE',
                    'class'=>'form-control',
                    'desc'=> $this->l('Obtenez vos clés d\'API sur le dashboard kkiapay dans la section Développeurs'),
                ],
                
                [
                    'col' => '6',
                    'type' => 'text',
                    'label' => $this->l('Secret'),
                    'name' => 'KKIAPAY_SECRET',
                    'size' => 20,
                    'class'=>'form-control',
                    'desc'=> $this->l('Obtenez vos clés d\'API sur le dashboard kkiapay dans la section Développeurs'),
                ]
                ,

                [
                    'col' => '6',
                    'type' => 'text',
                    'label' => $this->l('Couleur du widget kkiapay'),
                    'name' => 'KKIAPAY_COLOR',
                    'class'=>'form-control',
                    'desc'=> $this->l('Paramétrez la couleur de la fenêtre kkiapay. Pour une meilleure harmonisation, utilisez la couleur dominante de votre site ou laissez vide.'),
                ]
                ,

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

            ],
            'submit' => [
                'title' => $this->l('Sauvegarder'),
                'class' => 'btn btn-default pull-right'
            ]
        ];

        $helper = new HelperForm();
        // $helper->fields_value["KKIAPAY_CHECKBOX_1"] = true;
        // Module, token and currentIndex
        $helper->module = $this->module;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        // Language
        $helper->default_form_language = $defaultLang;
        $helper->allow_employee_form_lang = $defaultLang;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit'.$this->name;
        $helper->toolbar_btn = [
            'save' => [
                'desc' => $this->l('Sauvegarder'),
                'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                '&token='.Tools::getAdminTokenLite('AdminModules'),
            ],
            'back' => [
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Retour à la liste')
            ]
        ];

        // Load current value
        // die(Configuration::get('KKIAPAY_NAME'));
        // var_dump(Configuration::get('KKIAPAY_CHECKBOX_1'));
        // die();
        $myModulePosition=Configuration::get('KKIAPAY_POSITION');
        // var_dump($myModulePosition);
        // die();
        $helper->fields_value['KKIAPAY_PUBLIC'] = Configuration::get('KKIAPAY_PUBLIC');
        $helper->fields_value['KKIAPAY_SECRET'] = Configuration::get('KKIAPAY_SECRET');
        $helper->fields_value['KKIAPAY_PRIVATE'] = Configuration::get('KKIAPAY_PRIVATE');
        $helper->fields_value['KKIAPAY_COLOR'] = Configuration::get('KKIAPAY_COLOR');
        $helper->fields_value['KKIAPAY_CHECKBOX'] = Configuration::get('KKIAPAY_CHECKBOX');
        $helper->fields_value['KKIAPAY_POSITION'] = Configuration::get('KKIAPAY_POSITION');
        return $helper->generateForm($fieldsForm);
    }


    public function hookDisplayHeader()
    {
        // die();
        $this->context->controller->addCSS($this->_path.'views/css/button.css', 'all');
        $this->context->controller->addJS($this->_path.'views/css/app.js', 'all');
    }

   
}
