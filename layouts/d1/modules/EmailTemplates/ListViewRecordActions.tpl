{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{strip}
<!--LIST VIEW RECORD ACTIONS-->

<div class="table-actions d-flex align-items-center">
    {if !$SEARCH_MODE_RESULTS}
    <span class="input">
        <input type="checkbox" value="{$LISTVIEW_ENTRY->getId()}" class="listViewEntriesCheckBox form-check-input m-0"/>
    </span>
    {/if}
    <div class="more dropdown action ms-2">
        <a href="javascript:;" class="px-2" data-bs-toggle="dropdown">
            <i class="fa fa-ellipsis-v icon"></i>
        </a>
        <ul class="dropdown-menu">
            <li>
                <a class="dropdown-item" data-id="{$LISTVIEW_ENTRY->getId()}" href="{$LISTVIEW_ENTRY->getFullDetailViewUrl()}">{vtranslate('LBL_DETAILS', $MODULE)}</a>
            </li>
            <li>
                <a class="dropdown-item editLink" data-id="{$LISTVIEW_ENTRY->getId()}" href="javascript:void(0);" data-url="{$LISTVIEW_ENTRY->getEditViewUrl()}">{vtranslate('LBL_EDIT', $MODULE)}</a>
            </li>
            <li>
                <a class="dropdown-item" data-id="{$LISTVIEW_ENTRY->getId()}" class="deleteRecordButton">{vtranslate('LBL_DELETE', $MODULE)}</a>
            </li>
        </ul>
    </div>
    <div class="btn-group inline-save hide">
        <button class="button btn-success btn-small save" name="save"><i class="fa fa-check"></i></button>
        <button class="button btn-danger btn-small cancel" name="Cancel"><i class="fa fa-close"></i></button>
    </div>
</div>
{/strip}
