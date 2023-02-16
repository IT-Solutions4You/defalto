{*/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */*}
{strip}
    {assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
    {assign var=FIELD_NAME value=$FIELD_MODEL->get('name')}
    {assign var=REFERENCE_LIST value=$FIELD_MODEL->getReferenceList()}
    {assign var=REFERENCE_LIST_COUNT value=php7_count($REFERENCE_LIST)}
    {assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
    {assign var=UITYPE_MODEL value=$FIELD_MODEL->getUITypeModel()}
    <div class="referencefield-wrapper multi-reference-field">
        {if $REFERENCE_LIST_COUNT eq 1}
            <input name="popupReferenceModule" type="hidden" value="{$REFERENCE_LIST[0]}"/>
        {/if}
        {if {$REFERENCE_LIST_COUNT} gt 1}
            {assign var=DISPLAYID value=$FIELD_MODEL->get('fieldvalue')}
            {assign var=REFERENCED_MODULE_STRUCT value=$UITYPE_MODEL->getReferenceModule($DISPLAYID)}
            {if !empty($REFERENCED_MODULE_STRUCT)}
                {assign var=REFERENCED_MODULE_NAME value=$REFERENCED_MODULE_STRUCT->get('name')}
            {/if}
            {if in_array($REFERENCED_MODULE_NAME, $REFERENCE_LIST)}
                <input name="popupReferenceModule" type="hidden" value="{$REFERENCED_MODULE_NAME}"/>
            {else}
                <input name="popupReferenceModule" type="hidden" value="{$REFERENCE_LIST[0]}"/>
            {/if}
        {/if}
        {$UITYPE_MODEL->retrieveReference($FIELD_MODEL->get('fieldvalue'), $RECORD_ID)}
        <input name="{$FIELD_NAME}" type="hidden" value="{$UITYPE_MODEL->getReferenceIds()}" class="sourceField" data-displayvalue='{$UITYPE_MODEL->getReferenceData($FIELD_MODEL->get('fieldvalue'), $RECORD)}' data-fieldinfo='{json_encode($FIELD_INFO)}' data-multiple='true'/>
        <div class="input-group">
            <input id="{$FIELD_NAME}_display" name="{$FIELD_NAME}_display" data-fieldname="{$FIELD_NAME}" data-fieldtype="reference" type="text"
                   class="marginLeftZero inputElement select2" multiple="multiple"
                   value="{$FIELD_MODEL->getEditViewDisplayValue($displayId)}"
                   data-fieldinfo='{json_encode($FIELD_INFO)}' data-fieldtype="multireference" placeholder="{vtranslate('LBL_TYPE_SEARCH',$MODULE)}"
                    {if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
            />
            <span class="input-group-addon relatedPopup cursorPointer" title="{vtranslate('LBL_SELECT', $MODULE)}" style="height:auto;width: 30px;">
				<i id="{$MODULE}_editView_fieldName_{$FIELD_NAME}_select" class="fa fa-search"></i>
			</span>
            {if $smarty.request.view eq 'Edit'}
                <span class="input-group-addon createReferenceRecord cursorPointer clearfix" title="{vtranslate('LBL_CREATE', $MODULE)}">
                   <i id="{$MODULE}_editView_fieldName_{$FIELD_NAME}_create" class="fa fa-plus"></i>
                </span>
            {/if}
        </div>
    </div>
{/strip}
