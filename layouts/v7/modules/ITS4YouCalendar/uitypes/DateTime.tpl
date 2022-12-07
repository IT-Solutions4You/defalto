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
    {assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
    {assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
    {assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'), $BLOCK_FIELDS)}
    {assign var=TIME_FORMAT value=$USER_MODEL->get('hour_format')}
    {assign var=DATE_FORMAT value=$USER_MODEL->get('date_format')}
    {if (!$FIELD_NAME)}
        {assign var=FIELD_NAME value=$FIELD_MODEL->getFieldName()}
    {/if}
    <div class="dateTimeField">
        <div class="input-group inputElement date" style="margin-bottom: 3px">
            <input type="text"
                   class="dateField form-control {if $IGNOREUIREGISTRATION}ignore-ui-registration{/if}"
                   data-fieldtype="date"
                   data-date-format="{$DATE_FORMAT}"
                   value=""
                    {if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
                   data-rule-date="true"/>
            <span class="input-group-addon"><i class="fa fa-calendar "></i></span>
        </div>
        <div class="input-group inputElement time">
            <input type="text"
                   data-format="{$TIME_FORMAT}"
                   class="timepicker-default form-control "
                   value=""
                    {if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
                   data-rule-time="true"/>
            <span class="input-group-addon" style="width: 30px;">
                <i class="fa fa-clock-o"></i>
            </span>
        </div>
        <div class="input-group inputElement datetime">
            <input id="{$MODULE}_editView_fieldName_{$FIELD_MODEL->get('name')}"
                   name="{$FIELD_MODEL->getFieldName()}"
                   type="text"
                   value="{$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'))}"/>
        </div>
    </div>
{/strip}