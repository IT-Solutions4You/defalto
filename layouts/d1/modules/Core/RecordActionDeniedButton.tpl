{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="d-inline-block me-2" id="{$CONTAINER_ID|escape}" title="{$DENY_REASON|escape}" data-bs-toggle="tooltip">
        <button type="button" class="btn btn-outline-secondary" disabled="disabled">
            <i class="fa-solid fa-lock me-2"></i>{$BUTTON_LABEL|escape}
        </button>
    </div>
{/strip}
