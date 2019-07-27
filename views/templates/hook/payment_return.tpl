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

{if $status == 'ok'}
<h3 style="color: green">{l s='Votre commande a réussi.' mod='kkiapay'}</h3>
		<div class="box">
		<h4>{l s='Résumé de la commande' mod='kkiapay'}</h3>
		{if !isset($reference)}
			- {l s='Référence de la commande' mod='kkiapay'} : {$id_order|escape:'html'}
		{else}
			- {l s='Référence de la commande' mod='kkiapay'} : {$reference|escape:'html'}
		<br />- {l s='Total' mod='kkiapay'} : <span class="price"><strong>{$total_to_pay}</strong></span>
		{/if}
		</div>
{else}
	<p class="warning">
		{l s='Nous avions notifié une erreur sur votre commande' mod='kkiapay'} 
		<a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='L\'équipe de support' mod='kkiapay'}</a>.
	</p>
{/if}
