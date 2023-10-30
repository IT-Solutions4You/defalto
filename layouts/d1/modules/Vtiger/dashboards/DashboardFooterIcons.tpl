{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
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
{if !empty($CHART_TYPE) && $REPORT_MODEL->isEditable() eq true}
<a class="me-2 text-secondary" href="{$REPORT_MODEL->getEditViewUrl()}" name="customizeChartReportWidget">
	<i class="fa fa-edit" hspace="2" border="0" align="absmiddle" title="{vtranslate('LBL_CUSTOMIZE',$MODULE)}" alt="{vtranslate('LBL_CUSTOMIZE',$MODULE)}"></i>
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