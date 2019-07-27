{*
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
*}

{capture name=path}
	<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}" title="{l s='Retournez à la vérification' mod='kkiapay'}">{l s='Vérification' mod='kkiapay'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='Payez avec kkiapay' mod='kkiapay'}
{/capture}

{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Récapitulatif de la commande' mod='kkiapay'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if isset($nbProducts) && $nbProducts <= 0}
	<p class="warning">{l s='Your shopping cart is empty.' mod='cheque'}</p>
{else}

<h3>{l s='Paiement par KKIAPAY' mod='kkiapay'}</h3>
<form>

    <p >
        <br />- {l s='Numéro de commande' mod='kkiapay'} : <span class="price"><strong>{$num_cart|escape:'htmlall':'UTF-8'}</strong></span>
		<br /><br />- {l s='Montant à payer' mod='kkiapay'} : <span class="price"><strong>{$price|escape:'htmlall':'UTF-8'} CFA</strong></span>
		<br /><br />- {l s='Moyen de paiement' mod='kkiapay'} : <span class="price"><strong>kkiapay</strong></span>
        <br /><br />- {l s='Date' mod='kkiapay'} : <span class="price"><strong>{$date|escape:'htmlall':'UTF-8'}</strong></span>
    </p>

<p class="cart_navigation" id="cart_navigation">
    <a class="exclusive_large kkiapay-button-prestashop">
        {l s='Je confirme ma commande' mod='kkiapay'}
    </a>
	<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html'}" class="button_large">{l s='Retournez en arrière' mod='kkiapay'}</a>
</p>
</form>
<script src="https://cdn.kkiapay.me/k.js"></script>


<script type="text/javascript">

	document.body.id = "module-cashondelivery-validation"
	document.body.className = document.body.className.replace("module-kkiapay-payment","module-cashondelivery-validation");
	document.body.className = document.body.className.replace("show-left-column","hide-left-column");

	console.log("okkkki");
	var firstname="{$firstname|escape:'htmlall':'UTF-8'}"
	var lastname ="{$lastname|escape:'htmlall':'UTF-8'}"
	var price ="{$price|escape:'htmlall':'UTF-8'}"
	var api ="{$api|escape:'htmlall':'UTF-8'}"
	var color ="{$color|escape:'htmlall':'UTF-8'}"
	var position = "{$position|escape:'htmlall':'UTF-8'}"
	var url = "{$link->getModuleLink('kkiapay', 'validation', [], true)|escape:'html'}"
	url = url+'?success=kkiapayojhsbbbes12345'
	var test ="{$test|escape:'htmlall':'UTF-8'}"
	var bool = null
	if (test==1){
	 bool="true";   
	}else{
		bool="false";
	}
	let button=document.querySelector('.kkiapay-button-prestashop')
	button.addEventListener('click',function(event){
		dict = {};
		dict['key'] = api;
		dict['sandbox'] = bool;
		dict['amount'] = (parseInt(price)+1).toString();
		dict['name'] = firstname+ ' ' +lastname;
		dict['position'] = position;
		dict['callback'] = url;
		dict['theme'] = color;
		event.preventDefault();
		openKkiapayWidget(dict)
	})
		
</script>

{/if}
