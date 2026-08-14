{**
 * This file is part of Defalto - a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="summaryWidgetEmptyState noContent border border-1 rounded text-center{if !empty($EMPTY_STATE_CLASS)} {$EMPTY_STATE_CLASS}{/if}">
        <p class="mb-0">
            {vtranslate($EMPTY_STATE_LABEL, $EMPTY_STATE_MODULE)}{if !empty($EMPTY_STATE_SUFFIX)} {$EMPTY_STATE_SUFFIX}{/if}
        </p>
    </div>
{/strip}
