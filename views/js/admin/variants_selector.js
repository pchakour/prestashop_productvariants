function needToStringify(x) {
  return typeof x === 'object' && x !== null;
}

function kebabize(str) {
  return str.replace(/[A-Z]+(?![a-z])|[A-Z]/g, ($, ofs) => (ofs ? "-" : "") + $.toLowerCase());
}


document.addEventListener('DOMContentLoaded', () => {
  const wait = (callback) => {
    if (globalThis.prestashop?.component?.EntitySearchInput) {
      callback();
    } else {
      console.log('[PackVariants] Waiting for ProductSearchInput...');
      setTimeout(() => wait(callback), 300);
    }
  };

  wait(() => {
    const EntitySearchInput = globalThis.prestashop.component.EntitySearchInput;
    const $searchContainer = $('.js-variant-products');
    $searchContainer.append($('<h3>Variants</h3>'));
    const remoteUrl = $('#product_description_related_products').attr('data-remote-url');
    $searchContainer.attr('data-remote-url', remoteUrl);
    // $('.js-variant-products').append($searchInput);

    const $input = $('#product_description_related_products .search').clone();
    $('#product_description_related_products_search_input', $input).attr('id', 'product_description_variants_search_input');
    const $list = $('<ul id="product_description_variants_list" class="entities-list entities-list-container" />')
    $searchContainer.append($input);
    
    for (const [key, value] of Object.entries($('#product_description_related_products').data())) {
      let stringyfiedValue = value;
      if (needToStringify(value)) {
        stringyfiedValue = JSON.stringify(value);
      }

      $searchContainer.attr(`data-${kebabize(key)}`, stringyfiedValue);
    }

    $searchContainer.attr('data-prototype-template', `<li id="product_variants_products___entity_index__" class="variants entity-item">
    <div class="variants-image">
    <input type="hidden" id="product_variants_products___entity_index___image" name="product[description][variants][__entity_index__][image]" value="__image__" />
    <img src="__image__" alt="Image preview for image" class="img-fluid" />
    
    </div>
    <div class="variants-legend">
    <input type="hidden" id="product_variants_products___entity_index___name" name="product[description][variants][__entity_index__][name]" value="__name__" />
  <span class="label text-preview ">
            <span class="text-preview-prefix">
            <i class="material-icons entity-item-delete">delete</i>
    </span>
    
    <span class="text-preview-value">
    __name__
          </span>

          </span>

          </div>
    <input type="hidden" id="product_variants_products___entity_index___id" name="product[description][variants][__entity_index__][id]" value="__id__" />
  
  </li>`);
  
  const variants = $searchContainer.data('variants') || [];
    if (variants.length > 0) {
      const prototype = $searchContainer.data('prototype-template');
      variants.forEach((variant, index) => {
        const html = prototype
          .replace(/__entity_index__/g, index)
          .replace(/__image__/g, variant.image || '')
          .replace(/__name__/g, variant.name || '')
          .replace(/__id__/g, variant.id_product);

          console.log(html);
        $list.append(html);
      });
    }
    
  $searchContainer.append($list);
  const { eventEmitter } = window.prestashop.instance;
    new EntitySearchInput($searchContainer, {
      // remoteUrl,
      onRemovedContent: () => {
        eventEmitter.emit('updateSubmitButtonState');
      },
      onSelectedContent: () => {
        eventEmitter.emit('updateSubmitButtonState');
      },
    });
  });

});
