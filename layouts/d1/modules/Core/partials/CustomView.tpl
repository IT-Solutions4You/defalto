{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
{strip}
    {assign var=CUSTOM_VIEWS value=CustomView_Record_Model::getAllByGroup($MODULE)}
    {assign var=ACTIVE_CUSTOM_VIEW value=CustomView_Record_Model::getInstanceByRequest($REQUEST_INSTANCE)}
    <div class="dropdown col">
        <div class="overflow-hidden w-25vw-max cursorPointer" data-bs-toggle="dropdown">
            <div class="d-flex align-items-center">
                <span class="current-filter-name text-truncate filter-name fs-5">{$ACTIVE_CUSTOM_VIEW->get('viewname')}</span>
                <i class="fa-solid fa-filter ms-2"></i>
            </div>
        </div>
        <div class="dropdown-menu w-30rem position-absolute">
            <div class="dropdown-header">
                <input type="text" class="form-control" data-search="1">
            </div>
            <div class="overflow-auto h-25vh-max">
                {include file="partials/CustomViewRecords.tpl"|vtemplate_path:$MODULE CUSTOM_VIEWS=$CUSTOM_VIEWS['Default']}
                <div class="dropdown-header">{vtranslate('LBL_MINE', $MODULE)}</div>
                {include file="partials/CustomViewRecords.tpl"|vtemplate_path:$MODULE CUSTOM_VIEWS=$CUSTOM_VIEWS['Mine']}
                <div class="dropdown-header">{vtranslate('LBL_SHARED_LIST', $MODULE)}</div>
                {include file="partials/CustomViewRecords.tpl"|vtemplate_path:$MODULE CUSTOM_VIEWS=$CUSTOM_VIEWS['Shared']}
            </div>
            <div class="dropdown-divider"></div>
            <div>
                <a class="dropdown-item" href="#" data-cv-create-url="{$ACTIVE_CUSTOM_VIEW->getCreateUrl()}">
                    <i class="fa-solid fa-plus"></i>
                    <span class="ms-2">{vtranslate('LBL_CREATE', $MODULE)}</span>
                </a>
            </div>
        </div>
    </div>
{/strip}