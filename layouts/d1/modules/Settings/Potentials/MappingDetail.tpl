{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* modules/Settings/Potentials/views/MappingDetail.php *}
{strip}
    <div class="potentialsFieldMappingListPageDiv px-4 pb-4">
        <div class="rounded bg-body">
            <div class="container-fluid p-3">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="m-0">{vtranslate('LBL_OPPORTUNITY_MAPPING', $QUALIFIED_MODULE)}</h4>
                    </div>
                    <div class="col-auto ms-2">
                        {foreach item=LINK_MODEL from=$MODULE_MODEL->getDetailViewLinks()}
                            <button type="button" class="btn btn-outline-secondary" onclick={$LINK_MODEL->getUrl()}>{vtranslate($LINK_MODEL->getLabel(), $QUALIFIED_MODULE)}</button>
                        {/foreach}
                    </div>
                </div>
            </div>
            <div class="contents table-container" id="detailView">
                <table class="table table-borderless listview-table" id="settings-listview-table">
                    <thead>
                    <tr>
                        <th class="bg-body-secondary"></th>
                        <th class="bg-body-secondary text-secondary">{vtranslate('LBL_FIELD_LABEL', $QUALIFIED_MODULE)}</th>
                        <th class="bg-body-secondary text-secondary">{vtranslate('LBL_FIELD_TYPE', $QUALIFIED_MODULE)}</th>
                        <th class="bg-body-secondary text-secondary">{vtranslate('LBL_MAPPING_WITH_OTHER_MODULES', $QUALIFIED_MODULE)}</th>
                    </tr>
                    <tr>
                        <th class="bg-body-secondary text-secondary" width="10%">{vtranslate('LBL_ACTIONS', $QUALIFIED_MODULE)}</th>
                        {foreach key=key item=LABEL from=$MODULE_MODEL->getHeaders()}
                            <th class="bg-body-secondary text-secondary" width="30%">{vtranslate($LABEL, $LABEL)}</th>
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
                                                <a class="btn text-secondary" onclick={$LINK_MODEL->getUrl()}>
                                                    <i title="{vtranslate($LINK_MODEL->getLabel(), $MODULE)}" class="fa fa-trash alignMiddle"></i>
                                                </a>
                                            </span>
                                        </div>
                                    {/foreach}
                                {/if}
                            </td>
                            <td>{if isset($MAPPING['Potentials']['label'])}{vtranslate($MAPPING['Potentials']['label'], 'Potentials')}{/if}</td>
                            <td>{if isset($MAPPING['Potentials']['fieldDataType'])}{vtranslate($MAPPING['Potentials']['fieldDataType'], $QUALIFIED_MODULE)}{/if}</td>
                            <td>{if isset($MAPPING['Project']['label'])}{vtranslate($MAPPING['Project']['label'], 'Project')}{/if}</td>
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