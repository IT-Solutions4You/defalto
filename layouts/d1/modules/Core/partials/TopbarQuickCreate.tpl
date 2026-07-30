<li class="ms-2">
    <div class="dropdown">
        <div data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="true">
            <a href="#" id="menubar_quickCreate" class="btn border-1 border-secondary text-secondary qc-button btn-outline-secondary" title="{vtranslate('LBL_QUICK_CREATE',$MODULE)}" aria-hidden="true">
                <i class="fa fa-plus-circle"></i>
            </a>
        </div>
        <ul class="dropdown-menu dropdown-menu-end dt-w-500 p-0 border-0 shadow" role="menu" aria-labelledby="dropdownMenu1">
            <li class="title py-3 px-4 border-bottom">
                <strong>{vtranslate('LBL_QUICK_CREATE',$MODULE)}</strong>
            </li>
            <li id="quickCreateModules">
                <div class="container-fluid py-3 px-4">
                    {assign var='count' value=0}
                    {foreach key=moduleName item=moduleModel from=$QUICK_CREATE_MODULES}
                        {if $moduleModel->isPermitted('CreateView') || $moduleModel->isPermitted('EditView')}
                            {assign var='quickCreateModule' value=$moduleModel->isQuickCreateSupported()}
                            {assign var='singularLabel' value=$moduleModel->getSingularLabelKey()}
                            {assign var=hideDiv value={!$moduleModel->isPermitted('CreateView') && $moduleModel->isPermitted('EditView')}}
                            {if $quickCreateModule == '1'}
                                {if $count % 3 == 0}
                                    <div class="row">
                                {/if}
                                {if $singularLabel == 'SINGLE_Documents'}
                                    <div class="{if $hideDiv}create_restricted_{$moduleModel->getName()} hide{else}col-lg-4 col-xs-4{/if}">
                                        <a id="menubar_quickCreate_{$moduleModel->getName()}" class="d-flex text-muted" data-name="{$moduleModel->getName()}" href="javascript:Documents_Index_Js.uploadTo('Vtiger')">
                                            <span class="lh-base">{$moduleModel->getModuleIcon()}</span>
                                            <span class="ps-3 quick-create-module">{vtranslate($singularLabel, $moduleName)}</span>
                                        </a>
                                    </div>
                                {else}
                                    <div class="{if $hideDiv}create_restricted_{$moduleModel->getName()} hide{else}col-lg-4 col-xs-4{/if}">
                                        <a id="menubar_quickCreate_{$moduleModel->getName()}" class="quickCreateModule d-flex text-muted" data-name="{$moduleModel->getName()}" data-url="{$moduleModel->getQuickCreateUrl()}" href="javascript:void(0)">
                                            <span class="lh-base">{$moduleModel->getModuleIcon()}</span>
                                            <span class="ps-3 quick-create-module">{vtranslate($singularLabel,$moduleName)}</span>
                                        </a>
                                    </div>
                                {/if}
                                {if $count % 3 == 2}
                                    </div>
                                    <br>
                                {/if}
                                {if !$hideDiv}
                                    {assign var='count' value=$count+1}
                                {/if}
                            {/if}
                        {/if}
                    {/foreach}
                </div>
            </li>
        </ul>
    </div>
</li>
