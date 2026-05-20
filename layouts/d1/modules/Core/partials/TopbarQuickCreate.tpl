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
                                    <div class="{if $hideDiv}create_restricted_{$moduleModel->getName()} hide{else}col-lg-4 col-xs-4{/if} dropdown">
                                        <a id="menubar_quickCreate_{$moduleModel->getName()}" class="quickCreateModuleSubmenu fs-6 text-muted" data-name="{$moduleModel->getName()}" data-bs-toggle="dropdown" data-url="{$moduleModel->getQuickCreateUrl()}" href="javascript:void(0)">
                                            <span class="lh-base">{$moduleModel->getModuleIcon()}</span>
                                            <span class="quick-create-module ps-3">
                                                                    {vtranslate($singularLabel,$moduleName)}
                                                                    <i class="fa fa-caret-down quickcreateMoreDropdownAction"></i>
                                                                </span>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-end quickcreateMoreDropdown" aria-labelledby="menubar_quickCreate_{$moduleModel->getName()}">
                                            <li>
                                                <h6 class="dropdown-header">
                                                    <i class="fa fa-upload"></i>
                                                    <span class="ps-3">{vtranslate('LBL_FILE_UPLOAD', $moduleName)}</span>
                                                </h6>
                                            </li>
                                            <li id="VtigerAction">
                                                <a class="dropdown-item" href="javascript:Documents_Index_Js.uploadTo('Vtiger')">
                                                    <i class="fa fa-home"></i>
                                                    <span class="ps-3">{vtranslate('LBL_TO_SERVICE', $moduleName, {vtranslate('LBL_CRM', $moduleName)})}</span>
                                                </a>
                                            </li>
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li>
                                                <h6 class="dropdown-header">
                                                    <i class="fa fa-link"></i>
                                                    <span class="ps-3">{vtranslate('LBL_LINK_EXTERNAL_DOCUMENT', $moduleName)}</span>
                                                </h6>
                                            </li>
                                            <li id="shareDocument">
                                                <a class="dropdown-item" href="javascript:Documents_Index_Js.createDocument('E')">
                                                    <i class="fa fa-external-link"></i>
                                                    <span class="ps-3">{vtranslate('LBL_FROM_SERVICE', $moduleName, {vtranslate('LBL_FILE_URL', $moduleName)})}</span>
                                                </a>
                                            </li>
                                            <li id="createDocument">
                                                <a class="dropdown-item" href="javascript:Documents_Index_Js.createDocument('W')">
                                                    <i class="fa fa-file-text"></i>
                                                    <span class="ps-3">{vtranslate('LBL_CREATE_NEW', $moduleName, {vtranslate('SINGLE_Documents', $moduleName)})}</span>
                                                </a>
                                            </li>
                                        </ul>
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
