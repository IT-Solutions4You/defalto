{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {$MODULE_SUMMARY}
    <div class="pt-3 mt-3 border-top">
        {assign var=FIELD_MODEL value=$RECORD->getField('createdtime')}
        {if $FIELD_MODEL}
            <div class="text-end text-secondary small">
                <span>{vtranslate($FIELD_MODEL->get('label'), $QUALIFIED_MODULE)}:</span>
                <span class="ms-2">{$RECORD->getDisplayValue($FIELD_MODEL->get('name'))}</span>
            </div>
        {/if}
        {assign var=FIELD_MODEL value=$RECORD->getField('modifiedtime')}
        {if $FIELD_MODEL}
            <div class="text-end text-secondary small">
                <span>{vtranslate($FIELD_MODEL->get('label'), $QUALIFIED_MODULE)}:</span>
                <span class="ms-2">{$RECORD->getDisplayValue($FIELD_MODEL->get('name'))}</span>
            </div>
        {/if}
    </div>
{/strip}
