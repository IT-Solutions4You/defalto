{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
<form class="form-horizontal modal-content" name="massMerge" method="post" action="index.php">
    {assign var=TITLE value="{{vtranslate('LBL_MERGE_RECORDS_IN', $MODULE)}|cat:' > '|cat:{vtranslate($MODULE,$MODULE)}}"}
    {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$TITLE}
    <div class="overlayBody modal-body overflow-auto">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <input type="hidden" name=module value="{$MODULE}" />
                    <input type="hidden" name="action" value="ProcessDuplicates" />
                    <input type="hidden" name="records" value={Zend_Json::encode($RECORDS)} />
                    <div class="well well-sm my-3">
                        {vtranslate('LBL_MERGE_RECORDS_DESCRIPTION',$MODULE)}
                    </div>
                    <div class="datacontent">
                        <table class="table table-bordered">
                            <thead class='listViewHeaders'>
                            <th>
                                {vtranslate('LBL_FIELDS', $MODULE)}
                            </th>
                            {foreach item=RECORD from=$RECORDMODELS name=recordList}
                                <th>
                                    <div class="checkbox">
                                        <label>
                                            <input {if $smarty.foreach.recordList.index eq 0}checked{/if} type=radio value="{$RECORD->getId()}" name="primaryRecord"/>
                                            <span class="mx-2">{vtranslate('LBL_RECORD')}</span>
                                            <a href="{$RECORD->getDetailViewUrl()}" target="_blank" class="text-primary">#{$RECORD->getId()}</a>
                                        </label>
                                    </div>
                                </th>
                            {/foreach}
                            </thead>
                            {foreach item=FIELD from=$FIELDS}
                                {if $FIELD->isEditable()}
                                    <tr>
                                        <td>
                                            {vtranslate($FIELD->get('label'), $MODULE)}
                                        </td>
                                        {foreach item=RECORD from=$RECORDMODELS name=recordList}
                                            <td>
                                                <div class="checkbox">
                                                    <label>
                                                        <input {if $smarty.foreach.recordList.index eq 0}checked="checked"{/if} type=radio name="{$FIELD->getName()}" data-id="{$RECORD->getId()}" value="{$RECORD->get($FIELD->getName())}"/>
                                                        <span class="ms-2">{$RECORD->getDisplayValue($FIELD->getName())}</span>
                                                    </label>
                                                </div>
                                            </td>
                                        {/foreach}
                                    </tr>
                                {/if}
                            {/foreach}
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {assign var=BUTTON_NAME value=vtranslate('LBL_MERGE',$MODULE)}
    {include file="ModalFooter.tpl"|vtemplate_path:$MODULE}
</form>
