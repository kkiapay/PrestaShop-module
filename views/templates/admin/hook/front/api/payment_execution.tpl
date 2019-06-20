{extends "$layout"}
{block name="content"}
    <section id="content-hook_payment_return" class="card definition-list">
      <div class="card-block">
        <div class="row">
          <div class="col-md-12">
            	<p><h3>{l s='Reçu de paiement' mod='kkiapay'}</h3>
        <br />- {l s='Numéro de commande' mod='kkiapay'} : <span class="price"><strong>{$num_cart|escape:'htmlall':'UTF-8'}</strong></span>
		<br /><br />- {l s='Montant à payer' mod='kkiapay'} : <span class="price"><strong>CFA{$price|escape:'htmlall':'UTF-8'}</strong></span>
		<br /><br />- {l s='Moyen de paiement' mod='kkiapay'} : <span class="price"><strong>{$moyen_payment|escape:'htmlall':'UTF-8'}</strong></span>
        <br /><br />- {l s='Date' mod='kkiapay'} : <span class="price"><strong>{$date|escape:'htmlall':'UTF-8'}</strong></span>
    	</p>

        <div id="payment-confirmation">
            <div class="ps-shown-by-js">
                <button class="btn btn-primary center-block kkiapay-button-prestashop">
                    {l s='Confirmer' mod='kkiapay'}
                </button>
                </div>
                <div class="ps-hidden-by-js">
          </div>
        </div>

          </div>
        </div>
      </div>
    </section>


  <script src="https://cdn.kkiapay.me/k.js"></script>
  <script type="text/javascript">


        var firstname="{$firstname|escape:'htmlall':'UTF-8'}"
        var lastname ="{$lastname|escape:'htmlall':'UTF-8'}"
        var price ="{$price|escape:'htmlall':'UTF-8'}"
        var api ="{$api|escape:'htmlall':'UTF-8'}"
        var color ="{$color|escape:'htmlall':'UTF-8'}"
        var position = "{$position|escape:'htmlall':'UTF-8'}"
        var url = "{$url|escape:'htmlall':'UTF-8'}"
        var test ="{$test|escape:'htmlall':'UTF-8'}"
        var bool = null
        console.log(api);
        if (test==1){
             bool="true";   
        }else{
            bool="false";
        }
        {literal}
        let button=document.querySelector('.kkiapay-button-prestashop')
        button.addEventListener('click',function(event){
            //console.log(url);
             event.preventDefault()
            openKkiapayWidget({amount:price, sandbox:bool, name:firstname+ ' ' +lastname,position,callback:url,theme:color,key:api})
           
        })
    {/literal}
  </script>
      
{/block}
