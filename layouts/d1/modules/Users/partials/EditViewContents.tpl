{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is: vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
{strip}
    {if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
        <input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}'/>
    {/if}
    <div name="editContent">
        {foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE name=blockIterator}
            {if $BLOCK_LABEL neq 'LBL_CALENDAR_SETTINGS'}
                {if php7_count($BLOCK_FIELDS)}
                    <div class="fieldBlockContainer border-bottom" data-block="{$BLOCK_LABEL}">
                        <h4 class="fieldBlockHeader fw-bold py-3 px-4 m-0">{vtranslate($BLOCK_LABEL, $MODULE)}</h4>
                        <div class="container-fluid py-3 px-4">
                            <div class="row">
                                {foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}
                                    {assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
                                    {assign var="refrenceList" value=$FIELD_MODEL->getReferenceList()}
                                    {assign var="refrenceListCount" value=php7_count($refrenceList)}
                                    {if $FIELD_MODEL->getName() eq 'theme' or $FIELD_MODEL->getName() eq 'rowheight'}
                                        <input type="hidden" name="{$FIELD_MODEL->getName()}" value="{$FIELD_MODEL->get('fieldvalue')}"/>
                                        {continue}
                                    {/if}
                                    {if $FIELD_MODEL->isEditable() eq true}
                                        {assign var=IS_FULL_WIDTH value=$FIELD_MODEL->isTableFullWidth()}
                                        <div class="py-2 {if $IS_FULL_WIDTH}col-lg-12{else}col-lg-6{/if}">
                                            <div class="row">
                                                <div class="fieldLabel {if $IS_FULL_WIDTH}col-sm-2{else}col-sm-4{/if}">
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
                                                <div class="fieldValue {if $IS_FULL_WIDTH}col-sm-10{else}col-sm-8{/if}">
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
            {/if}
        {/foreach}
    </div>
{/strip}