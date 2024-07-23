{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}

<div class="px-4 pb-4">
    <div class="rounded bg-body" id="RegionsContainer">
        <div class="editViewHeader p-3 border-bottom">
            <h4 class="m-0">{vtranslate('LBL_TAXES_REGIONS', $QUALIFIED_MODULE)}</h4>
        </div>
        <div class="contents tabbable py-3">
            <table class="table table-borderless regionsTable">
                <thead>
                <tr class="bg-body-secondary">
                    <th class="bg-body-secondary text-secondary w-25"></th>
                    <th class="bg-body-secondary text-secondary">{vtranslate('LBL_NAME', $QUALIFIED_MODULE)}</th>
                </tr>
                </thead>
                <tbody>
                <tr data-region_id="" class="border-bottom regionContainer regionClone hide">
                    <td>
                        <div class="d-flex align-items-center">
                            <button type="button" class="regionEdit btn text-secondary ms-2">
                                <i class="fa fa-pencil"></i>
                            </button>
                            <button type="button" class="regionDelete btn text-secondary ms-2">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </td>
                    <td class="regionName"></td>
                </tr>
                {foreach from=$REGION_RECORDS item=REGION_RECORD}
                    <tr data-region_id="{$REGION_RECORD->getId()}" class="border-bottom regionContainer">
                        <td>
                            <div class="d-flex align-items-center">
                                <button type="button" class="regionEdit btn text-secondary ms-2">
                                    <i class="fa fa-pencil"></i>
                                </button>
                                <button type="button" class="regionDelete btn text-secondary ms-2">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </td>
                        <td class="regionName">{$REGION_RECORD->getName()}</td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>
</div>