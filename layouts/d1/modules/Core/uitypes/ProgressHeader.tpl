{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}

{strip}
    {assign var=PROGRESS_VALUE value=$FIELD_MODEL->get('fieldvalue')}
    {assign var=PROGRESS_DISPLAY_VALUE value=$FIELD_MODEL->getUITypeModel()->getHeaderDisplayValue($PROGRESS_VALUE, $RECORD->getId(), $RECORD)}
    <span class="core-header-progress d-flex align-items-center gap-2 h-100 {if $PROGRESS_VALUE === '' || $PROGRESS_VALUE === null}d-none{/if}" data-header-progress data-bs-toggle="tooltip" tabindex="0" title="{$PROGRESS_DISPLAY_VALUE|escape:'html'}">
        <progress class="core-field-progress" max="100" value="{$PROGRESS_VALUE|default:0|escape:'html'}" aria-label="{vtranslate($FIELD_MODEL->get('label'), $MODULE)|escape:'html'}">{$PROGRESS_DISPLAY_VALUE|escape:'html'}</progress>
        <span class="flex-shrink-0 text-nowrap" data-header-progress-label>{$PROGRESS_DISPLAY_VALUE|escape:'html'}</span>
    </span>
{/strip}
