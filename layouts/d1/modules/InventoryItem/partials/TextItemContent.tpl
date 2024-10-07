{**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}

<td class="text-center">
    <a class="btn deleteRow me-2">
        <i class="fa fa-trash" title="{vtranslate('LBL_DELETE',$MODULE)}"></i>
    </a>
    <a>
        <img src="{vimage_path('drag.png')}" title="{vtranslate('LBL_DRAG',$MODULE)}"/>
    </a>
    <input type="hidden" class="rowNumber" value="{$row_no}" />
</td>
<td colspan="50">
    {assign var="productName" value="productName"|cat:$row_no}
    <input type="text" id="{$productName}" name="{$productName}" value="{$data.$productName}" class="productName form-control autoComplete" data-rule-required=true>
    <input type="hidden" id="{$hdnProductId}" name="{$hdnProductId}" value="{$data.$hdnProductId}" class="selectedModuleId"/>
    <input type="hidden" id="lineItemType{$row_no}" name="lineItemType{$row_no}" value="Text" class="lineItemType"/>
</td>
