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

$(document).ready(function () {

  if (!Array.isArray(allCmses) || !allCmses.length) {
    return;
  }

  var renameKey = function(arr) {
    $.each(arr, function(i, el) {
      el.value = el.meta_title;
      delete el.meta_title;
    });
  };

  renameKey(allCmses);

  var tagify = new Tagify($('input[name=bf_product_cms_linker]')[0], {
    delimiters: null,
    templates: {
      tag: function(tagData) {
        return `<tag title='${tagData.value}' contenteditable='false' spellcheck='false' class='tagify__tag ${tagData.class ? tagData.class : ""}' ${this.getAttributes(tagData)}>
                  <x title='remove tag' class='tagify__tag__removeBtn'></x>
                  <div>
                    <span class='tagify__tag-text'>${tagData.value}</span>
                  </div>
                </tag>`;
      },
      dropdownItem: function(tagData) {
        return `<div class='tagify__dropdown__item ${tagData.class ? tagData.class : ""}' tagifySuggestionIdx='${tagData.tagifySuggestionIdx}'>
                  <span>${tagData.value}</span>
                </div>`;
      }
    },
    enforceWhitelist: true,
    whitelist: allCmses,
    dropdown: {
      enabled: 0,
      classname: 'extra-properties'
    }
  });

  if (Array.isArray(productCmses) && productCmses.length) {
    renameKey(productCmses);
    tagify.addTags(productCmses);
  }

});