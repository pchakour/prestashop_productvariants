<div class="product-variants-selector mb-3">
  {foreach from=$product_variants item=variant}
    {if $variant.size}
      <input onclick="onVariantSelectChange(this)" type="radio" name="product_size_variant" value="{$variant.url}" {if $variant.is_current}checked{/if}>
        <span class="size">x{$variant.size}</span>
      </input>
    {/if}
  {/foreach}
</div>
