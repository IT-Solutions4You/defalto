{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
	<!DOCTYPE html>
	<html>
		<head>
			<title>{vtranslate($PAGETITLE, $MODULE_NAME)}</title>
			<link rel="SHORTCUT ICON" href="layouts/d1/skins/images/favicon.ico">
			<meta name="viewport" content="width=device-width, initial-scale=1.0" />
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
			<link type='text/css' rel='stylesheet' href='{vresource_url('vendor/twbs/bootstrap/dist/css/bootstrap.min.css')}'>
			<link type='text/css' rel='stylesheet' href='{vresource_url('vendor/twbs/bootstrap/dist/css/bootstrap.min.css')}'>
			<link type='text/css' rel='stylesheet' href='{vresource_url('vendor/components/font-awesome/css/all.min.css')}'>
			<link type='text/css' rel='stylesheet' href='{vresource_url('vendor/select2/select2/dist/css/select2.min.css')}'>
			<link type='text/css' rel='stylesheet' href='{vresource_url('vendor/apalfrey/select2-bootstrap-5-theme/dist/select2-bootstrap-5-theme.min.css')}'>

			<link type='text/css' rel='stylesheet' href='libraries/bootstrap/js/eternicode-bootstrap-datepicker/css/datepicker3.css'/>
			<link type='text/css' rel='stylesheet' href='layouts/d1/lib/jquery/jquery-ui-1.12.0.custom/jquery-ui.css'/>
			<link type='text/css' rel='stylesheet' href='layouts/d1/lib/vt-icons/style.css'/>

			{assign var=THEME_PATH value=Vtiger_Theme::getv7AppStylePath('base')}
			{if strpos($THEME_PATH,".less") ne false}
				<link type="text/css" rel="stylesheet/less" href="{vresource_url($THEME_PATH)}" media="screen"/>
			{else}
				<link type="text/css" rel="stylesheet" href="{vresource_url($THEME_PATH)}" media="screen"/>
			{/if}

			{foreach key=index item=cssModel from=$STYLES}
				<link type="text/css" rel="{$cssModel->getRel()}" href="{vresource_url($cssModel->getHref())}" media="{$cssModel->getMedia()}" />
			{/foreach}

			{* For making pages - print friendly *}
			<style type="text/css">
				@media print {
				.noprint { display:none; }
			}
			</style>

			<script src="{vresource_url('layouts/d1/lib/jquery/jquery.min.js')}"></script>
			<script type="text/javascript">
				var _META = { 'module': "{$MODULE}", view: "{$VIEW}", 'parent': "{$PARENT_MODULE}" };
				{if $EXTENSION_MODULE}
					var _EXTENSIONMETA = { 'module': "{$EXTENSION_MODULE}", view: "{$EXTENSION_VIEW}"};
				{/if}
				var _USERMETA;
				{if $CURRENT_USER_MODEL}
					_USERMETA =  { 'id' : "{$CURRENT_USER_MODEL->get('id')}", 'menustatus' : "{$CURRENT_USER_MODEL->get('leftpanelhide')}" };
				{/if}
			</script>
		</head>
		 {assign var=CURRENT_USER_MODEL value=Users_Record_Model::getCurrentUserModel()}
		<body class="bg-body-secondary" data-skinpath="{Vtiger_Theme::getBaseThemePath()}" data-language="{$LANGUAGE}" data-user-decimalseparator="{$CURRENT_USER_MODEL->get('currency_decimal_separator')}" data-user-dateformat="{$CURRENT_USER_MODEL->get('date_format')}"
			data-user-groupingseparator="{$CURRENT_USER_MODEL->get('currency_grouping_separator')}" data-user-numberofdecimals="{$CURRENT_USER_MODEL->get('no_of_currency_decimals')}">
			<div id="page" class="p-0">
				<div id="pjaxContainer" class="hide noprint"></div>
{/strip}