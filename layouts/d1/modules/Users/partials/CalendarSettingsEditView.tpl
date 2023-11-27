{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    {if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
        <input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
    {/if}
    <div name='editContent'>
        {foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE name=blockIterator}
            {if $BLOCK_FIELDS|@count gt 0}
                <div class="fieldBlockContainer">
                    <h4 class="fieldBlockHeader m-0 p-3 border-bottom" >{vtranslate($BLOCK_LABEL, $MODULE)}</h4>
                    <div class="container-fluid p-3">
                        <div class="row">
                            {foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}
                                {assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
                                {assign var="refrenceList" value=$FIELD_MODEL->getReferenceList()}
                                {assign var="refrenceListCount" value=php7_count($refrenceList)}
                                {if $FIELD_MODEL->isEditable() eq true}
                                    <div class="col-lg-6 py-2">
                                        <div class="row">
                                            <div class="col-sm-4 fieldLabel alignMiddle">
                                                {if $isReferenceField eq "reference"}
                                                    {if $refrenceListCount > 1}
                                                        <select style="width: 140px;" class="select2 referenceModulesList">
                                                            {foreach key=index item=value from=$refrenceList}
                                                                <option value="{$value}">{vtranslate($value, $value)}</option>
                                                            {/foreach}
                                                        </select>
                                                    {else}
                                                        {vtranslate($FIELD_MODEL->get('label'), $MODULE)}
                                                    {/if}
                                                {else}
                                                    {vtranslate($FIELD_MODEL->get('label'), $MODULE)}
                                                {/if}
                                                {if $FIELD_MODEL->isMandatory() eq true}<span class="text-danger ms-2">*</span>{/if}
                                            </div>
                                            <div class="col-sm fieldValue">
                                                {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
                                            </div>
                                        </div>
                                    </div>
                                {/if}
                            {/foreach}
                        </div>
                    </div>
                </div>
                <br>
            {/if}
        {/foreach}
    </div>
{/strip}