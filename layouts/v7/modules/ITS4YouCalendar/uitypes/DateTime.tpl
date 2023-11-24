{**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}
{strip}
    {assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
    {assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
    {assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'))}
    {assign var=TIME_FORMAT value=$USER_MODEL->get('hour_format')}
    {assign var=DATE_FORMAT value=$USER_MODEL->get('date_format')}
    {if (!$FIELD_NAME)}
        {assign var=FIELD_NAME value=$FIELD_MODEL->getFieldName()}
    {/if}
    <div class="dateTimeField">
        <div class="input-group inputElement date marginRight15">
            <input type="text"
                   name="{$FIELD_NAME}_date"
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
                   name="{$FIELD_NAME}_time"
                   data-format="{$TIME_FORMAT}"
                   class="timepicker-default form-control "
                   value=""
                    {if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
                   data-rule-time="true"/>
            <span class="input-group-addon" style="width: 30px;">
                <i class="fa fa-clock-o"></i>
            </span>
        </div>
        <div class="datetime">
            <input id="{$MODULE}_editView_fieldName_{$FIELD_MODEL->get('name')}"
                   name="{$FIELD_NAME}"
                   type="hidden"
                   value="{$FIELD_VALUE}"/>
        </div>
    </div>
{/strip}