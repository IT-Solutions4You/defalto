{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <input type=hidden name="timeFormatOptions" data-value='{$DAY_STARTS}'/>
    <input type='hidden' name='pwd_regex' value= {ZEND_json::encode($PWD_REGEX)}/>
    {foreach key=BLOCK_LABEL_KEY item=FIELD_MODEL_LIST from=$RECORD_STRUCTURE}
        {if $BLOCK_LABEL_KEY neq 'LBL_CALENDAR_SETTINGS'}
            {assign var=BLOCK value=$BLOCK_LIST[$BLOCK_LABEL_KEY]}
            {if $BLOCK eq null}{continue}{/if}
            {include file=vtemplate_path($BLOCK->getUITypeModel()->getDetailViewTemplateName(), $MODULE_NAME) BLOCK=$BLOCK USER_MODEL=$USER_MODEL MODULE_NAME=$MODULE_NAME RECORD=$RECORD}
        {/if}
    {/foreach}
{/strip}