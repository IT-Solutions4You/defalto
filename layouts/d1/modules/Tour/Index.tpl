{*
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
{strip}
    <div class="container-fluid main-container">
        <div class="row h-100">
            {include file='ModuleNavigator.tpl'|vtemplate_path:$MODULE}
            <div class="col overflow-auto">
                <div class="container bg-body my-3 rounded shadow pb-5">
                    <div class="row py-3 cursorDefault">
                        <div class="col">
                            <h1 class="fw-bold text-primary">{vtranslate('LBL_READY_TO_USE_CRM', $QUALIFIED_MODULE)}</h1>
                            <br>
                            <h2>{vtranslate('LBL_WHAT_TO_DO_NEXT', $QUALIFIED_MODULE)}</h2>
                        </div>
                    </div>
                    <a class="row align-items-center py-3" href="https://defalto.com" target="_blank">
                        <div class="col-lg-2 fs-1 text-center text-primary">
                            <i class="fa-solid fa-lightbulb"></i>
                        </div>
                        <div class="col">
                            <h3 class="text-primary">{vtranslate('LBL_VISIT_CRM', $QUALIFIED_MODULE)}</h3>
                            <p class="m-0">{vtranslate('LBL_VISIT_CRM_DESC', $QUALIFIED_MODULE)}</p>
                        </div>
                    </a>
                    <a class="row align-items-center py-3" href="https://defalto.com/docs/user-guide/" target="_blank">
                        <div class="col-lg-2 fs-1 text-center text-primary">
                            <i class="fa-solid fa-book"></i>
                        </div>
                        <div class="col">
                            <h3 class="text-primary">{vtranslate('LBL_DOCS', $QUALIFIED_MODULE)}</h3>
                            <p class="m-0">{vtranslate('LBL_DOCS_DESC', $QUALIFIED_MODULE)}</p>
                        </div>
                    </a>
                    <a class="row align-items-center py-3" href="https://github.com/IT-Solutions4You/defalto/issues" target="_blank">
                        <div class="col-lg-2 fs-1 text-center text-primary">
                            <i class="fa-solid fa-comment"></i>
                        </div>
                        <div class="col">
                            <h3 class="text-primary">{vtranslate('LBL_FORUMS', $QUALIFIED_MODULE)}</h3>
                            <p class="m-0">{vtranslate('LBL_FORUMS_DESC', $QUALIFIED_MODULE)}</p>
                        </div>
                    </a>
                    <div class="row py-3 cursorDefault">
                        <div class="col">
                            <br>
                            <h2>{vtranslate('LBL_GUIDES', $QUALIFIED_MODULE)}</h2>
                        </div>
                    </div>
                    {foreach from=$GUIDES item=GUIDE}
                        <a class="row align-items-center py-3" href="index.php?module=Tour&view=Guide&name={$GUIDE->getName()}">
                            <div class="col-lg-2 fs-1 text-center text-primary">
                                <i class="{$GUIDE->getIcon()}"></i>
                            </div>
                            <div class="col">
                                <h3 class="text-primary">{vtranslate($GUIDE->getLabel(), $QUALIFIED_MODULE)}</h3>
                                <p class="m-0">{vtranslate($GUIDE->getDescription(), $QUALIFIED_MODULE)}</p>
                            </div>
                        </a>
                    {/foreach}
                </div>
            </div>
        </div>
    </div>
{/strip}