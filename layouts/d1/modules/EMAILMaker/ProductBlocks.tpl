{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="h-main p-4">
        <div class="container-fluid p-3 rounded bg-body" id="ProductBlocksContainer">
            <form name="product_blocks" action="index.php" method="post" class="form-horizontal">
                <input type="hidden" name="module" value="{$MODULE}"/>
                <input type="hidden" name="view" value="EditProductBlock"/>
                <input type="hidden" name="action" value=""/>
                <input type="hidden" name="tplid" value=""/>
                <input type="hidden" name="mode" value=""/>
                <h3>{vtranslate('LBL_PRODUCTBLOCKTPL',$MODULE)}</h3>
                <p>{vtranslate('LBL_PRODUCTBLOCKTPL_DESC',$MODULE)}</p>
                <hr>
                <div class="row-fluid">
                    <div class="row py-2">
                        <div class="col">
                            <strong>{vtranslate('LBL_DEFINE_PBTPL',$MODULE)}:</strong>
                        </div>
                        <div class="col-auto">
                            <button type="button" class="addProductBlock btn btn-primary active addButton ProductBlockBtn" data-url="index.php?module={$MODULE}&view=EditProductBlock">
                                <i class="fa fa-plus"></i>
                                <strong class="ms-2">{vtranslate('LBL_ADD')}</strong>
                            </button>
                        </div>
                        <div class="col-auto">
                            <button type="reset" class="btn btn-primary" onClick="window.history.back();">{vtranslate('LBL_CANCEL')}</button>
                        </div>
                    </div>
                    <div class="pushDownHalfper">
                        <table id="ProductBlocksTable" class="table table-bordered table-condensed ProductBlocksTable">
                            <thead>
                            <tr class="blockHeader">
                                <th class="text-secondary bg-body-secondary">{vtranslate('LBL_EMAIL_NAME',$MODULE)}</th>
                                <th class="text-secondary bg-body-secondary w-75" id="bodyColumn">{vtranslate('LBL_BODY',$MODULE)}</th>
                                <th class="text-secondary bg-body-secondary" nowrap></th>
                            </tr>
                            </thead>
                            <tbody>
                            <script type="text/javascript" language="javascript">var existingKeys = [];</script>
                            {foreach item=arr key=tpl_id item=tpl_value from=$PB_TEMPLATES name=tpl_foreach}
                                <tr class="opacity">
                                    <td>{$tpl_value.name}</td>
                                    <td>
                                        <div class="overflow-auto w-100">
                                            <div style="width: 50vw;">
                                                {$tpl_value.body}
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <div class="btn-toolbar">
                                                <button type="button" class="btn btn-outline-secondary me-2 editProductBlock ProductBlockBtn" data-url="index.php?module={$MODULE}&view=EditProductBlock&tplid={$tpl_id}" data-tplid="{$tpl_id}">
                                                    <i title="{vtranslate('LBL_EDIT',$MODULE)}" class="fa fa-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary me-2 duplicateProductBlock ProductBlockBtn" data-url="index.php?module={$MODULE}&view=EditProductBlock&tplid={$tpl_id}&mode=duplicate" data-tplid="{$tpl_id}">
                                                    <i title="{vtranslate('LBL_DUPLICATE',$MODULE)}" class="fa-solid fa-clone"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary ProductBlockBtn" data-url="index.php?module={$MODULE}&action=IndexAjax&mode=DeleteProductBlock&tplid={$tpl_id}" data-tplid="{$tpl_id}">
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
                    <div class="row">
                        <div class="col text-end">
                            <button type="button" class="addProductBlock btn btn-primary active addButton ProductBlockBtn" data-url="index.php?module={$MODULE}&view=EditProductBlock">
                                <i class="fa fa-plus"></i>
                                <strong class="ms-2">{vtranslate('LBL_ADD')}</strong>
                            </button>
                        </div>
                        <div class="col-auto">
                            <button type="reset" class="btn btn-primary" onClick="window.history.back();">{vtranslate('LBL_CANCEL')}</button>
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
    </div>
{/strip}