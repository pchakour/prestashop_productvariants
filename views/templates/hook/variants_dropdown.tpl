<div class="product-variants-selector mb-3">
  {foreach from=$product_variants item=variant}
    {if $variant.size}
      <a href="{$variant.url}"><span class="product_size_variant {if $variant.is_current}checked{/if}">{$variant.size} {l s='guests' mod='pc_packvariants'}</span></a>
    {/if}
  {/foreach}
</div>
