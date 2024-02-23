{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{* modules/Settings/Vtiger/views/TaxIndex.php *}
{strip}
    <div class="px-4 pb-4">
        <div class="rounded bg-body" id="TaxCalculationsContainer">
            <div class="editViewHeader p-3 border-bottom">
                <h4 class="m-0">{vtranslate('LBL_TAX_CALCULATIONS', $QUALIFIED_MODULE)}</h4>
            </div>
            <div class="contents tabbable pt-3">
                <ul class="nav nav-tabs massEditTabs border-bottom">
                    <li class="tab-item taxesTab active ms-3">
                        <a class="nav-link active" data-bs-toggle="tab" href="#taxes">
                            <strong>{vtranslate('LBL_TAXES', $QUALIFIED_MODULE)}</strong>
                        </a>
                    </li>
                    <li class="tab-item chargesTab ms-3">
                        <a class="nav-link" data-bs-toggle="tab" href="#charges">
                            <strong>{vtranslate('LBL_CHARGES_AND ITS_TAXES', $QUALIFIED_MODULE)}</strong>
                        </a>
                    </li>
                    <li class="tab-item taxRegionsTab ms-3">
                        <a class="nav-link" data-bs-toggle="tab" href="#taxRegions">
                            <strong>{vtranslate('LBL_TAX_REGIONS', $QUALIFIED_MODULE)}</strong>
                        </a>
                    </li>
                </ul>
                <div class="tab-content layoutContent pb-3 overflowVisible">
                    <div class="tab-pane active" id="taxes">
                        {assign var=CREATE_TAX_URL value=$TAX_RECORD_MODEL->getCreateTaxUrl()}
                        {assign var=WIDTHTYPE value=$CURRENT_USER_MODEL->get('rowheight')}
                        <div class="p-3">
                            <button type="button" class="btn btn-outline-secondary addTax addButton module-buttons" data-url="{$CREATE_TAX_URL}" data-type="0">
                                <i class="fa fa-plus"></i>
                                <span class="ms-2">{vtranslate('LBL_ADD_NEW_TAX', $QUALIFIED_MODULE)}</span>
                            </button>
                        </div>
                        <table class="table table-borderless inventoryTaxTable">
                            <thead>
                                <tr>
                                    <th class="bg-body-secondary {$WIDTHTYPE}"><strong>{vtranslate('LBL_TAX_NAME', $QUALIFIED_MODULE)}</strong></th>
                                    <th class="bg-body-secondary {$WIDTHTYPE}"><strong>{vtranslate('LBL_TYPE', $QUALIFIED_MODULE)}</strong></th>
                                    <th class="bg-body-secondary {$WIDTHTYPE}"><strong>{vtranslate('LBL_CALCULATION', $QUALIFIED_MODULE)}</strong></th>
                                    <th class="bg-body-secondary {$WIDTHTYPE}"><strong>{vtranslate('LBL_TAX_VALUE', $QUALIFIED_MODULE)}</strong></th>
                                    <th class="bg-body-secondary {$WIDTHTYPE}" colspan="2"><strong>{vtranslate('LBL_STATUS', $QUALIFIED_MODULE)}</strong></th>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach item=PRODUCT_SERVICE_TAX_MODEL from=$PRODUCT_AND_SERVICES_TAXES}
                                    <tr class="opacity border-bottom" data-taxid="{$PRODUCT_SERVICE_TAX_MODEL->get('taxid')}" data-taxtype="{$PRODUCT_SERVICE_TAX_MODEL->getType()}">
                                        <td class="{$WIDTHTYPE}">
                                            <span class="taxLabel" style="width:120px">{$PRODUCT_SERVICE_TAX_MODEL->getName()}</span>
                                        </td>
                                        <td class="{$WIDTHTYPE}">
                                            <span class="taxType">{$PRODUCT_SERVICE_TAX_MODEL->getTaxType()}</span>
                                        </td>
                                        <td class="{$WIDTHTYPE}">
                                            <span class="taxMethod">{$PRODUCT_SERVICE_TAX_MODEL->getTaxMethod()}</span>
                                        </td>
                                        <td class="{$WIDTHTYPE}">
                                            <span class="taxPercentage">{$PRODUCT_SERVICE_TAX_MODEL->getTax()}%</span>
                                        </td>
                                        <td class="{$WIDTHTYPE}">
                                            <input type="checkbox" class="editTaxStatus form-check-input" {if !$PRODUCT_SERVICE_TAX_MODEL->isDeleted()}checked{/if} />
                                        </td>
                                        <td class="{$WIDTHTYPE}">
                                            <div class="actions">
                                                <a class="editTax cursorPointer" data-url="{$PRODUCT_SERVICE_TAX_MODEL->getEditTaxUrl()}">
                                                    <i title="{vtranslate('LBL_EDIT', $MODULE)}" class="fa fa-pencil alignMiddle"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane" id="charges"></div>
                    <div class="tab-pane" id="taxRegions"></div>
                </div>
            </div>
        </div>
    </div>
{/strip}