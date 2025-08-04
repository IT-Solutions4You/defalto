{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="container-fluid main-container">
        <div class="row h-100">
            {include file='ModuleNavigator.tpl'|vtemplate_path:$MODULE}
            <div class="col overflow-auto">
                <div class="container bg-body my-3 rounded shadow">
                    <div class="row py-3 cursorDefault">
                        <div class="col">
                            <h1>{vtranslate($GUIDE->getLabel(), $QUALIFIED_MODULE)}</h1>
                            <h3 class="fs-5">{vtranslate($GUIDE->getDescription(), $QUALIFIED_MODULE)}</h3>
                        </div>
                    </div>
                    <div class="row align-items-center py-3">
                        {if $GUIDE->hasDemoData()}
                            <div class="col-12">
                                <div class="alert alert-primary">
                                    {vtranslate('LBL_DELETE_DATA_AFTER_FINISH', $QUALIFIED_MODULE)}
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="alert alert-primary">
                                    {vtranslate('LBL_CONTINUE_WITH_GUIDE', $QUALIFIED_MODULE)}
                                </div>
                            </div>
                            <div class="col text-end">
                                <a class="btn btn-primary" href="{$GUIDE->getDeleteDemoDataUrl()}">{vtranslate('LBL_DELETE_DEMO_DATA', $QUALIFIED_MODULE)}</a>
                            </div>
                            <div class="col">
                                <a class="btn btn-primary active" href="{$GUIDE->getNextStepUrl()}">{vtranslate('LBL_START_GUIDE', $QUALIFIED_MODULE)}</a>
                            </div>
                        {else}
                            <div class="col-12">
                                <div class="alert alert-primary">
                                    {vtranslate('LBL_IMPORT_DATA_REQUIRED', $QUALIFIED_MODULE)}
                                </div>
                            </div>
                            <div class="col text-end">
                                <a class="btn btn-primary" href="{$GUIDE->getGuidesUrl()}">{vtranslate('LBL_GUIDES', $QUALIFIED_MODULE)}</a>
                            </div>
                            <div class="col">
                                <a class="btn btn-primary active" href="{$GUIDE->getImportDemoDataUrl()}">{vtranslate('LBL_IMPORT_DEMO_DATA', $QUALIFIED_MODULE)}</a>
                            </div>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    </div>
{/strip}