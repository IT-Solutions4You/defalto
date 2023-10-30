{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    {assign var="MODULE_NAME" value=$MODULE_MODEL->get('name')}
    <input id="recordId" type="hidden" value="{$RECORD->getId()}" />
    <div class="detailViewContainer">
        <div class="detailViewTitle" id="prefPageHeader">
        </div>
        <div class="detailViewInfo userPreferences row-fluid">
            <div class="details col-xs-12">
            {/strip}