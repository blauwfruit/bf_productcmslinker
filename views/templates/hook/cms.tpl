{*
*   bf_productcmslinker
*
*   Do not copy, modify or distribute this document in any form.
*
*   @copyright  Copyright (c) 2013-2021 blauwfruit (http://www.blauwfruit.nl)
*   @license    Proprietary Software
*   @author     Matthijs <matthijs@blauwfruit.nl>
*
*}

{if $cms_pages|count}
  <div id="productcmslinker">
    <div class="container">
      <div class="row">
        <div class="col-md-12">
          <h2>{l s='Projects with this product' mod='bf_productcmslinker'}</h2>
        </div>
      </div>
     <div class="row">
         {foreach from=$cms_pages item=cms_page}
              <a href="{$cms_page.link}">
                  <div class="col-md-6">
                      <div class="card" {if $cms_page.image} style="background-image: url('{$cms_page.image}');"{/if}>
                          <div class="card-body">
                          </div>
                          <div class="card-title">
                            <h3>{$cms_page.meta_title}</h3>
                          </div>
                      </div>
                  </div>
              </a>
         {/foreach}
     </div>
    </div>
  </div>
{/if}