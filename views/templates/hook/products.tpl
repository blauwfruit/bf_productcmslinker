{if $products|count}
<div id="productcmslinker">
  <div class="row">
    <div class="col-md-12">
      <h2>{l s='Related products' mod='bf_productcmslinker'}</h2>
    </div>
  </div>
   <div class="row">
       {foreach from=$products item=product}
           {include file="catalog/_partials/miniatures/product.tpl" product=$product}
       {/foreach}
   </div>
</div>
{/if}
