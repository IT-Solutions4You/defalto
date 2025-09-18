{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* modules/Vtiger/views/MassActionAjax.php *}
{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
<div class="modal-dialog">
    <div class="modal-content">
        <form class="form-horizontal" id="findDuplicate">
            <input type='hidden' name='module' value='{$MODULE}' />
            <input type='hidden' name='view' value='FindDuplicates' />
            
            {assign var=HEADER_TITLE value={vtranslate('LBL_MATCH_CRITERIA', $MODULE)}}
            {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
            <div class="modal-body">
                <div class="py-2">
                    <label class="col-lg-12 control-label">{vtranslate('LBL_MATCH_FIELDS', $MODULE)}</label>
                </div>
                <div class="py-2">
                    <select id="fieldList" class="select2 form-control" multiple="true" name="fields[]"
                            data-rule-required="true">
                        {foreach from=$FIELDS item=FIELD}
                            {if $FIELD->isViewableInDetailView()}
                                <option value="{$FIELD->getName()}">{vtranslate($FIELD->get('label'), $MODULE)}</option>
                            {/if}
                        {/foreach}
                    </select>
                </div>
                <div class="py-2">
                    <div class="checkbox">
                        <label class="form-check form-switch form-switch-lg">
                            <input type="checkbox" checked="checked" name="ignoreEmpty" class="form-check-input"/>
                            <span class="form-check-label">{vtranslate('LBL_IGNORE_EMPTY_VALUES',$MODULE)}</span>
                        </label>
                    </div>
                </div>
            </div>
            {include file='ModalFooter.tpl'|@vtemplate_path:$MODULE BUTTON_NAME={vtranslate('LBL_FIND_DUPLICATES',$MODULE)}}
        </form>
    </div>
</div>
