{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* modules/Settings/SharingAccess/views/IndexAjax.php *}
{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
    <div class="ruleListContainer container-fluid">
        <div class="title row mb-3">
            <div class="rulehead col-sm fs-5 fw-bold">
                <!-- Check if the module should the for module to get the translations-->
                <span class="me-2">{vtranslate('LBL_SHARING_RULE', $QUALIFIED_MODULE)}</span>
                <span class="me-2">{vtranslate('LBL_FOR', $MODULE)}</span>
                {if $FOR_MODULE == 'Accounts'}
                    <span class="me-2">{vtranslate($FOR_MODULE, $QUALIFIED_MODULE)}</span>
                {else}
                    <span class="me-2">{vtranslate($FOR_MODULE, $MODULE)}</span>
                {/if}
            </div>
            <div class="col-sm-auto">
                <div class="btn-group">
                    <button class="btn btn-outline-secondary addButton addCustomRule" type="button" data-url="{$MODULE_MODEL->getCreateRuleUrl()}">
                        <i class="fa fa-plus"></i>
                        <span class="ms-2">{vtranslate('LBL_ADD_CUSTOM_RULE', $QUALIFIED_MODULE)}</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="contents">
            {if $RULE_MODEL_LIST}
                <table class="table table-borderless table-condensed customRuleTable">
                    <thead>
                    <tr class="customRuleHeaders">
                        <th class="text-secondary bg-body-secondary">{vtranslate('LBL_RULE_NO', $QUALIFIED_MODULE)}</th>
                        <!-- Check if the module should the for module to get the translations -->
                        <th class="text-secondary bg-body-secondary">
                            <span class="me-1">{if $FOR_MODULE == 'Accounts'}{vtranslate($FOR_MODULE, $QUALIFIED_MODULE)}{else}{vtranslate($FOR_MODULE, $MODULE)}{/if}</span>
                            <span>{vtranslate('LBL_OF', $MODULE)}</span>
                        </th>
                        <th class="text-secondary bg-body-secondary">{vtranslate('LBL_CAN_ACCESSED_BY', $QUALIFIED_MODULE)}</th>
                        <th class="text-secondary bg-body-secondary">{vtranslate('LBL_PRIVILEGES', $QUALIFIED_MODULE)}</th>
                        <th class="text-secondary bg-body-secondary"></th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach item=RULE_MODEL key=RULE_ID from=$RULE_MODEL_LIST name=customRuleIterator}
                        <tr class="customRuleEntries border-top border-bottom">
                            <td class="sequenceNumber">
                                {$smarty.foreach.customRuleIterator.index + 1}
                            </td>
                            <td>
                                <a href="{$RULE_MODEL->getSourceDetailViewUrl()}">{vtranslate('SINGLE_'|cat:$RULE_MODEL->getSourceMemberName(), $QUALIFIED_MODULE)}::{$RULE_MODEL->getSourceMember()->getName()}</a>
                            </td>
                            <td>
                                <a href="{$RULE_MODEL->getTargetDetailViewUrl()}">{vtranslate('SINGLE_'|cat:$RULE_MODEL->getTargetMemberName(), $QUALIFIED_MODULE)}::{$RULE_MODEL->getTargetMember()->getName()}</a>
                            </td>
                            <td>
                                {if $RULE_MODEL->isReadOnly()}
                                    {vtranslate('Read Only', $QUALIFIED_MODULE)}
                                {else}
                                    {vtranslate('Read Write', $QUALIFIED_MODULE)}
                                {/if}
                            </td>
                            <td class="table-actions text-end">
                                <a href="javascript:void(0);" class="edit btn text-secondary" data-url="{$RULE_MODEL->getEditViewUrl()}"><i title="{vtranslate('LBL_EDIT', $MODULE)}" class="fa fa-pencil"></i></a>
                                <a href="javascript:void(0);" class="delete btn text-secondary" data-url="{$RULE_MODEL->getDeleteActionUrl()}"><i title="{vtranslate('LBL_DELETE', $MODULE)}" class="fa fa-trash"></i></a>
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
                <div class="recordDetails border-top border-bottom p-4 hide">
                    <p class="text-center m-0">{vtranslate('LBL_CUSTOM_ACCESS_MESG', $QUALIFIED_MODULE)}.<!--<a href="">{vtranslate('LBL_CLICK_HERE', $QUALIFIED_MODULE)}</a>&nbsp;{vtranslate('LBL_CREATE_RULE_MESG', $QUALIFIED_MODULE)}--></p>
                </div>
            {else}
                <div class="recordDetails border-top border-bottom p-4">
                    <p class="text-center m-0">{vtranslate('LBL_CUSTOM_ACCESS_MESG', $QUALIFIED_MODULE)}.<!--<a href="">{vtranslate('LBL_CLICK_HERE', $QUALIFIED_MODULE)}</a>&nbsp;{vtranslate('LBL_CREATE_RULE_MESG', $QUALIFIED_MODULE)}--></p>
                </div>
            {/if}
        </div>
    </div>
{/strip}
