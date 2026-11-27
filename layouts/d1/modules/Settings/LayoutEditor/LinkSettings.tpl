{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}

{strip}
<div class="linkSettings pb-3" data-delete-confirmation="{vtranslate('LBL_LINK_DELETE_CONFIRMATION', 'Core')|escape:'html'}">
    <p class="text-secondary">{vtranslate('LBL_LINK_SETTINGS_HELP', 'Core')|escape:'html'}</p>
    <p class="linkSettingsAllEmpty text-secondary {if $HAS_MODULE_LINKS}d-none{/if}">{vtranslate('LBL_LINK_SETTINGS_EMPTY', 'Core')|escape:'html'}</p>
    {foreach item=SECTION from=$LINK_SECTIONS}
    <div class="linkSettingsSection mb-4">
        <h4 class="mb-3">{vtranslate($SECTION['label'], 'Core')|escape:'html'}</h4>
        <div class="row g-3">
        {foreach item=GROUP from=$SECTION['groups']}
        <section class="col-12 col-lg-4">
            <div class="border rounded p-3 h-100 linkSettingsGroup">
                <h5 class="mb-3">{vtranslate($GROUP['label'], 'Core')|escape:'html'}</h5>
                <div class="row g-3">
                    {foreach item=LINK from=$GROUP['links']}
                    <div class="moduleLinkItem col-12" data-link-id="{$LINK['linkid']|escape:'html'}">
                        <div class="border rounded p-3 h-100">
                            <div class="d-flex gap-2 justify-content-between align-items-start">
                                <div class="text-break">
                                    <div class="fw-semibold">{if $LINK['is_default']}{if $LINK['is_installed']}{assign var=DEFAULT_STATUS_LABEL value='LBL_LINK_DEFAULT_INSTALLED'}{else}{assign var=DEFAULT_STATUS_LABEL value='LBL_LINK_DEFAULT_MISSING'}{/if}<span class="{if $LINK['is_installed']}text-success{else}text-danger{/if} me-2" role="img" title="{vtranslate($DEFAULT_STATUS_LABEL, 'Core')|escape:'html'}" aria-label="{vtranslate($DEFAULT_STATUS_LABEL, 'Core')|escape:'html'}"><i class="fa-solid fa-check" aria-hidden="true"></i></span>{/if}{$LINK['title']|escape:'html'}</div>
                                </div>
                                {if $LINK['can_delete']}
                                <div class="dropdown">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown" aria-expanded="false" title="{vtranslate('LBL_ACTIONS', $QUALIFIED_MODULE)|escape:'html'}" aria-label="{vtranslate('LBL_ACTIONS', $QUALIFIED_MODULE)|escape:'html'}"><i class="fa-solid fa-ellipsis" aria-hidden="true"></i></button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><button type="button" class="dropdown-item text-danger deleteModuleLink">{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)|escape:'html'}</button></li>
                                    </ul>
                                </div>
                                {/if}
                            </div>
                            <details>
                                <summary class="text-secondary">{$LINK['title']|escape:'html'}</summary>
                                <dl class="small text-break mt-2 mb-0">
                                    {foreach item=DETAIL from=$LINK['details']}
                                        <dt>{$DETAIL['name']|escape:'html'}</dt>
                                        <dd>{if $DETAIL['icon_class']}<i class="{$DETAIL['icon_class']|escape:'html'}" aria-hidden="true"></i><span class="visually-hidden">{$DETAIL['name']|escape:'html'}</span>{else}{$DETAIL['value']|escape:'html'}{/if}</dd>
                                    {/foreach}
                                </dl>
                            </details>
                        </div>
                    </div>
                    {/foreach}
                </div>
                <p class="linkSettingsEmpty text-secondary mb-0 {if $GROUP['links']}d-none{/if}">{vtranslate('LBL_LINK_SETTINGS_EMPTY', 'Core')|escape:'html'}</p>
            </div>
        </section>
        {/foreach}
        </div>
        </div>
        {/foreach}
</div>
{/strip}
