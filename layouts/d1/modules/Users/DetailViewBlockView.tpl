{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    <input type=hidden name="timeFormatOptions" data-value='{$DAY_STARTS}'/>
    <input type='hidden' name='pwd_regex' value= {ZEND_json::encode($PWD_REGEX)}/>
    {foreach key=BLOCK_LABEL_KEY item=FIELD_MODEL_LIST from=$RECORD_STRUCTURE}
        {if $BLOCK_LABEL_KEY neq 'LBL_CALENDAR_SETTINGS'}
            {assign var=BLOCK value=$BLOCK_LIST[$BLOCK_LABEL_KEY]}
            {if $BLOCK eq null}{continue}{/if}
            {include file=vtemplate_path($RECORD_STRUCTURE_MODEL->blockData[$BLOCK_LABEL_KEY]['template_name'], $MODULE_NAME) BLOCK=$BLOCK USER_MODEL=$USER_MODEL MODULE_NAME=$MODULE_NAME RECORD=$RECORD}
        {/if}
    {/foreach}
{/strip}