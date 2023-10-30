{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{* modules/Inventory/views/SubProductsPopupAjax.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
{include file="PicklistColorMap.tpl"|vtemplate_path:$MODULE}
<div class="row">
    {include file='PopupNavigation.tpl'|vtemplate_path:$MODULE}
</div>
<div id='popupContentsDiv'>
<div class="row">
    <div class="col-md-12">
	{include file="PopupEntries.tpl"|@vtemplate_path:$MODULE_NAME}
    </div>
</div>
</div>
{/strip}

