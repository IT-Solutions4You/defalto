{*<!--
/*********************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
-->*}
{strip}
    <div class="container-fluid" id="ProductBlocksContainer">
        <form name="product_blocks" action="index.php" method="post" class="form-horizontal">
            <input type="hidden" name="module" value="{$MODULE}"/>
            <input type="hidden" name="view" value="EditProductBlock"/>
            <input type="hidden" name="action" value=""/>
            <input type="hidden" name="tplid" value=""/>
            <input type="hidden" name="mode" value=""/>
            <br>
            <label class="pull-left themeTextColor font-x-x-large">{vtranslate('LBL_PRODUCTBLOCKTPL',$MODULE)}</label>
            <br clear="all">{vtranslate('LBL_PRODUCTBLOCKTPL_DESC',$MODULE)}
            <hr>
            <br/>
            <div class="row-fluid">
                <label class="fieldLabel"><strong>{vtranslate('LBL_DEFINE_PBTPL',$MODULE)}:</strong></label><br>
                <div class="row-fluid">
                    <div class="pull-right btn-group">
                        <button type="button" class="addProductBlock btn addButton btn-default ProductBlockBtn" data-url="index.php?module={$MODULE}&view=EditProductBlock"><i class="icon-plus icon-white"></i>&nbsp;<strong> {vtranslate('LBL_ADD')}</strong></button>
                        <button type="reset" class="btn btn-default" onClick="window.history.back();">{vtranslate('LBL_CANCEL')}</button>
                    </div>
                </div>
                <div class="pushDownHalfper">
                    <table id="ProductBlocksTable" class="table table-bordered table-condensed ProductBlocksTable" style="padding:0px;margin:0px" id="lbltbl">
                        <thead>
                        <tr class="blockHeader">
                            <th style="border-left: 1px solid #DDD !important;" width="250px">{vtranslate('LBL_EMAIL_NAME',$MODULE)}</th>
                            <th style="border-left: 1px solid #DDD !important;" id="bodyColumn">{vtranslate('LBL_BODY',$MODULE)}</th>
                            <th style="border-left: 0px solid #DDD !important;" width="200px" nowrap></th>
                        </tr>
                        </thead>
                        <tbody>
                        <script type="text/javascript" language="javascript">var existingKeys = [];</script>
                        {foreach item=arr key=tpl_id item=tpl_value from=$PB_TEMPLATES name=tpl_foreach}
                            <tr class="opacity">
                                <td>{$tpl_value.name}</td>
                                <td>
                                    <div style="overflow-x:auto; overflow-y:auto; width:100px;" class="bodyCell">
                                        {$tpl_value.body}
                                    </div>
                                </td>
                                <td style="border-left: none;">
                                    <div class="pull-right actions">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-default editProductBlock ProductBlockBtn" data-url="index.php?module={$MODULE}&view=EditProductBlock&tplid={$tpl_id}" data-tplid="{$tpl_id}">
                                                <i title="{vtranslate('LBL_EDIT',$MODULE)}" class="fa fa-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-default duplicateProductBlock ProductBlockBtn" data-url="index.php?module={$MODULE}&view=EditProductBlock&tplid={$tpl_id}&mode=duplicate" data-tplid="{$tpl_id}">{vtranslate('LBL_DUPLICATE',$MODULE)}</button>
                                            <button type="button" class="btn btn-danger ProductBlockBtn" data-url="index.php?module={$MODULE}&action=IndexAjax&mode=DeleteProductBlock&tplid={$tpl_id}" data-tplid="{$tpl_id}">
                                                <i title="{vtranslate('LBL_DELETE',$MODULE)}" class="fa fa-trash"></i>
                                            </button>
                                        </div>

                                    </div>
                                </td>
                            </tr>
                            {foreachelse}
                            <tr id="noItemFountTr">
                                <td colspan="4" class="cellText" align="center" style="padding:10px;"><strong>{vtranslate('LBL_NO_ITEM_FOUND',$MODULE)}</strong></td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
                <div id="otherLangsDiv" style="display:none; width:350px; position:absolute;" class="layerPopup"></div>
                <div class="row-fluid pushDownHalfper">
                    <div class="pull-right btn-group">
                        <button type="button" class="addProductBlock btn btn-default addButton ProductBlockBtn" data-url="index.php?module={$MODULE}&view=EditProductBlock"><i class="icon-plus icon-white"></i>&nbsp;<strong> {vtranslate('LBL_ADD')}</strong></button>
                        <button type="reset" class="btn btn-default" onClick="window.history.back();">{vtranslate('LBL_CANCEL')}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <script type="text/javascript" language="javascript">
        {literal}
        jQuery(document).ready(function () {
            var elmWidth = jQuery("#bodyColumn").width();
            jQuery(".bodyCell").each(function () {
                jQuery(this).css("width", elmWidth + "px");
            });
        });
        {/literal}
    </script>
{/strip}