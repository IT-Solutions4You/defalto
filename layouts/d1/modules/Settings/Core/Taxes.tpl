{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}

<div class="px-4 pb-4">
    <div class="rounded bg-body" id="TaxesContainer">
        <div class="editViewHeader p-3 border-bottom">
            <h4 class="m-0">{vtranslate('LBL_TAXES', $QUALIFIED_MODULE)}</h4>
        </div>
        <div class="contents tabbable py-3">
            <table class="table table-borderless taxesTable">
                <thead>
                    <tr class="bg-body-secondary">
                        <th class="bg-body-secondary text-secondary">{vtranslate('LBL_STATUS', $QUALIFIED_MODULE)}</th>
                        <th class="bg-body-secondary text-secondary">{vtranslate('LBL_TAX_NAME', $QUALIFIED_MODULE)}</th>
                        <th class="bg-body-secondary text-secondary">{vtranslate('LBL_CALCULATION', $QUALIFIED_MODULE)}</th>
                        <th class="bg-body-secondary text-secondary">{vtranslate('LBL_TAX_VALUE', $QUALIFIED_MODULE)}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr data-tax_id="" class="border-bottom taxContainer taxClone hide">
                        <td>
                            <div class="d-flex align-items-center">
                                <label class="btn text-secondary">
                                    <input type="checkbox" class="taxStatus form-check-input">
                                </label>
                                <button type="button" class="taxEdit btn text-secondary ms-2" title="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}">
                                    <i class="fa fa-pencil"></i>
                                </button>
                                <button type="button" class="taxDelete btn text-secondary ms-2" title="{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}">
                                    <i class="fa fa-trash"></i>
                                </button>
                                <button type="button" class="taxUpdate btn text-secondary ms-2" title="{vtranslate('LBL_UPDATE_RECORD_TAXES', $QUALIFIED_MODULE)}">
                                    <i class="fa-solid fa-upload"></i>
                                </button>
                            </div>
                        </td>
                        <td class="taxLabel w-50"></td>
                        <td class="taxMethod"></td>
                        <td class="taxPercentage"></td>
                    </tr>
                    {foreach from=$TAX_RECORDS item=TAX_RECORD}
                        <tr data-tax_id="{$TAX_RECORD->getId()}" class="border-bottom taxContainer">
                            <td>
                                <div class="d-flex align-items-center">
                                    <label class="btn text-secondary">
                                        <input type="checkbox" class="taxStatus form-check-input" {if $TAX_RECORD->isActive()}checked="checked"{/if}>
                                    </label>
                                    <button type="button" class="taxEdit btn text-secondary ms-2" title="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}">
                                        <i class="fa fa-pencil"></i>
                                    </button>
                                    <button type="button" class="taxDelete btn text-secondary ms-2" title="{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                    <button type="button" class="taxUpdate btn text-secondary ms-2" title="{vtranslate('LBL_UPDATE_RECORD_TAXES', $QUALIFIED_MODULE)}">
                                        <i class="fa-solid fa-upload"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="taxLabel w-50">{$TAX_RECORD->getLabel()}</td>
                            <td class="taxMethod">{$TAX_RECORD->getTaxMethod()}</td>
                            <td class="taxPercentage">{$TAX_RECORD->getPercentage()}</td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    </div>
</div>