{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* Email labels table — shared by the admin AI settings and My Settings.
   Params: LABELS (label rows), EDITABLE (add / edit / delete for the
   LabelEditor.js), LABEL_STRINGS_JSON (editor messages, EDITABLE only) *}
{strip}
{assign var=LABEL_LANG value='Settings:MailManager'}
<div class="its4you-label-editor" {if $EDITABLE}data-strings="{$LABEL_STRINGS_JSON|escape}"{/if}>
    {if $EDITABLE}
        <div class="mb-3 clearfix">
            <div class="float-end">
                <button type="button" class="btn btn-outline-secondary its4you-add-label">
                    <i class="fa-solid fa-plus"></i>&nbsp;&nbsp;{vtranslate('LBL_ADD_LABEL', $LABEL_LANG)}
                </button>
            </div>
        </div>
    {/if}
    <table class="table table-bordered its4you-labels-table">
        <thead>
            <tr>
                {if $EDITABLE}<th class="its4you-w-80">{vtranslate('LBL_ACTIONS', $LABEL_LANG)}</th>{/if}
                <th class="its4you-w-140">{vtranslate('LBL_LABEL', $LABEL_LANG)}</th>
                <th>{vtranslate('LBL_AI_DESCRIPTION', $LABEL_LANG)}</th>
                <th class="its4you-w-160">{vtranslate('LBL_SUGGESTED_ACTION', $LABEL_LANG)}</th>
                <th class="its4you-w-90 text-center">{vtranslate('LBL_WORKSPACE', $LABEL_LANG)}</th>
                <th class="its4you-w-60 text-center">{vtranslate('LBL_ACTIVE', $LABEL_LANG)}</th>
            </tr>
        </thead>
        <tbody>
            {foreach item=AI_LABEL from=$LABELS}
                <tr data-labelid="{$AI_LABEL.labelid}"
                    data-name="{$AI_LABEL.name|escape}"
                    data-color="{$AI_LABEL.color|escape}"
                    data-description="{$AI_LABEL.ai_description|escape}"
                    data-module="{$AI_LABEL.suggested_module|escape}"
                    data-active="{$AI_LABEL.active}"
                    data-workspace="{$AI_LABEL.workspace}"
                    data-auto="{$AI_LABEL.auto_action}">
                    {if $EDITABLE}
                        <td>
                            <a href="javascript:void(0);" class="its4you-edit-label" title="{vtranslate('LBL_EDIT', $LABEL_LANG)}">
                                <i class="fa-solid fa-pencil"></i>
                            </a>
                            &nbsp;&nbsp;
                            <a href="javascript:void(0);" class="its4you-delete-label" title="{vtranslate('LBL_DELETE', $LABEL_LANG)}">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    {/if}
                    <td><span class="its4you-label-chip" style="background: {$AI_LABEL.color|escape}; color: {Settings_MailManager_Label_Model::getTextColor($AI_LABEL.color)};">{$AI_LABEL.name|escape}</span></td>
                    <td class="its4you-muted">{$AI_LABEL.ai_description|escape}</td>
                    <td>
                        {if $AI_LABEL.suggested_module}{vtranslate("LBL_ACTION_"|cat:($AI_LABEL.suggested_module|replace:':':'_'), $LABEL_LANG)}{else}&mdash;{/if}
                        {if $AI_LABEL.auto_action eq 1}&nbsp;<i class="fa-solid fa-bolt its4you-active-check" title="{vtranslate('LBL_AUTO_ACTION', $LABEL_LANG)}"></i>{/if}
                    </td>
                    <td class="text-center">{if $AI_LABEL.workspace eq 1}<i class="fa-solid fa-briefcase its4you-active-check"></i>{/if}</td>
                    <td class="text-center">{if $AI_LABEL.active eq 1}<i class="fa-solid fa-check its4you-active-check"></i>{/if}</td>
                </tr>
            {foreachelse}
                <tr class="its4you-no-labels">
                    <td colspan="{if $EDITABLE}6{else}5{/if}" class="text-center">{vtranslate('LBL_NO_LABELS', $LABEL_LANG)}</td>
                </tr>
            {/foreach}
        </tbody>
    </table>
</div>
{/strip}
