{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* Label rules learned from manual label corrections — My Settings (the
   selected own mailbox, HIDE_MAILBOX) and the admin AI settings (shared
   mailboxes). Params: LABEL_RULES, HIDE_MAILBOX *}
{strip}
{assign var=LABEL_LANG value='Settings:MailManager'}
<div class="its4you-label-rules"
     data-confirm="{vtranslate('LBL_DELETE_LABELRULE_CONFIRM', $LABEL_LANG)|escape}"
     data-deleted="{vtranslate('LBL_LABELRULE_DELETED', $LABEL_LANG)|escape}">
    <h5 class="its4you-label-rules-title">{vtranslate('LBL_LEARNED_RULES', $LABEL_LANG)}</h5>
    <div class="its4you-hint mb-2">{vtranslate('LBL_LEARNED_RULES_HINT', $LABEL_LANG)}</div>
    <table class="table table-bordered its4you-labels-table">
        <thead>
            <tr>
                <th class="its4you-w-60">{vtranslate('LBL_ACTIONS', $LABEL_LANG)}</th>
                <th class="its4you-w-150">{vtranslate('LBL_RULE_TYPE', $LABEL_LANG)}</th>
                <th>{vtranslate('LBL_RULE_PATTERN', $LABEL_LANG)}</th>
                <th class="its4you-w-140">{vtranslate('LBL_LABEL', $LABEL_LANG)}</th>
                {if !$HIDE_MAILBOX}
                    <th class="its4you-w-200">{vtranslate('LBL_MAILBOX', $LABEL_LANG)}</th>
                {/if}
                <th class="its4you-w-80 text-center">{vtranslate('LBL_RULE_APPLIED', $LABEL_LANG)}</th>
                <th class="its4you-w-150">{vtranslate('LBL_LAST_APPLIED', $LABEL_LANG)}</th>
            </tr>
        </thead>
        <tbody>
            {foreach item=LABEL_RULE from=$LABEL_RULES}
                <tr data-ruleid="{$LABEL_RULE.ruleid}">
                    <td>
                        <a href="javascript:void(0);" class="its4you-delete-labelrule" title="{vtranslate('LBL_DELETE', $LABEL_LANG)}">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </td>
                    <td>{vtranslate('LBL_RULE_TYPE_'|cat:strtoupper($LABEL_RULE.rule_type), $LABEL_LANG)}</td>
                    <td>
                        {if $LABEL_RULE.rule_type eq 'domain'}
                            @{$LABEL_RULE.domain|escape}
                        {else}
                            {$LABEL_RULE.from_email|escape}
                            {if $LABEL_RULE.rule_type eq 'subject'}
                                <div class="its4you-muted">&bdquo;{$LABEL_RULE.subject_key|escape}&ldquo;</div>
                            {/if}
                        {/if}
                    </td>
                    <td>
                        {if $LABEL_RULE.labelid eq 0}
                            <span class="its4you-muted">{vtranslate('LBL_RULE_NO_LABEL', $LABEL_LANG)}</span>
                        {else}
                            <span class="its4you-label-chip" style="background: {$LABEL_RULE.label_color|escape}; color: {Settings_MailManager_Label_Model::getTextColor($LABEL_RULE.label_color)};">{$LABEL_RULE.label_name|escape}</span>
                        {/if}
                    </td>
                    {if !$HIDE_MAILBOX}
                        <td class="its4you-muted">{$LABEL_RULE.mailbox_email|escape}</td>
                    {/if}
                    <td class="text-center">{$LABEL_RULE.applied_count}</td>
                    <td>{$LABEL_RULE.last_applied_display}</td>
                </tr>
            {foreachelse}
                <tr>
                    <td colspan="{if $HIDE_MAILBOX}6{else}7{/if}" class="text-center">{vtranslate('LBL_NO_LEARNED_RULES', $LABEL_LANG)}</td>
                </tr>
            {/foreach}
        </tbody>
    </table>
</div>
{/strip}
