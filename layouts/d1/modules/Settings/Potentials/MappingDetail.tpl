{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{* modules/Settings/Potentials/views/MappingDetail.php *}

{strip}
    <div class="potentialsFieldMappingListPageDiv px-4 pb-4">
        <div class="rounded bg-body">
            <div class="text-end p-3">
                {foreach item=LINK_MODEL from=$MODULE_MODEL->getDetailViewLinks()}
                    <button type="button" class="btn btn-outline-secondary" onclick={$LINK_MODEL->getUrl()}>{vtranslate($LINK_MODEL->getLabel(), $QUALIFIED_MODULE)}</button>
                {/foreach}
            </div>
            <div class="contents table-container" id="detailView">
                <table class="table table-borderless listview-table" id="listview-table">
                    <thead>
                    <tr>
                        <th class="bg-body-secondary"></th>
                        <th class="bg-body-secondary">{vtranslate('LBL_FIELD_LABEL', $QUALIFIED_MODULE)}</th>
                        <th class="bg-body-secondary">{vtranslate('LBL_FIELD_TYPE', $QUALIFIED_MODULE)}</th>
                        <th class="bg-body-secondary">{vtranslate('LBL_MAPPING_WITH_OTHER_MODULES', $QUALIFIED_MODULE)}</th>
                    </tr>
                    <tr>
                        <th class="bg-body-secondary" width="10%">{vtranslate('LBL_ACTIONS', $QUALIFIED_MODULE)}</th>
                        {foreach key=key item=LABEL from=$MODULE_MODEL->getHeaders()}
                            <th class="bg-body-secondary" width="30%">{vtranslate($LABEL, $LABEL)}</th>
                        {/foreach}
                    </tr>
                    </thead>
                    <tbody>
                    {foreach key=MAPPING_ID item=MAPPING from=$MODULE_MODEL->getMapping()}
                        <tr class="listViewEntries border-bottom" data-cfmid="{$MAPPING_ID}">
                            <td>
                                {if $MAPPING['editable'] eq 1}
                                    {foreach item=LINK_MODEL from=$MODULE_MODEL->getMappingLinks()}
                                        <div class="table-actions">
                                            <span class="actionImages btn-group">
                                                <a onclick={$LINK_MODEL->getUrl()}><i title="{vtranslate($LINK_MODEL->getLabel(), $MODULE)}" class="fa fa-trash alignMiddle"></i></a>
                                            </span>
                                        </div>
                                    {/foreach}
                                {/if}
                            </td>
                            <td>{vtranslate({$MAPPING['Potentials']['label']}, 'Potentials')}</td>
                            <td>{vtranslate($MAPPING['Potentials']['fieldDataType'], $QUALIFIED_MODULE)}</td>
                            <td>{vtranslate({$MAPPING['Project']['label']}, 'Project')}</td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
        <div id="scroller_wrapper" class="bottom-fixed-scroll">
            <div id="scroller" class="scroller-div"></div>
        </div>
    </div>
{/strip}