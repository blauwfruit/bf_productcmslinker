/**
* 2007-2021 PrestaShop
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
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*
* Don't forget to prefix your containers with your own identifier
* to avoid any conflicts with others containers.
*/

document.addEventListener('DOMContentLoaded', function () {

  if (typeof allCmses === 'undefined' || allCmses.length === 0) {
    return
  }

  /**
   * Mapping meta_title to value in collection
   *
   * @param arr
   */
  const renameKey = (arr) => {
    arr.map(function (el) {
      el["value"] = el.meta_title
      delete(el["meta_title"])
    })
  }

  // tagify requires object.key to be 'value'
  renameKey(allCmses)

  var tagify = new Tagify(document.querySelector('input[name=bf_product_cms_linker]'), {
    delimiters : null,
    templates : {
      tag : function(tagData){
        try{
          return `<tag title='${tagData.value}' contenteditable='false' spellcheck="false" class='tagify__tag ${tagData.class ? tagData.class : ""}' ${this.getAttributes(tagData)}>
                        <x title='remove tag' class='tagify__tag__removeBtn'></x>
                        <div>
                            <span class='tagify__tag-text'>${tagData.value}</span>
                        </div>
                    </tag>`
        }
        catch(err){}
      },

      dropdownItem : function(tagData){
        try{
          return `<div class='tagify__dropdown__item ${tagData.class ? tagData.class : ""}' tagifySuggestionIdx="${tagData.tagifySuggestionIdx}">
                            <span>${tagData.value}</span>
                        </div>`
        }
        catch(err){}
      }
    },
    enforceWhitelist : true,
    whitelist : allCmses,
    dropdown : {
      enabled: 0, // suggest tags after a single character input
      classname : 'extra-properties' // custom class for the suggestions dropdown
    } // map tags' values to this property name, so this property will be the actual value and not the printed value on the screen
  })

  tagify.on('click', function(e){
    // console.log(e.detail);
  });

  tagify.on('remove', function(e){
    // console.log(e.detail);
  });

  tagify.on('add', function(e){
    // console.log( "original Input:", tagify.DOM.originalInput);
    // console.log( "original Input's value:", tagify.DOM.originalInput.value);
    // console.log( "event detail:", e.detail);
  });

  if (typeof productCmses !== 'undefined' && productCmses.length > 0) {
    renameKey(productCmses)
    tagify.addTags(productCmses)
  }

})
