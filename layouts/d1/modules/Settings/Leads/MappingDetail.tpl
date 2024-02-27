{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}

{strip}
    <div class="leadsFieldMappingListPageDiv px-4 pb-4">
        <div class="bg-body rounded">
            <div class="container-fluid p-3">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="m-0">{vtranslate('LBL_LEAD_MAPPING', $QUALIFIED_MODULE)}</h4>
                    </div>
                    <div class="col-auto">
                        <div class="settingsHeader">
                            {foreach item=LINK_MODEL from=$MODULE_MODEL->getDetailViewLinks()}
                                <button type="button" class="btn btn-outline-secondary" onclick={$LINK_MODEL->getUrl()}>{vtranslate($LINK_MODEL->getLabel(), $QUALIFIED_MODULE)}</button>
                            {/foreach}
                        </div>
                    </div>
                </div>
            </div>
            <div class="contents table-container" id="detailView">
                <table id="settings-listview-table" class="table table-borderless listview-table">
                    <thead>
                        <tr>
                            <th class="bg-body-secondary" width="5%"></th>
                            <th class="bg-body-secondary text-secondary" width="15%">{vtranslate('LBL_FIELD_LABEL', $QUALIFIED_MODULE)}</th>
                            <th class="bg-body-secondary text-secondary" width="15%">{vtranslate('LBL_FIELD_TYPE', $QUALIFIED_MODULE)}</th>
                            <th class="bg-body-secondary text-secondary" colspan="3" width="70%">{vtranslate('LBL_MAPPING_WITH_OTHER_MODULES', $QUALIFIED_MODULE)}</th>
                        </tr>
                        <tr>
                            <th class="bg-body-secondary text-secondary" width="5%">{vtranslate('LBL_ACTIONS', $QUALIFIED_MODULE)}</th>
                            {foreach key=key item=LABEL from=$MODULE_MODEL->getHeaders()}
                                <th class="bg-body-secondary text-secondary" width="15%">{vtranslate($LABEL, $LABEL)}</th>
                            {/foreach}
                        </tr>
                    </thead>
                    <tbody>
                        {foreach key=MAPPING_ID item=MAPPING from=$MODULE_MODEL->getMapping()}
                            <tr class="listViewEntries border-bottom" data-cfmid="{$MAPPING_ID}">
                                <td width="5%">
                                    {if $MAPPING['editable'] eq 1}
                                        {foreach item=LINK_MODEL from=$MODULE_MODEL->getMappingLinks()}
                                            <div class="table-actions">
                                                <span>
                                                    <a class="btn text-secondary" onclick={$LINK_MODEL->getUrl()}>
                                                        <i title="{vtranslate($LINK_MODEL->getLabel(), $MODULE)}" class="fa fa-trash alignMiddle"></i>
                                                    </a>
                                                </span>
                                            </div>
                                        {/foreach}
                                    {/if}
                                </td>
                                <td width="10%">{vtranslate({$MAPPING['Leads']['label']}, 'Leads')}</td>
                                <td width="10%">{vtranslate({$MAPPING['Leads']['fieldDataType']}, $QUALIFIED_MODULE)}</td>
                                <td width="10%">{vtranslate({$MAPPING['Accounts']['label']}, 'Accounts')}</td>
                                <td width="10%">{vtranslate({$MAPPING['Contacts']['label']}, 'Contacts')}</td>
                                <td width="10%">{vtranslate({$MAPPING['Potentials']['label']}, 'Potentials')}</td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
            <div id="scroller_wrapper" class="bottom-fixed-scroll">
                <div id="scroller" class="scroller-div"></div>
            </div>
		</div>
    </div>
{/strip}