{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{assign var=REMINDER_VALUES value=$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD->getId())}
{if $REMINDER_VALUES eq ''}
    {vtranslate('LBL_NO', $MODULE)}
{else}
    {$REMINDER_VALUES}{vtranslate('LBL_BEFORE_EVENT', $MODULE)}
{/if}