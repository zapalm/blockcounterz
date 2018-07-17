{**
* Block counters: module for PrestaShop 1.2-1.7
*
* @author    zapalm <zapalm@ya.ru>
* @copyright (c) 2010-2015, zapalm
* @link      http://prestashop.modulez.ru/en/frontend-features/43-block-counters.html The module's homepage
* @license   http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
*}

<!-- MODULE: blockcounterz -->
<div class="stat-counters-block-footer col-xs-12">
    {if $psVersion == 1.7}
        {$stat_counters nofilter}
    {else}
        {$stat_counters}
    {/if}
</div>
<!-- /MODULE: blockcounterz -->