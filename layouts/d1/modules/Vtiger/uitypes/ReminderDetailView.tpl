{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{assign var=REMINDER_VALUES value=$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD->getId())}
{if $REMINDER_VALUES eq ''}
    {vtranslate('LBL_NO', $MODULE)}
{else}
    {$REMINDER_VALUES}{vtranslate('LBL_BEFORE_EVENT', $MODULE)}
{/if}