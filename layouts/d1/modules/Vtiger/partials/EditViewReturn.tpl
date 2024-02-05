{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
{if $RETURN_VIEW}
    <input type="hidden" name="returnmodule" value="{$RETURN_MODULE}" />
    <input type="hidden" name="returnview" value="{$RETURN_VIEW}" />
    <input type="hidden" name="returnrecord" value="{$RETURN_RECORD}" />
    <input type="hidden" name="returntab_label" value="{$RETURN_RELATED_TAB}" />
    <input type="hidden" name="returnrelatedModule" value="{$RETURN_RELATED_MODULE}" />
    <input type="hidden" name="returnpage" value="{$RETURN_PAGE}" />
    <input type="hidden" name="returnviewname" value="{$RETURN_VIEW_NAME}" />
    <input type="hidden" name="returnsearch_params" value='{Vtiger_Functions::jsonEncode($RETURN_SEARCH_PARAMS)}' />
    <input type="hidden" name="returnsearch_key" value="{$RETURN_SEARCH_KEY}" />
    <input type="hidden" name="returnsearch_value" value="{$RETURN_SEARCH_VALUE}" />
    <input type="hidden" name="returnoperator" value="{$RETURN_SEARCH_OPERATOR}" />
    <input type="hidden" name="returnsortorder" value="{$RETURN_SORTBY}" />
    <input type="hidden" name="returnorderby" value="{$RETURN_ORDERBY}" />
    <input type="hidden" name="returnmode" value="{$RETURN_MODE}" />
    <input type="hidden" name="returnrelationId" value="{$RETURN_RELATION_ID}" />
    <input type="hidden" name="returnparent" value="{$RETURN_PARENT_MODULE}"/>
{/if}