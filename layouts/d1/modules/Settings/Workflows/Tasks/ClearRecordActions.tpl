{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="record-action-permission-task">
        <div class="row py-2">
            <div class="col-lg-2">{vtranslate('LBL_RECORD_ACTIONS', 'Core')}</div>
            <div class="col-lg-6">
                <select class="select2 inputElement" multiple="true" name="actions[]" data-placeholder="{vtranslate('LBL_RECORD_ACTION_ALL_ACTIONS', 'Core')}">
                    {foreach from=Core_RecordActionPermission_Model::getActions() key=ACTION item=ACTION_LABEL}
                        <option value="{$ACTION}" {if $TASK_OBJECT->isSelectedAction($ACTION)}selected="selected"{/if}>{vtranslate($ACTION_LABEL, 'Core')}</option>
                    {/foreach}
                </select>
                <div class="form-text">{vtranslate('LBL_RECORD_ACTION_CLEAR_INFO', 'Core')}</div>
            </div>
        </div>
    </div>
{/strip}
