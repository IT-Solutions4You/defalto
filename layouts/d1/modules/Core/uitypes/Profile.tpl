{**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*}
{strip}
    {assign var=FIELD_NAME value=$FIELD_MODEL->get('name')}
    {assign var=FIELD_INFO value=Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($FIELD_MODEL->getFieldInfo()))}
    {assign var=PICKLIST_VALUES value=$FIELD_MODEL->getAllProfiles()}
    {assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
    <select class="select2" style="width: 90%;" name="{$FIELD_MODEL->getFieldName()}" {if $FIELD_MODEL->isMandatory() eq true}required="required"{/if} data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} >
        {foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
            <option value="{$PICKLIST_VALUE}" {if $FIELD_MODEL->get('fieldvalue') eq $PICKLIST_VALUE} selected {/if}>{vtranslate($PICKLIST_NAME, $MODULE)}</option>
        {/foreach}
    </select>
{/strip}
