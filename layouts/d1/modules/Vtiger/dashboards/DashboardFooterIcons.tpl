{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{if $SETTING_EXIST}
<a class="me-2 text-secondary" name="dfilter">
	<i class='fa fa-cog' border='0' align="absmiddle" title="{vtranslate('LBL_FILTER')}" alt="{vtranslate('LBL_FILTER')}"/>
</a>
{/if}
{if !empty($CHART_TYPE)}
    {assign var=CHART_DATA value=ZEND_JSON::decode($DATA)}
    {assign var=CHART_VALUES value=$CHART_DATA['values']}
{/if}
{if (!empty($DATA) && empty($CHART_TYPE))|| !empty($CHART_VALUES)}
<a class="me-2 text-secondary" href="javascript:void(0);" name="widgetFullScreen">
	<i class="fa fa-arrows-alt" hspace="2" border="0" align="absmiddle" title="{vtranslate('LBL_FULLSCREEN')}" alt="{vtranslate('LBL_FULLSCREEN')}"></i>
</a>
{/if}
<a class="me-2 text-secondary" href="javascript:void(0);" name="drefresh" data-url="{$WIDGET->getUrl()}&linkid={$WIDGET->get('linkid')}&content=data">
	<i class="fa fa-refresh" hspace="2" border="0" align="absmiddle" title="{vtranslate('LBL_REFRESH')}" alt="{vtranslate('LBL_REFRESH')}"></i>
</a>
{if !$WIDGET->isDefault()}
	<a class="widget me-2 text-secondary" name="dclose" data-url="{$WIDGET->getDeleteUrl()}">
		<i class="fa fa-remove" hspace="2" border="0" align="absmiddle" title="{vtranslate('LBL_REMOVE')}" alt="{vtranslate('LBL_REMOVE')}"></i>
	</a>
{/if}