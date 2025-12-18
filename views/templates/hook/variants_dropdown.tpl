<div class="product-variants-selector mb-3">
  <label for="variant-select" class="form-label">
    {l s='Choose an other variant:' mod='packvariants'}
  </label>
  <select id="variant-select" name="variant" class="form-control" onchange="onVariantSelectChange(this)">
    <option value=""></option>
    {foreach from=$product_variants item=variant}
      <option value="{$variant.url}">{$variant.name}</option>
    {/foreach}
  </select>
</div>
