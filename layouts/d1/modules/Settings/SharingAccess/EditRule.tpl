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
    {assign var=RULE_MODEL_EXISTS value=true}
    {assign var=RULE_ID value=$RULE_MODEL->getId()}
    {if empty($RULE_ID)}
        {assign var=RULE_MODEL_EXISTS value=false}
    {/if}
    <div class="modal-dialog modelContainer">
        <div class="modal-content">
            {assign var=HEADER_TITLE value={vtranslate('LBL_ADD_CUSTOM_RULE_TO', $QUALIFIED_MODULE)}|cat:" "|cat:{vtranslate($MODULE_MODEL->get('name'), $MODULE)}}
            {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
            <form class="form-horizontal" id="editCustomRule" method="post">
                <input type="hidden" name="for_module" value="{$MODULE_MODEL->get('name')}"/>
                <input type="hidden" name="record" value="{$RULE_ID}"/>
                <div name="massEditContent">
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label fieldLabel mb-3">{vtranslate($MODULE_MODEL->get('name'), $MODULE)}&nbsp;{vtranslate('LBL_OF', $MODULE)}</label>
                            <div class="controls fieldValue mb-3">
                                <select class="select2" name="source_id">
                                    {foreach from=$ALL_RULE_MEMBERS key=GROUP_LABEL item=ALL_GROUP_MEMBERS}
                                        <optgroup label="{vtranslate($GROUP_LABEL, $QUALIFIED_MODULE)}">
                                            {foreach from=$ALL_GROUP_MEMBERS item=MEMBER}
                                                <option value="{$MEMBER->getId()}"
                                                        {if $RULE_MODEL_EXISTS} {if $RULE_MODEL->getSourceMember()->getId() == $MEMBER->getId()}selected{/if}{/if}>
                                                    {$MEMBER->getName()}
                                                </option>
                                            {/foreach}
                                        </optgroup>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label fieldLabel mb-3">{vtranslate('LBL_CAN_ACCESSED_BY', $QUALIFIED_MODULE)}</label>
                            <div class="controls fieldValue mb-3">
                                <select class="select2" name="target_id">
                                    {foreach from=$ALL_RULE_MEMBERS key=GROUP_LABEL item=ALL_GROUP_MEMBERS}
                                        <optgroup label="{vtranslate($GROUP_LABEL, $QUALIFIED_MODULE)}">
                                            {foreach from=$ALL_GROUP_MEMBERS item=MEMBER}
                                                <option value="{$MEMBER->getId()}"
                                                        {if $RULE_MODEL_EXISTS}{if $RULE_MODEL->getTargetMember()->getId() == $MEMBER->getId()}selected{/if}{/if}>
                                                    {$MEMBER->getName()}
                                                </option>
                                            {/foreach}
                                        </optgroup>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label fieldLabel mb-3">{vtranslate('LBL_WITH_PERMISSIONS', $QUALIFIED_MODULE)}</label>
                            <div class="controls fieldValue mb-3">
                                <label class="radio w-50">
                                    <input type="radio" value="0" name="permission" {if $RULE_MODEL_EXISTS} {if $RULE_MODEL->isReadOnly()} checked {/if} {else} checked {/if}/>
                                    <span class="mx-2">{vtranslate('LBL_READ', $QUALIFIED_MODULE)}</span>
                                </label>
                                <label class="radio w-50">
                                    <input type="radio" value="1" name="permission" {if $RULE_MODEL->isReadWrite()} checked {/if} />
                                    <span class="mx-2">{vtranslate('LBL_READ_WRITE', $QUALIFIED_MODULE)}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                {include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
            </form>
        </div>
    </div>
{/strip}
