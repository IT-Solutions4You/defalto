{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
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

