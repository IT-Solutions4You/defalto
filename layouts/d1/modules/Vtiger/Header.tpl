{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
	<head>
		<title>{vtranslate($PAGETITLE, $QUALIFIED_MODULE)}</title>
        <link rel="SHORTCUT ICON" href="layouts/d1/skins/images/favicon.ico">
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<link type='text/css' rel='stylesheet' href='{vresource_url('vendor/twbs/bootstrap/dist/css/bootstrap.min.css')}'>
		<link type='text/css' rel='stylesheet' href='{vresource_url('vendor/twbs/bootstrap-icons/font/bootstrap-icons.min.css')}'>
		<link type='text/css' rel='stylesheet' href='{vresource_url('layouts/$LAYOUT$/lib/font-awesome/css/font-awesome.min.css')}'>
		<link type='text/css' rel='stylesheet' href='{vresource_url('vendor/components/font-awesome/css/all.min.css')}'>
        <link type='text/css' rel='stylesheet' href='{vresource_url('vendor/select2/select2/dist/css/select2.min.css')}'>
        <link type='text/css' rel='stylesheet' href='{vresource_url('vendor/apalfrey/select2-bootstrap-5-theme/dist/select2-bootstrap-5-theme.min.css')}'>
        <link type='text/css' rel='stylesheet' href='{vresource_url('layouts/$LAYOUT$/lib/jquery/jquery-ui-1.12.0.custom/jquery-ui.css')}'>
        <link type='text/css' rel='stylesheet' href='{vresource_url('layouts/$LAYOUT$/lib/vt-icons/style.css')}'>
        <link type='text/css' rel='stylesheet' href='{vresource_url('layouts/$LAYOUT$/lib/animate/animate.min.css')}'>
        <link type='text/css' rel='stylesheet' href='{vresource_url('layouts/$LAYOUT$/lib/jquery/malihu-custom-scrollbar/jquery.mCustomScrollbar.css')}'>
        <link type='text/css' rel='stylesheet' href='{vresource_url('layouts/$LAYOUT$/lib/jquery/jquery.qtip.custom/jquery.qtip.css')}'>
		{assign var=THEME_PATH value=Vtiger_Theme::getv7AppStylePath('base')}
		<link type="text/css" rel="{if strpos($THEME_PATH,'.less') ne false}stylesheet/less{else}stylesheet{/if}" href="{vresource_url($THEME_PATH)}" media="screen"/>
        {foreach key=index item=cssModel from=$STYLES}
			<link type="text/css" rel="{$cssModel->getRel()}" href="{vresource_url($cssModel->getHref())}" media="{$cssModel->getMedia()}" />
		{/foreach}
		<script type="text/javascript">var __pageCreationTime = (new Date()).getTime();</script>
		<script src="{vresource_url('layouts/$LAYOUT$/lib/jquery/jquery.min.js')}"></script>
		<script src="{vresource_url('layouts/$LAYOUT$/lib/jquery/jquery-migrate-1.4.1.js')}"></script>
		<script type="text/javascript">
			var _META = { 'module': "{$MODULE}", view: "{$VIEW}", 'parent': "{$PARENT_MODULE}", 'notifier':"{$NOTIFIER_URL}", 'app':"{if isset($SELECTED_MENU_CATEGORY)}{$SELECTED_MENU_CATEGORY}{/if}" };
            {if $EXTENSION_MODULE}
                var _EXTENSIONMETA = { 'module': "{$EXTENSION_MODULE}", view: "{$EXTENSION_VIEW}"};
            {/if}
            var _USERMETA;
            {if $CURRENT_USER_MODEL}
               _USERMETA =  { 'id' : "{$CURRENT_USER_MODEL->get('id')}", 'menustatus' : "{$CURRENT_USER_MODEL->get('leftpanelhide')}",
                              'currency' : "{decode_html($USER_CURRENCY_SYMBOL)}", 'currencySymbolPlacement' : "{$CURRENT_USER_MODEL->get('currency_symbol_placement')}",
                          'currencyGroupingPattern' : "{$CURRENT_USER_MODEL->get('currency_grouping_pattern')}", 'truncateTrailingZeros' : "{$CURRENT_USER_MODEL->get('truncate_trailing_zeros')}",'userlabel':"{($CURRENT_USER_MODEL->get('userlabel'))|escape:html}",};
            {/if}
		</script>
	</head>
	 {assign var=CURRENT_USER_MODEL value=Users_Record_Model::getCurrentUserModel()}
	<body data-skinpath="{Vtiger_Theme::getBaseThemePath()}" data-language="{$LANGUAGE}" data-user-decimalseparator="{$CURRENT_USER_MODEL->get('currency_decimal_separator')}" data-user-dateformat="{$CURRENT_USER_MODEL->get('date_format')}"
          data-user-groupingseparator="{$CURRENT_USER_MODEL->get('currency_grouping_separator')}" data-user-numberofdecimals="{$CURRENT_USER_MODEL->get('no_of_currency_decimals')}" data-user-hourformat="{$CURRENT_USER_MODEL->get('hour_format')}"
          data-user-calendar-reminder-interval="{$CURRENT_USER_MODEL->getCurrentUserActivityReminderInSeconds()}" class="bg-body-secondary d-flex flex-column">
		<input type="hidden" id="start_day" value="{$CURRENT_USER_MODEL->get('dayoftheweek')}" />
		<input type="hidden" id="inventoryModules" value={ZEND_JSON::encode($INVENTORY_MODULES)}>
		<main id="page">
            <div id="pjaxContainer" class="hide noprint"></div>
            <div id="messageBar" class="hide"></div>
{/strip}