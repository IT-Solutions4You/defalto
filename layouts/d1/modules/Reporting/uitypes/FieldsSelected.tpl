{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="selectedFields border me-1 pe-grab">
        <div class="d-flex flex-nowrap align-items-center p-2" data-bs-toggle="dropdown" aria-expanded="false">
            <input class="fieldValue" type="hidden" name="fields[]" value="{$FIELD_VALUE}">
            <span class="fieldLabel text-truncate pe-none">{$FIELD_DISPLAY_VALUE}</span>
            <button class="border-0 bg-transparent btn-outline-secondary dropdown-toggle ms-auto px-2 py-0" type="button"  ></button>
        </div>
        <ul class="dropdown-menu dropdown-menu-end mt-1">
            <li>
                <button type="button" class="sortSelected dropdown-item" data-field="{$FIELD_VALUE}" data-value="ASC">
                    <i class="bi bi-arrow-up"></i>
                    <span class="ms-2">{vtranslate('LBL_SORT_ASCENDING', $QUALIFIED_MODULE)}</span>
                </button>
                <button type="button" class="sortSelected dropdown-item" data-field="{$FIELD_VALUE}" data-value="DESC">
                    <i class="bi bi-arrow-down"></i>
                    <span class="ms-2">{vtranslate('LBL_SORT_DESCENDING', $QUALIFIED_MODULE)}</span>
                </button>
                <button type="button" class="sortSelected dropdown-item" data-field="{$FIELD_VALUE}" data-value="">
                    <i class="bi bi-x-lg"></i>
                    <span class="ms-2">{vtranslate('LBL_SORT_CLEAR', $QUALIFIED_MODULE)}</span>
                </button>
                <hr class="dropdown-divider">
                <button type="button" class="moveSelected dropdown-item" data-value="left">
                    <i class="bi bi-arrow-left"></i>
                    <span class="ms-2">{vtranslate('LBL_MOVE_LEFT', $QUALIFIED_MODULE)}</span>
                </button>
                <button type="button" class="moveSelected dropdown-item" data-value="right">
                    <i class="bi bi-arrow-right"></i>
                    <span class="ms-2">{vtranslate('LBL_MOVE_RIGHT', $QUALIFIED_MODULE)}</span>
                </button>
                <hr class="dropdown-divider">
                <button type="button" class="editLabelSelected dropdown-item">
                    <i class="fa-solid fa-text-width"></i>
                    <span class="ms-2">{vtranslate('LBL_EDIT_LABEL', $QUALIFIED_MODULE)}</span>
                </button>
                <button type="button" class="editFieldSelected dropdown-item">
                    <i class="fa-solid fa-pencil"></i>
                    <span class="ms-2">{vtranslate('LBL_EDIT_FIELD', $QUALIFIED_MODULE)}</span>
                </button>
                <button type="button" class="deleteSelected dropdown-item">
                    <i class="fa-solid fa-trash"></i>
                    <span class="ms-2">{vtranslate('LBL_REMOVE_COLUMN', $QUALIFIED_MODULE)}</span>
                </button>
            </li>
        </ul>
    </div>
{/strip}