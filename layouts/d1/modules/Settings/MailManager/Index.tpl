{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}

{strip}
<div class="px-4 pb-4 its4you-module-settings">
    <div class="bg-body rounded pb-3">
    <div class="editViewHeader container-fluid py-3 mb-3 border-bottom">
        <span class="fs-4 fw-bold"><i class="fa-solid fa-envelope me-2"></i> {vtranslate('MailManager', $QUALIFIED_MODULE)}</span>
    </div>

    <div class="marginBottom10px clearfix">
        <div class="float-end">
            <a class="btn btn-outline-secondary" href="index.php?module=MailManager&parent=Settings&view=AISettings">
                <i class="fa-solid fa-gear"></i>&nbsp;&nbsp;{vtranslate('LBL_SETTINGS', $QUALIFIED_MODULE)}
            </a>
            &nbsp;
            <a class="btn btn-primary" href="index.php?module=MailManager&parent=Settings&view=Edit">
                <i class="fa-solid fa-plus"></i>&nbsp;&nbsp;{vtranslate('LBL_ADD_MAILBOX', $QUALIFIED_MODULE)}
            </a>
        </div>
    </div>

    <table class="table table-bordered table-hover its4you-mailboxes-table">
        <thead>
            <tr>
                <th style="width: 90px;">{vtranslate('LBL_ACTIONS', $QUALIFIED_MODULE)}</th>
                <th>{vtranslate('LBL_MAILBOX_NAME', $QUALIFIED_MODULE)}</th>
                <th>{vtranslate('LBL_EMAIL_ADDRESS', $QUALIFIED_MODULE)}</th>
                <th>{vtranslate('LBL_PROVIDER', $QUALIFIED_MODULE)}</th>
                <th>{vtranslate('LBL_MAILBOX_TYPE', $QUALIFIED_MODULE)}</th>
                <th>{vtranslate('LBL_OWNER_ACCESS', $QUALIFIED_MODULE)}</th>
                <th>{vtranslate('LBL_STATUS', $QUALIFIED_MODULE)}</th>
                <th style="width: 70px;">{vtranslate('LBL_IS_DEFAULT', $QUALIFIED_MODULE)}</th>
            </tr>
        </thead>
        <tbody>
            {foreach item=MAILBOX from=$MAILBOXES}
                <tr>
                    <td>
                        <a href="index.php?module=MailManager&parent=Settings&view=Detail&record={$MAILBOX.mailboxid}"
                           title="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}">
                            <i class="fa-solid fa-pencil"></i>
                        </a>
                        &nbsp;&nbsp;
                        <a href="javascript:void(0);" class="its4you-delete-mailbox"
                           data-mailboxid="{$MAILBOX.mailboxid}" data-name="{$MAILBOX.name|escape}"
                           title="{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </td>
                    <td><a href="index.php?module=MailManager&parent=Settings&view=Detail&record={$MAILBOX.mailboxid}">{$MAILBOX.name|escape}</a></td>
                    <td>{$MAILBOX.email|escape}</td>
                    <td>
                        {if $MAILBOX.provider eq 'office365'}{vtranslate('LBL_PROVIDER_MS', $QUALIFIED_MODULE)}
                        {elseif $MAILBOX.provider eq 'google'}{vtranslate('LBL_PROVIDER_GOOGLE', $QUALIFIED_MODULE)}
                        {else}IMAP{if $MAILBOX.imap_server}&nbsp;<span class="its4you-muted">({$MAILBOX.imap_server|escape})</span>{/if}{/if}
                    </td>
                    <td>
                        {if $MAILBOX.mailbox_type eq 'shared'}
                            {vtranslate('LBL_TYPE_SHARED', $QUALIFIED_MODULE)}
                        {else}
                            {vtranslate('LBL_TYPE_PERSONAL', $QUALIFIED_MODULE)}
                        {/if}
                    </td>
                    {* whose it is — and for a shared one, who else works in it *}
                    <td>
                        {if $MAILBOX.mailbox_type eq 'shared'}
                            {if $MAILBOX.owner_name}<div><i class="fa-solid fa-user its4you-muted"></i>&nbsp;{$MAILBOX.owner_name|escape}</div>{/if}
                            {if $MAILBOX.member_names}<div><i class="fa-solid fa-users its4you-muted"></i>&nbsp;{$MAILBOX.member_names|escape}</div>{/if}
                            {if !$MAILBOX.owner_name && !$MAILBOX.member_names}&mdash;{/if}
                        {else}
                            {if $MAILBOX.owner_name}<i class="fa-solid fa-user its4you-muted"></i>&nbsp;{$MAILBOX.owner_name|escape}{else}&mdash;{/if}
                            {* deputies the owner shared it with — the admin can end the access *}
                            {foreach item=DEPUTY from=$MAILBOX.deputies}
                                <div class="its4you-deputy">
                                    <i class="fa-solid fa-share-nodes its4you-muted" title="{vtranslate('LBL_SHARING_DEPUTY', $QUALIFIED_MODULE)}"></i>&nbsp;{$DEPUTY.user_name|escape}
                                    {if $DEPUTY.valid_display}
                                        &nbsp;<span class="its4you-muted">({vtranslate('LBL_SHARING_UNTIL', $QUALIFIED_MODULE)}&nbsp;{$DEPUTY.valid_display}{if $DEPUTY.expired},&nbsp;{vtranslate('LBL_SHARING_EXPIRED', $QUALIFIED_MODULE)}{/if})</span>
                                    {/if}
                                    &nbsp;<a href="javascript:void(0);" class="its4you-revoke-deputy"
                                             data-mailboxid="{$MAILBOX.mailboxid}" data-userid="{$DEPUTY.userid}"
                                             data-name="{$DEPUTY.user_name|escape}"
                                             title="{vtranslate('LBL_SHARING_REVOKE', $QUALIFIED_MODULE)}"><i class="fa-solid fa-xmark"></i></a>
                                </div>
                            {/foreach}
                        {/if}
                    </td>
                    <td>
                        {if $MAILBOX.status eq 'active'}<span class="badge text-bg-success">{vtranslate('LBL_STATUS_ACTIVE', $QUALIFIED_MODULE)}</span>
                        {elseif $MAILBOX.status eq 'reauth_needed'}<span class="badge text-bg-warning">{vtranslate('LBL_STATUS_REAUTH', $QUALIFIED_MODULE)}</span>
                        {else}<span class="badge text-bg-secondary">{vtranslate('LBL_STATUS_DISABLED', $QUALIFIED_MODULE)}</span>{/if}
                    </td>
                    <td class="text-center">{if $MAILBOX.is_default eq 1}&#9679;{/if}</td>
                </tr>
            {foreachelse}
                <tr>
                    <td colspan="8" class="text-center">{vtranslate('LBL_NO_MAILBOXES', $QUALIFIED_MODULE)}</td>
                </tr>
            {/foreach}
        </tbody>
    </table>

    <div class="its4you-flash hide"
         {if $SUCCESS}data-success="{vtranslate($SUCCESS, $QUALIFIED_MODULE)}"{/if}
         data-delete-confirm="{vtranslate('LBL_DELETE_MAILBOX_CONFIRM', $QUALIFIED_MODULE)}"
         data-revoke-confirm="{vtranslate('LBL_SHARING_REVOKE_CONFIRM', $QUALIFIED_MODULE)|escape}"></div>
</div>
</div>
{/strip}
