{**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}
{strip}
    <div class="modal-dialog modelContainer modal-lg">
        <form id="EditEventType" name="EditEventType" method="post" action="index.php">
            <input type="hidden" name="event_type_record" value="{$EVENT_TYPE_RECORD->getId()}">
            <textarea class="hide EditEventTypeFields">{json_encode($EVENT_TYPE_ALL_FIELDS)}</textarea>
            <div class="modal-content">
                {include file='ModalHeader.tpl'|vtemplate_path:$MODULE TITLE=vtranslate('LBL_EDIT_EVENT_TYPE_TITLE', $QUALIFIED_MODULE)}
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row py-2">
                            <div class="col-lg-4 fieldLabel">
                                {vtranslate('LBL_MODULE', $QUALIFIED_MODULE)}
                            </div>
                            <div class="col-lg fieldValue">
                                <select name="event_type_module" id="event_type_module" class="select2 form-control inputElement" {if !$EVENT_TYPE_RECORD->isEmptyId()}disabled="disabled"{/if}>
                                    {foreach from=$EVENT_TYPE_ALL_MODULES item=EVENT_TYPE_ALL_MODULE}
                                        <option value="{$EVENT_TYPE_ALL_MODULE}" {if $EVENT_TYPE_ALL_MODULE eq $EVENT_TYPE_RECORD->getModule()}selected="selected"{/if}>{vtranslate($EVENT_TYPE_ALL_MODULE, $EVENT_TYPE_ALL_MODULE)}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-lg-4 fieldLabel">
                                {vtranslate('LBL_FIELD', $QUALIFIED_MODULE)}
                            </div>
                            <div class="col-lg fieldValue">
                                <select name="event_type_field" id="event_type_module" class="select2 form-control inputElement" {if !$EVENT_TYPE_RECORD->isEmptyId()}disabled="disabled"{/if}>
                                    {foreach from=$EVENT_TYPE_FIELDS key=EVENT_TYPE_FIELD_KEY item=EVENT_TYPE_FIELD_LABEL}
                                        <option value="{$EVENT_TYPE_FIELD_KEY}" {if $EVENT_TYPE_FIELD_KEY eq $EVENT_TYPE_RECORD->get('field')}selected="selected"{/if}>{$EVENT_TYPE_FIELD_LABEL}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-lg-4 fieldLabel">
                                {vtranslate('LBL_RANGE_FIELD', $QUALIFIED_MODULE)}
                            </div>
                            <div class="col-lg fieldValue">
                                <select name="event_type_range_field" id="event_type_module" class="select2 form-control inputElement" {if !$EVENT_TYPE_RECORD->isEmptyId()}disabled="disabled"{/if}>
                                    <option value="">{vtranslate('LBL_ONE_DAY_EVENT', $QUALIFIED_MODULE)}</option>
                                    {foreach from=$EVENT_TYPE_FIELDS key=EVENT_TYPE_FIELD_KEY item=EVENT_TYPE_FIELD_LABEL}
                                        <option value="{$EVENT_TYPE_FIELD_KEY}" {if $EVENT_TYPE_FIELD_KEY eq $EVENT_TYPE_RECORD->get('range_field')}selected="selected"{/if}>{$EVENT_TYPE_FIELD_LABEL}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="col-lg-4 fieldLabel">
                                {vtranslate('LBL_COLOR', $QUALIFIED_MODULE)}
                            </div>
                            <div class="col-lg fieldValue">
                                <input type="color" name="event_type_color" id="event_type_color" class="form-control form-control-color" value="{$EVENT_TYPE_RECORD->get('color')}" required>
                            </div>
                        </div>
                    </div>
                </div>
                {include file="ModalFooter.tpl"|vtemplate_path:$MODULE}
            </div>
        </form>
    </div>
{/strip}