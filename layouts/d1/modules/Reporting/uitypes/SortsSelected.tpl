{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
{strip}
    {assign var=VALUE_FIELD value=$FIELD_UITYPE->getFieldName($FIELD_VALUE)}
    {assign var=VALUE_ORDER value=$FIELD_UITYPE->getOrderType($FIELD_VALUE)}
    <div class="selectedSorts pe-grab"  data-field="{$VALUE_FIELD}" data-type="{$VALUE_ORDER}">
        <div class="row align-items-center py-2">
            <div class="col-sm-8 dropdown">
                <div class="input-group" title="{if 'DESC' eq $VALUE_ORDER}{vtranslate('LBL_DESC', $QUALIFIED_MODULE)}{else}{vtranslate('LBL_ASC', $QUALIFIED_MODULE)}{/if}">
                    <span class="input-group-text">
                        <span class="fieldValueOrder {if 'DESC' eq $VALUE_ORDER}visually-hidden{/if}" data-order="ASC">
                            <i class="fa-solid fa-arrow-up"></i>
                        </span>
                        <span class="fieldValueOrder {if 'ASC' eq $VALUE_ORDER}visually-hidden{/if}" data-order="DESC">
                            <i class="fa-solid fa-arrow-down"></i>
                        </span>
                    </span>
                    <input class="fieldLabel form-control pe-grab" type="text" value="{$LABEL_OPTIONS[$VALUE_FIELD]}">
                    <input class="fieldValue" type="hidden" name="sort_by[]" value="{$FIELD_VALUE}">
                    <span class="input-group-text dropdown-toggle" data-bs-toggle="dropdown">
                    </span>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a href="#" class="dropdown-item selectedSortDelete">
                                <i class="bi bi-x-lg"></i>
                                <span class="ms-2">{vtranslate('LBL_SORT_CLEAR', $QUALIFIED_MODULE)}</span>
                            </a>
                        </li>
                    </ul>
                    <span class="input-group-text">
                        <i class="bi bi-arrows-vertical"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
{/strip}