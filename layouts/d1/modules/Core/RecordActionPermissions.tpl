{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {if !empty($RECORD_DENIED_ACTIONS) or !empty($RECORD_ACTION_RULES)}
        <div class="recordActionPermissions alert alert-warning mt-3 mb-0">
            <div class="fw-bold mb-1">
                <i class="fa-solid fa-lock me-2"></i>{vtranslate('LBL_RECORD_ACTION_RESTRICTIONS', 'Core')}
            </div>
            {if !empty($RECORD_DENIED_ACTIONS)}
                <div class="small fw-semibold">{vtranslate('LBL_RECORD_ACTION_RESTRICTED_FOR_YOU', 'Core')}</div>
                <ul class="mb-0 ps-3">
                    {foreach from=$RECORD_DENIED_ACTIONS item=DENIED_ACTION}
                        <li><span class="fw-semibold">{$DENIED_ACTION['label']|escape}</span>: {$DENIED_ACTION['reason']|escape}</li>
                    {/foreach}
                </ul>
            {/if}
            {if !empty($RECORD_ACTION_RULES)}
                <div class="d-flex align-items-center mt-2">
                    <div class="small fw-semibold">{vtranslate('LBL_RECORD_ACTION_ALL_RULES', 'Core')}</div>
                    {if $RECORD_ACTION_RULES|php7_count gt 1}
                        <form method="post" action="index.php?module={$RECORD->getModuleName()|escape:'url'}&action=RecordActionPermission" class="recordActionPermissionRemove ms-auto" data-confirm="{vtranslate('LBL_RECORD_ACTION_REMOVE_ALL_CONFIRM', 'Core')|escape}">
                            <input type="hidden" name="record" value="{$RECORD->getId()}"/>
                            <input type="hidden" name="remove_all" value="1"/>
                            <button type="submit" class="btn btn-sm text-reset fw-bold text-nowrap">
                                <i class="fa-solid fa-lock-open me-1"></i>{vtranslate('LBL_RECORD_ACTION_REMOVE_ALL', 'Core')}
                            </button>
                        </form>
                    {/if}
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-borderless align-middle mb-0 bg-transparent">
                        <thead>
                            <tr>
                                <th class="bg-transparent text-reset">{vtranslate('LBL_RECORD_ACTIONS', 'Core')}</th>
                                <th class="bg-transparent text-reset">{vtranslate('LBL_RECORD_ACTION_EFFECT', 'Core')}</th>
                                <th class="bg-transparent text-reset">{vtranslate('LBL_RECORD_ACTION_MEMBERS', 'Core')}</th>
                                <th class="bg-transparent text-reset">{vtranslate('LBL_RECORD_ACTION_REASON', 'Core')}</th>
                                <th class="bg-transparent text-reset">{vtranslate('LBL_RECORD_ACTION_SOURCE', 'Core')}</th>
                                <th class="bg-transparent text-reset"></th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach from=$RECORD_ACTION_RULES item=ACTION_RULE}
                                <tr>
                                    <td class="bg-transparent text-reset">{$ACTION_RULE['action_label']|escape}</td>
                                    <td class="bg-transparent text-reset">
                                        {if $ACTION_RULE['effect'] eq 'allow'}{vtranslate('LBL_RECORD_ACTION_ALLOW', 'Core')}{else}{vtranslate('LBL_RECORD_ACTION_DENY', 'Core')}{/if}
                                    </td>
                                    <td class="bg-transparent text-reset">{$ACTION_RULE['scope_label']|escape}</td>
                                    <td class="bg-transparent text-reset">{$ACTION_RULE['reason_label']|escape}</td>
                                    <td class="bg-transparent text-reset">{$ACTION_RULE['source_label']|escape}</td>
                                    <td class="bg-transparent text-reset text-end">
                                        <form method="post" action="index.php?module={$RECORD->getModuleName()|escape:'url'}&action=RecordActionPermission" class="recordActionPermissionRemove d-inline" data-confirm="{vtranslate('LBL_RECORD_ACTION_REMOVE_CONFIRM', 'Core')|escape}">
                                            <input type="hidden" name="record" value="{$RECORD->getId()}"/>
                                            <input type="hidden" name="rule_id" value="{$ACTION_RULE['id']}"/>
                                            <button type="submit" class="btn btn-sm text-reset fw-bold text-nowrap" title="{vtranslate('LBL_RECORD_ACTION_REMOVE', 'Core')}">
                                                <i class="fa-solid fa-lock-open me-1"></i>{vtranslate('LBL_RECORD_ACTION_REMOVE', 'Core')}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
            {/if}
        </div>
    {/if}
{/strip}
