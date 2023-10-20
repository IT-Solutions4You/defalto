{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/
-->*}

{strip}
    <div class="leadsFieldMappingEditPageDiv">
        <div class="pt-3 rounded bg-body">
            <div class="editViewContainer">
                <form id="leadsMapping" method="POST">
                    <div class="editViewBody ">
                        <div class="editViewContents table-container" >
                            <input type="hidden" id="restrictedFieldsList" value={ZEND_JSON::encode($RESTRICTED_FIELD_IDS_LIST)} />
                            <table class="table table-borderless" width="100%" id="convertLeadMapping">
                                <tbody>
                                    <tr>
                                        <th class="bg-body-secondary" width="7%"></th>
                                        <th class="bg-body-secondary" width="15%">{vtranslate('LBL_FIELD_LABEL', $QUALIFIED_MODULE)}</th>
                                        <th class="bg-body-secondary" width="15%">{vtranslate('LBL_FIELD_TYPE', $QUALIFIED_MODULE)}</th>
                                        <th class="bg-body-secondary" colspan="3" width="70%">{vtranslate('LBL_MAPPING_WITH_OTHER_MODULES', $QUALIFIED_MODULE)}</th>
                                    </tr>
                                    <tr>
                                        <th class="bg-body-secondary" width="7%">{vtranslate('LBL_ACTIONS', $QUALIFIED_MODULE)}</th>
                                        {foreach key=key item=LABEL from=$MODULE_MODEL->getHeaders()}
                                            <th class="bg-body-secondary" width="15%">{vtranslate($LABEL, $LABEL)}</th>
                                        {/foreach}
                                    </tr>

                                    {foreach key=MAPPING_ID item=MAPPING_ARRAY from=$MODULE_MODEL->getMapping()  name="mappingLoop"}
                                        <tr class="listViewEntries border-bottom" sequence-number="{$smarty.foreach.mappingLoop.iteration}">
                                            <td width="7%">
                                                {if $MAPPING_ARRAY['editable'] eq 1}
                                                    {foreach item=LINK_MODEL from=$MODULE_MODEL->getMappingLinks()}
                                                        <div class="table-actions">
                                                            <span class="actionImages">
                                                                <i title="{vtranslate($LINK_MODEL->getLabel(), $MODULE)}" class="fa fa-trash deleteMapping"></i>
                                                            </span>
                                                        </div>
                                                    {/foreach}
                                                {/if}
                                            </td>
                                            <td width="10%">
                                                <input type="hidden" name="mapping[{$smarty.foreach.mappingLoop.iteration}][mappingId]" value="{$MAPPING_ID}"/>
                                                <select class="leadsFields select2 col-sm-12" name="mapping[{$smarty.foreach.mappingLoop.iteration}][lead]" {if $MAPPING_ARRAY['editable'] eq 0} disabled {/if}>
                                                    {foreach key=FIELD_TYPE item=FIELDS_INFO from=$LEADS_MODULE_MODEL->getFields()}
                                                        {foreach key=FIELD_ID item=FIELD_OBJECT from=$FIELDS_INFO}
                                                            <option data-type="{$FIELD_TYPE}" {if $FIELD_ID eq $MAPPING_ARRAY['Leads']['id']} selected {/if} label="{vtranslate($FIELD_OBJECT->get('label'), $LEADS_MODULE_MODEL->getName())}" value="{$FIELD_ID}">
                                                                    {vtranslate($FIELD_OBJECT->get('label'), $LEADS_MODULE_MODEL->getName())}
                                                            </option>
                                                        {/foreach}
                                                    {/foreach}
                                                </select>
                                            </td>
                                            <td width="10%" class="selectedFieldDataType">{vtranslate($MAPPING_ARRAY['Leads']['fieldDataType'], $QUALIFIED_MODULE)}</td>
                                            <td width="10%">
                                                <select class="accountsFields select2 col-sm-12" name="mapping[{$smarty.foreach.mappingLoop.iteration}][account]" {if $MAPPING_ARRAY['editable'] eq 0} disabled {/if}>
                                                    <option data-type="{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}" value="0" label="{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}">{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}</option>
                                                        {foreach key=FIELD_TYPE item=FIELDS_INFO from=$ACCOUNTS_MODULE_MODEL->getFields()}
                                                            {foreach key=FIELD_ID item=FIELD_OBJECT from=$FIELDS_INFO}
                                                                {if $MAPPING_ARRAY['Leads']['fieldDataType'] eq $FIELD_TYPE}
                                                                    <option data-type="{$FIELD_TYPE}" {if $FIELD_ID eq $MAPPING_ARRAY['Accounts']['id']} selected {/if} label="{vtranslate($FIELD_OBJECT->get('label'), $ACCOUNTS_MODULE_MODEL->getName())}" value="{$FIELD_ID}">
                                                                            {vtranslate($FIELD_OBJECT->get('label'), $ACCOUNTS_MODULE_MODEL->getName())}
                                                                    </option>
                                                                {/if}
                                                            {/foreach}
                                                        {/foreach}
                                                </select>
                                            </td>
                                            <td width="10%">
                                                <select class="contactFields select2 col-sm-12" name="mapping[{$smarty.foreach.mappingLoop.iteration}][contact]" {if $MAPPING_ARRAY['editable'] eq 0} disabled {/if}>
                                                    <option data-type="{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}" value="0" label="{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}">{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}</option>
                                                    {foreach key=FIELD_TYPE item=FIELDS_INFO from=$CONTACTS_MODULE_MODEL->getFields()}
                                                        {foreach key=FIELD_ID item=FIELD_OBJECT from=$FIELDS_INFO}
                                                            {if $MAPPING_ARRAY['Leads']['fieldDataType'] eq $FIELD_TYPE}
                                                                <option data-type="{$FIELD_TYPE}" {if $FIELD_ID eq $MAPPING_ARRAY['Contacts']['id']} selected {/if} label="{vtranslate($FIELD_OBJECT->get('label'), $CONTACTS_MODULE_MODEL->getName())}" value="{$FIELD_ID}">
                                                                    {vtranslate($FIELD_OBJECT->get('label'), $CONTACTS_MODULE_MODEL->getName())}
                                                                </option>
                                                            {/if}
                                                        {/foreach}
                                                    {/foreach}
                                                </select>
                                            </td>
                                            <td width="10%">
                                                <select class="potentialFields select2 col-sm-12" name="mapping[{$smarty.foreach.mappingLoop.iteration}][potential]" {if $MAPPING_ARRAY['editable'] eq 0} disabled {/if}>
                                                    <option data-type="{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}" value="0" label="{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}">{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}</option>
                                                    {foreach key=FIELD_TYPE item=FIELDS_INFO from=$POTENTIALS_MODULE_MODEL->getFields()}
                                                        {foreach key=FIELD_ID item=FIELD_OBJECT from=$FIELDS_INFO}
                                                            {if $MAPPING_ARRAY['Leads']['fieldDataType'] eq $FIELD_TYPE}
                                                                <option data-type="{$FIELD_TYPE}" {if $FIELD_ID eq $MAPPING_ARRAY['Potentials']['id']} selected {/if} label="{vtranslate($FIELD_OBJECT->get('label'), $POTENTIALS_MODULE_MODEL->getName())}" value="{$FIELD_ID}">
                                                                    {vtranslate($FIELD_OBJECT->get('label'), $POTENTIALS_MODULE_MODEL->getName())}
                                                                </option>
                                                            {/if}
                                                        {/foreach}
                                                    {/foreach}
                                                </select>
                                            </td>
                                        </tr>
                                    {/foreach}
                                    <tr class="hide newMapping listViewEntries">
                                        <td width="5%">
                                            {foreach item=LINK_MODEL from=$MODULE_MODEL->getMappingLinks()}
                                                <div class="table-actions">
                                                    <span class="actionImages">
                                                        <i title="{vtranslate($LINK_MODEL->getLabel(), $MODULE)}" class="fa fa-trash deleteMapping"></i>
                                                    </span>
                                                </div>
                                            {/foreach}
                                        </td>
                                        <td width="10%">
                                            <select class="leadsFields newSelect col-sm-12">
                                                <option data-type="{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}" value="0" label="{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}">{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}</option>
                                                {foreach key=FIELD_TYPE item=FIELDS_INFO from=$LEADS_MODULE_MODEL->getFields()}
                                                    {foreach key=FIELD_ID item=FIELD_OBJECT from=$FIELDS_INFO}
                                                        <option data-type="{$FIELD_TYPE}" label="{vtranslate($FIELD_OBJECT->get('label'), $LEADS_MODULE_MODEL->getName())}" value="{$FIELD_ID}">
                                                                {vtranslate($FIELD_OBJECT->get('label'), $LEADS_MODULE_MODEL->getName())}
                                                        </option>
                                                    {/foreach}
                                                {/foreach}
                                            </select>
                                        </td>
                                        <td width="10%" class="selectedFieldDataType"></td>
                                        <td width="10%">
                                            <select class="accountsFields newSelect col-sm-12">
                                                <option data-type="{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}" label="{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}" value="0">{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}</option>
                                                {foreach key=FIELD_TYPE item=FIELDS_INFO from=$ACCOUNTS_MODULE_MODEL->getFields()}
                                                    {foreach key=FIELD_ID item=FIELD_OBJECT from=$FIELDS_INFO}
                                                        <option data-type="{$FIELD_TYPE}" label="{vtranslate($FIELD_OBJECT->get('label'), $ACCOUNTS_MODULE_MODEL->getName())}" value="{$FIELD_ID}">
                                                                {vtranslate($FIELD_OBJECT->get('label'), $ACCOUNTS_MODULE_MODEL->getName())}
                                                        </option>
                                                    {/foreach}
                                                {/foreach}
                                            </select>
                                        </td>
                                        <td width="10%">
                                            <select class="contactFields newSelect col-sm-12">
                                                <option data-type="{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}" label="{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}" value="0">{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}</option>
                                                {foreach key=FIELD_TYPE item=FIELDS_INFO from=$CONTACTS_MODULE_MODEL->getFields()}
                                                    {foreach key=FIELD_ID item=FIELD_OBJECT from=$FIELDS_INFO}
                                                        <option data-type="{$FIELD_TYPE}" label="{vtranslate($FIELD_OBJECT->get('label'), $CONTACTS_MODULE_MODEL->getName())}" value="{$FIELD_ID}">
                                                                {vtranslate($FIELD_OBJECT->get('label'), $CONTACTS_MODULE_MODEL->getName())}
                                                        </option>
                                                    {/foreach}
                                                {/foreach}
                                            </select>
                                        </td>
                                        <td width="10%">
                                            <select class="potentialFields newSelect col-sm-12">
                                                <option data-type="{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}" label="{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}" value="0">{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}</option>
                                                {foreach key=FIELD_TYPE item=FIELDS_INFO from=$POTENTIALS_MODULE_MODEL->getFields()}
                                                    {foreach key=FIELD_ID item=FIELD_OBJECT from=$FIELDS_INFO}
                                                        <option data-type="{$FIELD_TYPE}" label="{vtranslate($FIELD_OBJECT->get('label'), $POTENTIALS_MODULE_MODEL->getName())}" value="{$FIELD_ID}">
                                                                {vtranslate($FIELD_OBJECT->get('label'), $POTENTIALS_MODULE_MODEL->getName())}
                                                        </option>
                                                    {/foreach}
                                                {/foreach}
                                            </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="p-3">
                                <button id="addMapping" class="btn btn-outline-primary addButton module-buttons" type="button">
                                    <i class="fa fa-plus"></i>
                                    <span class="ms-2">{vtranslate('LBL_ADD_MAPPING', $QUALIFIED_MODULE)}</span>
                                </button>
                            </div>
						</div>
						<div class="modal-overlay-footer modal-footer border-top">
                            <div class="container-fluid py-3">
                                <div class="row">
                                    <div class="col text-end">
                                        <a class="btn btn-primary cancelLink" type="reset" href="{$MODULE_MODEL->getDetailViewUrl()}">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                                    </div>
                                    <div class="col">
                                        <button type="submit" class="btn btn-primary active saveButton" >{vtranslate('LBL_SAVE', $MODULE)}</button>
                                    </div>
                                </div>
                            </div>
						</div>
					</div>
				</form>
            </div>
		</div>
    </div>
{/strip}