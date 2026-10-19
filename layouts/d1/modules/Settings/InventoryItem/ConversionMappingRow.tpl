{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <tr class="conversionMappingRow">
        <td>
            <select class="form-select form-select-sm conversionMappingTarget" data-name="target">
                <option value="">{vtranslate('LBL_SELECT_OPTION', $QUALIFIED_MODULE)}</option>
                {foreach key=FIELD_NAME item=FIELD_LABEL from=$TARGET_FIELD_OPTIONS}
                    <option value="{$FIELD_NAME}" {if $FIELD_NAME eq $TARGET_FIELD}selected{/if}>{$FIELD_LABEL}</option>
                {/foreach}
            </select>
        </td>
        <td>
            <select class="form-select form-select-sm conversionMappingSource" data-name="source">
                <option value="">{vtranslate('LBL_CONVERSION_MAPPING_NOT_COPIED', $QUALIFIED_MODULE)}</option>
                {foreach key=FIELD_NAME item=FIELD_LABEL from=$SOURCE_FIELD_OPTIONS}
                    <option value="{$FIELD_NAME}" {if $FIELD_NAME eq $SOURCE_FIELD}selected{/if}>{$FIELD_LABEL}</option>
                {/foreach}
            </select>
        </td>
        <td class="text-end"><a class="btn btn-sm text-secondary removeConversionMappingRow"><i class="fa fa-trash-o"></i></a></td>
    </tr>
{/strip}
